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

// Back end modules
$GLOBALS['BE_MOD']['content']['jobs'] = [
    'tables'  => ['tl_plakart_jobs', 'tl_content'],
    'stylesheet' => 'bundles/contaojobs/backend.css'
];
$GLOBALS['BE_MOD']['content']['jobsCategories'] = [
    'tables'  => ['tl_plakart_jobs_categories'],
];
$GLOBALS['BE_MOD']['system']['jobsSettings'] = [
    'tables'  => ['tl_plakart_jobs_settings'],
];

// Models
$GLOBALS['TL_MODELS']['tl_plakart_jobs'] = \Plakart\ContaoJobsBundle\Model\PlakartJobsModel::class;
$GLOBALS['TL_MODELS']['tl_plakart_jobs_categories'] = \Plakart\ContaoJobsBundle\Model\PlakartJobsCategoriesModel::class;

// Widgets
$GLOBALS['TL_FFL']['job_select_field'] = \Plakart\ContaoJobsBundle\Widget\Frontend\JobSelectField::class;