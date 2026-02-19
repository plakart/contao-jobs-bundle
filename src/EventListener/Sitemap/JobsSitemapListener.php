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

namespace Plakart\ContaoJobsBundle\EventListener\Sitemap;

use Contao\ContentModel;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\SitemapEvent;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(event: ContaoCoreEvents::SITEMAP)]
class JobsSitemapListener
{
    public function __construct(
        private readonly ContentUrlGenerator $contentUrlGenerator,
    ) {
    }

    public function __invoke(SitemapEvent $event): void
    {
        $readerPages = $this->findReaderPages($event->getRootPageIds());

        if ([] === $readerPages) {
            return;
        }

        $jobs = PlakartJobsModel::findBy('published', 1);

        if (null === $jobs) {
            return;
        }

        $urls = [];

        while ($jobs->next()) {
            $job = $jobs->current();

            if (!$this->isJobIndexable($job)) {
                continue;
            }

            foreach ($readerPages as $readerPage) {
                try {
                    $url = $this->contentUrlGenerator->generate(
                        $readerPage,
                        ['parameters' => '/' . $job->alias],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                } catch (ExceptionInterface) {
                    continue;
                }

                $urls[$url] = true;
            }
        }

        foreach (array_keys($urls) as $url) {
            $event->addUrlToDefaultUrlSet($url);
        }
    }

    /**
     * @return array<int, PageModel>
     */
    private function findReaderPages(array $rootPageIds): array
    {
        $listings = ContentModel::findBy(
            ['type=?', 'jumpTo>?'],
            ['jobs_listing', 0]
        );

        if (null === $listings) {
            return [];
        }

        $readerPages = [];

        while ($listings->next()) {
            $readerPageId = (int) $listings->jumpTo;

            if (isset($readerPages[$readerPageId])) {
                continue;
            }

            $readerPage = PageModel::findById($readerPageId);

            if (null === $readerPage) {
                continue;
            }

            $readerPage->loadDetails();

            if (!\in_array((int) $readerPage->rootId, $rootPageIds, true)) {
                continue;
            }

            $readerPages[$readerPageId] = $readerPage;
        }

        return $readerPages;
    }

    private function isJobIndexable(PlakartJobsModel $job): bool
    {
        $now = time();
        $start = (int) ($job->start ?? 0);
        $stop = (int) ($job->stop ?? 0);
        $robots = (string) ($job->robots ?? '');

        if ($start > 0 && $start > $now) {
            return false;
        }

        if ($stop > 0 && $stop <= $now) {
            return false;
        }

        if (str_starts_with($robots, 'noindex')) {
            return false;
        }

        return true;
    }
}
