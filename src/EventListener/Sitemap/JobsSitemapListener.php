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

use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\SitemapEvent;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\PageModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Plakart\ContaoJobsBundle\Routing\ReaderPageResolver;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener(event: ContaoCoreEvents::SITEMAP)]
class JobsSitemapListener
{
    public function __construct(
        private readonly ContentUrlGenerator $contentUrlGenerator,
        private readonly ReaderPageResolver $readerPageResolver,
    ) {
    }

    public function __invoke(SitemapEvent $event): void
    {
        $readerPages = $this->findReaderPages($event->getRootPageIds());

        if ([] === $readerPages) {
            return;
        }

        $urls = [];

        foreach (PlakartJobsModel::findBy('published', 1) ?? [] as $job) {
            if (!$job->isVisible() || str_starts_with((string) $job->robots, 'noindex')) {
                continue;
            }

            foreach ($readerPages as $readerPage) {
                try {
                    $url = $this->contentUrlGenerator->generate(
                        $readerPage,
                        ['parameters' => '/'.$job->alias],
                        UrlGeneratorInterface::ABSOLUTE_URL,
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
     * @param array<int> $rootPageIds
     *
     * @return array<int, PageModel>
     */
    private function findReaderPages(array $rootPageIds): array
    {
        $rootPageIds = array_map('intval', $rootPageIds);

        return array_filter(
            $this->readerPageResolver->findAll(),
            static fn (PageModel $page): bool => \in_array((int) $page->loadDetails()->rootId, $rootPageIds, true),
        );
    }
}
