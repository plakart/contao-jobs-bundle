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

use Contao\System;
use Plakart\ContaoJobsBundle\Model\PlakartJobsCategoriesModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsContactsModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsLocationsModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Plakart\ContaoJobsBundle\Widget\Frontend\JobSelectField;
use Symfony\Component\HttpFoundation\Request;

// Back end modules: own "Jobs" category right after "Content"
$jobModules = [
    'plakartJobs' => [
        // Must not be named "jobs": Contao >= 5.7 has a core back end module with that name
        'jobsOffers' => [
            'tables' => ['tl_plakart_jobs', 'tl_content'],
        ],
        'jobsCategories' => [
            'tables' => ['tl_plakart_jobs_categories'],
        ],
        'jobsLocations' => [
            'tables' => ['tl_plakart_jobs_locations'],
        ],
        'jobsContacts' => [
            'tables' => ['tl_plakart_jobs_contacts'],
        ],
        'jobsSettings' => [
            'tables' => ['tl_plakart_jobs_settings'],
        ],
    ],
];

$position = array_search('content', array_keys($GLOBALS['BE_MOD']), true);
$position = false === $position ? \count($GLOBALS['BE_MOD']) : $position + 1;

$GLOBALS['BE_MOD'] = \array_slice($GLOBALS['BE_MOD'], 0, $position, true) + $jobModules + \array_slice($GLOBALS['BE_MOD'], $position, null, true);

unset($jobModules, $position);

// Back end style sheet (navigation icon and Google for Jobs field markers)
if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
    $GLOBALS['TL_CSS'][] = 'bundles/contaojobs/backend.css';
}

// Models
$GLOBALS['TL_MODELS']['tl_plakart_jobs'] = PlakartJobsModel::class;
$GLOBALS['TL_MODELS']['tl_plakart_jobs_categories'] = PlakartJobsCategoriesModel::class;
$GLOBALS['TL_MODELS']['tl_plakart_jobs_locations'] = PlakartJobsLocationsModel::class;
$GLOBALS['TL_MODELS']['tl_plakart_jobs_contacts'] = PlakartJobsContactsModel::class;

// Widgets
$GLOBALS['TL_FFL']['job_select_field'] = JobSelectField::class;
