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
use Contao\DataContainer;
use Contao\PageModel;
use Plakart\ContaoJobsBundle\Helper\GoogleApi;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

#[AsCallback(table: 'tl_plakart_jobs', target: 'config.ondelete')]
class JobOndeleteCallback
{
    public function __construct(
        private readonly GoogleApi $googleApi,
        private readonly ContentUrlGenerator $contentUrlGenerator,
        #[Autowire(service: 'monolog.logger.contao')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(DataContainer $dc, int $undoId): void
    {
        if (!Config::get('gfjApi')) {
            return;
        }

        if (!$dc->id) {
            return;
        }

        $alias = trim((string) ($dc->activeRecord?->alias ?? ''));

        if ('' === $alias) {
            $job = PlakartJobsModel::findById((int) $dc->id);
            $alias = trim((string) ($job?->alias ?? ''));
        }

        if ('' === $alias) {
            $this->logPing('Delete', 'URL_DELETED', null, false, ['reason' => 'missing_alias']);
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
                // Fallback to relative URL so GoogleApi can still try to normalize it.
            }
        }

        $ok = $this->googleApi->indexing($url, 'URL_DELETED');
        $this->logPing('Delete', 'URL_DELETED', $url, $ok);
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
