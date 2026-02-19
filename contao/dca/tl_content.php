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
use Plakart\ContaoJobsBundle\Helper\CategoriesHandler;

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_content']['palettes']['jobs_listing'] = '{type_legend},type,headline;{config_legend},jumpTo,size;{plakartjobs_legend},jobsCategories,jobsSortBy,numberOfItems,perPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['jumpTo'] = array
(
    'inputType'               => 'pageTree',
    'foreignKey'              => 'tl_page.title',
    'eval'                    => ['fieldType'=>'radio'],
    'sql'                     => ['type' => 'integer', 'length' => 10, 'notnull' => false, 'unsigned' => true, 'default' => 0],
    'relation'                => ['type'=>'hasOne', 'load'=>'lazy']
);

$GLOBALS['TL_DCA']['tl_content']['fields']['jobsCategories'] = array
(
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => [],
    'eval'                    => ['multiple'=>true, 'chosen' => true, 'tl_class' => 'clr', 'includeBlankOption' => true],
    'sql'                     => ['type' => 'blob', 'notnull' => false]
);

$GLOBALS['TL_DCA']['tl_content']['fields']['jobsSortBy'] = array
(
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['date_asc', 'date_desc', 'name_asc', 'name_desc'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['sortByOptions'],
    'eval' => ['tl_class'=>'w50 clr'],
    'sql' => ['type' => 'string', 'length' => 32, 'notnull' => true, 'default' => ''],
);