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
$GLOBALS['TL_LANG']['tl_plakart_jobs']['title_legend'] = 'Title and alias';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['date_legend'] = 'Date and time';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['job_legend'] = 'Job details';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['meta_legend'] = 'Meta data';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['teaser_legend'] = 'Subheadline and teaser';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['image_legend'] = 'Image settings';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['expert_legend'] = 'Expert settings';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['publish_legend'] = 'Publishing';

// Fields
$GLOBALS['TL_LANG']['tl_plakart_jobs']['title'] = ['Title', 'Please enter the title of the job offer.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['featured'] = ['Feature job', 'Show the job offer in a list of featured jobs.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['alias'] = ['Job alias', 'The job alias is a unique reference which can be called instead of its numeric ID.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['date'] = ['Date', 'Please enter the date of the job offer.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['time'] = ['Time', 'Please enter the time of the job offer.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['categories'] = ['Categories', 'Please select one or more categories.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['street'] = ['Street', 'The street of the job location.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['streetNr'] = ['Street number', 'The street number of the job location.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['zip'] = ['Postal code', 'The postal code of the job location.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['city'] = ['City', 'The city of the job location.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentType'] = ['Employment type', 'Please select one or more employment types.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentValidThrough'] = ['Application deadline', 'The date until which the job offer is valid.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalary'] = ['Salary (fixed value)', 'The fixed base salary as a number without thousands separators, e.g. 3500. Ignored if a salary range is given.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryMin'] = ['Salary (min.)', 'The lower bound of the salary range as a number without thousands separators.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryMax'] = ['Salary (max.)', 'The upper bound of the salary range as a number without thousands separators.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryPer'] = ['Salary per', 'The period the salary refers to.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['pageTitle'] = ['Page title', 'Here you can enter a custom page title.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['robots'] = ['Robots tag', 'Here you can define how search engines handle the job offer.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['description'] = ['Meta description', 'Here you can enter a custom meta description.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['subheadline'] = ['Subheadline', 'Here you can enter a subheadline.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['teaser'] = ['Teaser', 'The teaser can be shown in the job list instead of the full article.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['addImage'] = ['Add an image', 'Add an image to the job offer.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['cssClass'] = ['CSS class', 'Here you can enter one or more CSS classes.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['published'] = ['Publish job', 'Show the job offer on the website.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['start'] = ['Show from', 'Do not show the job offer on the website before this date.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['stop'] = ['Show until', 'Do not show the job offer on the website after this date.'];

// References
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentType_options'] = [
    'FULL_TIME'  => 'Full-time',
    'PART_TIME'  => 'Part-time',
    'CONTRACTOR' => 'Contractor',
    'TEMPORARY'  => 'Temporary',
    'INTERN'     => 'Internship',
    'VOLUNTEER'  => 'Volunteer',
    'PER_DIEM'   => 'Per diem',
    'OTHER'      => 'Other',
];

$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryPer_options'] = [
    'HOUR'  => 'Hour',
    'DAY'   => 'Day',
    'WEEK'  => 'Week',
    'MONTH' => 'Month',
    'YEAR'  => 'Year',
];

// Operations
$GLOBALS['TL_LANG']['tl_plakart_jobs']['toggle'] = ['Publish/unpublish job', 'Publish/unpublish job ID %s'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['feature'] = ['Feature/unfeature job', 'Feature/unfeature job ID %s'];

// Locations and contact persons
$GLOBALS['TL_LANG']['tl_plakart_jobs']['contact_legend'] = 'Contact persons';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['locations'] = ['Locations', 'Please choose one or more locations. If no location is selected, the address below or the company address is used.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['contacts'] = ['Contact persons', 'Please choose one or more contact persons.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['company'] = ['Company', 'The hiring company. If there is only one company, it is selected automatically.'];
