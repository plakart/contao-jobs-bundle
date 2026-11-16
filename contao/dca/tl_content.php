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

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_content']['palettes']['jobs_listing'] = '{type_legend},type,headline;{config_legend},jumpTo,size;{plakartjobs_legend},jobsCategories,jobsFeatured,jobsSortBy,numberOfItems,perPage;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';

// Contao >= 5.7 ships its own "jumpTo" field for content elements, only add it in older versions
$GLOBALS['TL_DCA']['tl_content']['fields']['jumpTo'] ??= array
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
    'eval'                    => ['multiple'=>true, 'chosen' => true, 'tl_class' => 'clr'],
    'sql'                     => ['type' => 'blob', 'notnull' => false]
);

$GLOBALS['TL_DCA']['tl_content']['fields']['jobsSortBy'] = array
(
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['date_asc', 'date_desc', 'name_asc', 'name_desc'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['sortByOptions'],
    'eval' => ['tl_class'=>'w50'],
    'sql' => ['type' => 'string', 'length' => 32, 'notnull' => true, 'default' => ''],
);

$GLOBALS['TL_DCA']['tl_content']['fields']['jobsFeatured'] = array
(
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['all_items', 'featured', 'unfeatured'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['jobsFeaturedOptions'],
    'eval' => ['tl_class'=>'w50'],
    'sql' => ['type' => 'string', 'length' => 16, 'notnull' => true, 'default' => 'all_items'],
);
