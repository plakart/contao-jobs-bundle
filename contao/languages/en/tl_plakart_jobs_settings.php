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
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['global_legend'] = 'Company details';
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfj_legend'] = 'Google for Jobs';
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['expert_legend'] = 'Expert settings';

// Fields
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['company'] = ['Company', 'The name of the hiring company.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['street'] = ['Street', 'The street of the company address.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['streetNr'] = ['Street number', 'The street number of the company address.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['zip'] = ['Postal code', 'The postal code of the company address.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['city'] = ['City', 'The city of the company address.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['url'] = ['Website URL', 'The URL of the company website.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['logo'] = ['Logo', 'Please select the company logo.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjSchema'] = ['Output structured data', 'Embeds the schema.org markup (JobPosting) for Google for Jobs on the detail page.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjApi'] = ['Use the Google Indexing API', 'Automatically reports new, changed and deleted job offers to the Google Indexing API.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjJsonFileName'] = ['JSON key file name', 'The file name of the Google service account key file (JSON).'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjApiAnalysis'] = [
    'Analysis mode (detailed logging)',
    'Enables verbose Google Indexing logs for analysis. Disable in production.',
];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjCountry'] = ['Country', 'The country of the company. Required for Google for Jobs (default: Germany).'];
