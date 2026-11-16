<?php

declare(strict_types=1);

/*
 * This file is part of Contao Jobs Bundle.
 *
 * (c) plakart GmbH & Co. KG (https://plakart.de)
 * author Jannik Nölke (https://jaynoe.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plakart\ContaoJobsBundle\EventListener\DataContainer;

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\DataContainer;
use Plakart\ContaoJobsBundle\Helper\GoogleApi;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Plakart\ContaoJobsBundle\Routing\ReaderPageResolver;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Notifies the Google Indexing API whenever a job is saved, deleted or restored.
 */
class JobIndexingListener
{
    private const TABLE = 'tl_plakart_jobs';
    private const TYPE_UPDATED = 'URL_UPDATED';
    private const TYPE_DELETED = 'URL_DELETED';

    public function __construct(
        private readonly GoogleApi $googleApi,
        private readonly ContentUrlGenerator $contentUrlGenerator,
        private readonly ReaderPageResolver $readerPageResolver,
        #[Autowire(service: 'monolog.logger.contao')]
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsCallback(table: self::TABLE, target: 'config.oncreate_version')]
    public function onCreateVersion(string $table, int $pid, int $version, array $record): void
    {
        if (self::TABLE !== $table || $pid <= 0 || !$this->isEnabled()) {
            return;
        }

        $row = $this->completeRow($record, $pid);
        $type = PlakartJobsModel::isRowVisible($row) ? self::TYPE_UPDATED : self::TYPE_DELETED;

        $this->notify('CreateVersion', $type, (string) ($row['alias'] ?? ''));
    }

    #[AsCallback(table: self::TABLE, target: 'config.onrestore_version')]
    public function onRestoreVersion(string $table, int $pid, int $version, array $record): void
    {
        if (self::TABLE !== $table || $pid <= 0 || !$this->isEnabled()) {
            return;
        }

        $row = $this->completeRow($record, $pid);
        $type = PlakartJobsModel::isRowVisible($row) ? self::TYPE_UPDATED : self::TYPE_DELETED;

        $this->notify('Restore', $type, (string) ($row['alias'] ?? ''));
    }

    #[AsCallback(table: self::TABLE, target: 'config.ondelete')]
    public function onDelete(DataContainer $dc, int $undoId): void
    {
        if (!$dc->id || !$this->isEnabled()) {
            return;
        }

        $row = $this->completeRow([], (int) $dc->id);

        $this->notify('Delete', self::TYPE_DELETED, (string) ($row['alias'] ?? ''));
    }

    #[AsCallback(table: self::TABLE, target: 'config.onundo')]
    public function onUndo(string $table, array $row, DataContainer $dc): void
    {
        if (self::TABLE !== $table || !$this->isEnabled()) {
            return;
        }

        // Jobs that are restored but not visible do not have to be (re-)indexed
        if (!PlakartJobsModel::isRowVisible($row)) {
            return;
        }

        $this->notify('Undo', self::TYPE_UPDATED, (string) ($row['alias'] ?? ''));
    }

    private function isEnabled(): bool
    {
        return (bool) Config::get('gfjApi');
    }

    /**
     * Version records do not necessarily contain all fields, therefore the
     * missing ones are read from the database.
     *
     * @param array<string, mixed> $record
     *
     * @return array<string, mixed>
     */
    private function completeRow(array $record, int $id): array
    {
        if (isset($record['alias'], $record['published'], $record['start'], $record['stop'])) {
            return $record;
        }

        return $record + (PlakartJobsModel::findById($id)?->row() ?? []);
    }

    private function notify(string $method, string $type, string $alias): void
    {
        $alias = trim($alias);

        if ('' === $alias) {
            $this->log($method, $type, null, false, ['reason' => 'missing_alias']);

            return;
        }

        // Fallback to a relative URL so the GoogleApi can still try to normalize it
        $url = '/'.ltrim($alias, '/');

        if ($readerPage = $this->readerPageResolver->findFirst()) {
            try {
                $url = $this->contentUrlGenerator->generate($readerPage, ['parameters' => $url], UrlGeneratorInterface::ABSOLUTE_URL);
            } catch (\Throwable) {
                // Keep the relative URL
            }
        }

        $this->log($method, $type, $url, $this->googleApi->indexing($url, $type));
    }

    private function log(string $method, string $type, string|null $url, bool $ok, array $extra = []): void
    {
        $this->logger->log(
            $ok ? LogLevel::INFO : LogLevel::ERROR,
            \sprintf('Google Ping [%s]: %s | Type: %s | URL: %s', $method, $ok ? 'OK' : 'NICHT OK', $type, $url ?? '-'),
            [
                'contao' => new ContaoContext(__METHOD__, $ok ? ContaoContext::GENERAL : ContaoContext::ERROR),
                'method' => $method,
                'url' => $url,
                'type' => $type,
            ] + $extra,
        );
    }
}
