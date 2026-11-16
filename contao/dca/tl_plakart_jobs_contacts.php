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

use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;

System::loadLanguageFile('tl_content');

$GLOBALS['TL_DCA']['tl_plakart_jobs_contacts'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'name',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tstamp' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name ASC'],
            'panelLayout' => 'search,limit',
            'defaultSearchField' => 'name',
        ],
        'label' => [
            'fields' => ['name', 'position'],
            'format' => '%s <span class="label-info">[%s]</span>',
        ],
        'operations' => [
            'edit',
            'copy',
            'delete',
            'show',
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['addImage'],
        'default' => '{contact_legend},name,position,email,phone;{image_legend},addImage',
    ],

    // Sub-palettes
    'subpalettes' => [
        'addImage' => 'singleSRC',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'name' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'position' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'email' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'email', 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'phone' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'phone', 'maxlength' => 64, 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
        ],
        'addImage' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => ['type' => 'boolean', 'default' => false],
        ],
        'singleSRC' => [
            'label' => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'mandatory' => true],
            'sql' => ['type' => 'binary', 'length' => 16, 'fixed' => true, 'notnull' => false],
        ],
    ],
];
