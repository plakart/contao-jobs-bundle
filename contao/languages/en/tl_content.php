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

// Legends
$GLOBALS['TL_LANG']['tl_content']['plakartjobs_legend'] = 'Job settings';

// Fields
$GLOBALS['TL_LANG']['tl_content']['jumpTo'] ??= ['Redirect page', 'Please choose the page to which visitors will be redirected when clicking a job offer.'];
$GLOBALS['TL_LANG']['tl_content']['jobsCategories'] = ['Filter by categories', 'Only show job offers from the selected categories.'];
$GLOBALS['TL_LANG']['tl_content']['jobsSortBy'] = ['Sort by', 'Please choose the sort order of the job offers.'];

// References
$GLOBALS['TL_LANG']['tl_content']['sortByOptions'] = [
    'date_asc'  => 'Date ascending',
    'date_desc' => 'Date descending',
    'name_asc'  => 'Title ascending (A–Z)',
    'name_desc' => 'Title descending (Z–A)',
];

$GLOBALS['TL_LANG']['tl_content']['jobsFeatured'] = ['Featured jobs', 'Here you can choose how featured job offers are handled.'];
$GLOBALS['TL_LANG']['tl_content']['jobsFeaturedOptions'] = [
    'all_items' => 'Show all jobs',
    'featured' => 'Show featured jobs only',
    'unfeatured' => 'Skip featured jobs',
];
