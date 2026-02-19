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

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsCategoriesModel;
use Plakart\ContaoJobsBundle\Helper\PrepareJobData;
use Contao\StringUtil;
use Contao\System;
use Contao\PageModel;

#[AsContentElement(category: 'plakartJobs', template: 'plakart_jobs_list')]
class JobsListingController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        // IMPLEMENT :: GET ONLY ITEMS BY THAT CATEGORY/IES
        $categories = $model->jobsCategories ? StringUtil::deserialize($model->jobsCategories, true) : null;

        // check sortBy Field
        $sortBy = '';
        if('date_asc' === $model->jobsSortBy) {
            $sortBy = 'date ASC';
        } elseif ('date_desc' === $model->jobsSortBy) {
            $sortBy = 'date DESC';
        } elseif ('name_asc' === $model->jobsSortBy) {
            $sortBy = 'title ASC';
        } elseif ('name_desc' === $model->jobsSortBy) {
            $sortBy = 'title DESC';
        }

        // check limit
        $limit = PHP_INT_MAX;
        if($model->numberOfItems > 0) {
            $limit = $model->numberOfItems;
        }

        // IMPLEMENT :: PAGINATION
        $pagination = '';

        // IMPLEMENT :: Featured
        $featured = false;

        if($jobs = PlakartJobsModel::findPublished(['order' => $sortBy, 'limit' => $limit]))
        {
            // Reader-Page
            $readerPage = PageModel::findById($model->jumpTo) ?? null;

            while($jobs->next()) {
                $job = $jobs->current();

                $job->readerPage = false;
                if($readerPage) {
                    $job->readerPage = true;
                    $job->alias = PrepareJobData::alias($readerPage, $job->alias);
                }

                if($job->singleSRC) {
                    $job->figure = PrepareJobData::image($job->singleSRC, $model->size, $job->fullsize);
                }

                if($job->categories) {
                    $job->categories = PrepareJobData::categories($job->categories);
                }

                if($job->employmentType) {
                    $job->employmentType = PrepareJobData::employmentType($job->employmentType);
                }

            }

            $template->jobs = $jobs;
        }

        return $template->getResponse();
    }
}