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

use Contao\ContentModel;
use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Plakart\ContaoJobsBundle\Helper\GoogleApi;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

#[AsCallback(table: 'tl_plakart_jobs', target: 'config.oncreate_version')]
class JobOncreateVersionCallback
{
    public function __construct(
        private readonly GoogleApi $googleApi,
        private readonly ContentUrlGenerator $contentUrlGenerator,
        #[Autowire(service: 'monolog.logger.contao')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $table, int $pid, int $version, array $record): void
    {
        if (!Config::get('gfjApi')) {
            return;
        }

        if ('tl_plakart_jobs' !== $table || $pid <= 0) {
            return;
        }

        $published = $record['published'] ?? null;
        if (null === $published) {
            $job = PlakartJobsModel::findById($pid);
            $published = $job?->published;
        }

        $type = $this->isPublished($published) ? 'URL_UPDATED' : 'URL_DELETED';

        $alias = trim((string) ($record['alias'] ?? ''));
        if ('' === $alias) {
            $job = isset($job) ? $job : PlakartJobsModel::findById($pid);
            $alias = trim((string) ($job?->alias ?? ''));
        }

        if ('' === $alias) {
            $this->logPing('CreateVersion', $type, null, false, ['reason' => 'missing_alias']);
            return;
        }

        $url = '/' . ltrim($alias, '/');
        $readerPage = $this->resolveReaderPage();

        if (null !== $readerPage) {
            try {
                $url = $this->contentUrlGenerator->generate(
                    $readerPage,
                    ['parameters' => '/' . ltrim($alias, '/')],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } catch (Throwable) {
                // Fallback to relative URL so GoogleApi can still normalize it.
            }
        }

        $ok = $this->googleApi->indexing($url, $type);
        $this->logPing('CreateVersion', $type, $url, $ok);
    }

    private function isPublished(mixed $published): bool
    {
        if (\is_bool($published)) {
            return $published;
        }

        return '1' === (string) $published || 1 === $published;
    }

    private function resolveReaderPage(): ?PageModel
    {
        $listings = ContentModel::findBy(
            ['type=?', 'jumpTo>?'],
            ['jobs_listing', 0],
            ['order' => 'id ASC']
        );

        if (null === $listings) {
            return null;
        }

        while ($listings->next()) {
            $page = PageModel::findById((int) $listings->jumpTo);
            if (null !== $page) {
                return $page;
            }
        }

        return null;
    }

    private function logPing(string $method, string $type, ?string $url, bool $ok, array $extra = []): void
    {
        $context = [
            'contao' => new ContaoContext(__CLASS__ . '::' . __FUNCTION__, $ok ? ContaoContext::GENERAL : ContaoContext::ERROR),
            'method' => $method,
            'url' => $url,
            'type' => $type,
        ];

        if ([] !== $extra) {
            $context += $extra;
        }

        $this->logger->log(
            $ok ? LogLevel::INFO : LogLevel::ERROR,
            sprintf('Google Ping [%s]: %s', $method, $ok ? 'OK' : 'NICHT OK'),
            $context
        );
    }
}
