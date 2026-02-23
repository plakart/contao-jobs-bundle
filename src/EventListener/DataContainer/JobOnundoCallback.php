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
use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\DataContainer;
use Contao\PageModel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use Plakart\ContaoJobsBundle\Helper\GoogleApi;

#[AsCallback(table: 'tl_plakart_jobs', target: 'config.onundo')]
class JobOnundoCallback
{
    public function __construct(
        private readonly GoogleApi $googleApi,
        private readonly ContentUrlGenerator $contentUrlGenerator,
        #[Autowire(service: 'monolog.logger.contao')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(string $table, array $row, DataContainer $dc): void
    {
        if (!Config::get('gfjApi')) {
            return;
        }

        if ('tl_plakart_jobs' !== $table) {
            return;
        }

        if (!$this->isPublished($row['published'] ?? null)) {
            $this->logPing('Restore', 'URL_UPDATED', null, false, ['reason' => 'not_published']);
            return;
        }

        $alias = trim((string) ($row['alias'] ?? ''));
        if ('' === $alias) {
            $this->logPing('Restore', 'URL_UPDATED', null, false, ['reason' => 'missing_alias']);
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

        $ok = $this->googleApi->indexing($url, 'URL_UPDATED');
        $this->logPing('Restore', 'URL_UPDATED', $url, $ok);
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
            sprintf(
                'Google Ping [%s]: %s | Type: %s | URL: %s',
                $method,
                $ok ? 'OK' : 'NICHT OK',
                $type,
                $url ?? '-'
            ),
            $context
        );
    }
}
