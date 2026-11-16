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

namespace Plakart\ContaoJobsBundle\Controller\ContentElement;

use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\PageOutOfRangeException;
use Contao\CoreBundle\Pagination\PaginationConfig;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use Contao\Pagination;
use Contao\StringUtil;
use Plakart\ContaoJobsBundle\Helper\PrepareJobData;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(category: 'plakartJobs', template: 'content_element/plakart_jobs_list')]
class JobsListingController extends AbstractContentElementController
{
    private const SORTING = [
        'date_asc' => 'tl_plakart_jobs.date ASC, tl_plakart_jobs.time ASC',
        'date_desc' => 'tl_plakart_jobs.date DESC, tl_plakart_jobs.time DESC',
        'name_asc' => 'tl_plakart_jobs.title ASC',
        'name_desc' => 'tl_plakart_jobs.title DESC',
    ];

    /**
     * @param object|null $paginationFactory The pagination factory of Contao >= 5.7
     */
    public function __construct(
        private readonly object|null $paginationFactory = null,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $categories = StringUtil::deserialize($model->jobsCategories, true);
        $featured = (string) ($model->jobsFeatured ?: PlakartJobsModel::FEATURED_ALL);

        // Total number of items, optionally limited by the element settings
        $total = PlakartJobsModel::countPublishedByFilter($categories, $featured);
        $limit = (int) $model->numberOfItems;

        if ($limit > 0) {
            $total = min($limit, $total);
        }

        $offset = 0;
        $perPage = (int) $model->perPage;

        $template->pagination = null;
        $template->pagination_html = '';

        if ($perPage > 0 && $total > $perPage) {
            $offset = $this->addPagination($template, $request, 'page_j'.$model->id, $total, $perPage);
            $limit = min($perPage, $total - $offset);
        }

        $options = ['order' => self::SORTING[$model->jobsSortBy] ?? self::SORTING['date_desc']];

        if ($limit > 0) {
            $options['limit'] = $limit;
            $options['offset'] = $offset;
        }

        $readerPage = $model->jumpTo ? PageModel::findById($model->jumpTo) : null;
        $jobs = [];

        foreach (PlakartJobsModel::findPublishedByFilter($categories, $featured, $options) ?? [] as $job) {
            $data = PrepareJobData::forTemplate($job, $model->size);
            $data['readerPage'] = null !== $readerPage;
            $data['url'] = $readerPage ? PrepareJobData::url($readerPage, (string) $job->alias) : null;

            // Backwards compatibility: "alias" used to contain the URL of the detail page
            if ($data['url']) {
                $data['alias'] = $data['url'];
            }

            $jobs[] = $data;
            $this->tagResponse('contao.db.tl_plakart_jobs.'.$job->id);
        }

        $template->jobs = $jobs;
        $template->total = $total;

        return $template->getResponse();
    }

    /**
     * Adds the pagination to the template and returns the offset of the current page.
     */
    private function addPagination(FragmentTemplate $template, Request $request, string $parameter, int $total, int $perPage): int
    {
        // Contao >= 5.7
        if (null !== $this->paginationFactory) {
            try {
                $pagination = $this->paginationFactory->create(new PaginationConfig($parameter, $total, $perPage));
            } catch (PageOutOfRangeException $e) {
                throw new PageNotFoundException('Page not found: '.$request->getUri(), previous: $e);
            }

            $template->pagination = $pagination;

            return $pagination->getOffset();
        }

        // Contao 5.3
        $page = (int) $request->query->get($parameter, 1);

        if ($page < 1 || $page > max(ceil($total / $perPage), 1)) {
            throw new PageNotFoundException('Page not found: '.$request->getUri());
        }

        $pagination = new Pagination($total, $perPage, Config::get('maxPaginationLinks'), $parameter);
        $template->pagination_html = $pagination->generate("\n  ");

        return ($page - 1) * $perPage;
    }
}
