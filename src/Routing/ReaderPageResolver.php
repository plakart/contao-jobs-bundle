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

namespace Plakart\ContaoJobsBundle\Routing;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;

/**
 * Resolves the reader pages of the jobs via the "jumpTo" setting of the job
 * listing content elements.
 */
class ReaderPageResolver
{
    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    /**
     * @return array<int, PageModel> The reader pages indexed by their ID
     */
    public function findAll(): array
    {
        $this->framework->initialize();

        $listings = ContentModel::findBy(
            ['tl_content.type=?', 'tl_content.jumpTo>?'],
            ['jobs_listing', 0],
            ['order' => 'tl_content.id ASC'],
        );

        $pages = [];

        foreach ($listings ?? [] as $listing) {
            $pageId = (int) $listing->jumpTo;

            if (!isset($pages[$pageId]) && $page = PageModel::findById($pageId)) {
                $pages[$pageId] = $page;
            }
        }

        return $pages;
    }

    public function findFirst(): PageModel|null
    {
        $pages = $this->findAll();

        return [] === $pages ? null : reset($pages);
    }
}
