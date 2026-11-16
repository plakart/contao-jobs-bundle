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

$GLOBALS['TL_DCA']['tl_plakart_jobs_companies'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
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
            'fields' => ['title ASC'],
            'panelLayout' => 'search,limit',
            'defaultSearchField' => 'title',
        ],
        'label' => [
            'fields' => ['title', 'zip', 'city'],
            'format' => '%s <span class="label-info">[%s %s]</span>',
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
        'default' => '{company_legend},title,url,logo;{address_legend},street,streetNr,zip,city,country',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'title' => [
            'search' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'gfj w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'url' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'url', 'maxlength' => 255, 'decodeEntities' => true, 'tl_class' => 'gfj w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'logo' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%', 'tl_class' => 'gfj clr'],
            'sql' => ['type' => 'binary', 'length' => 16, 'fixed' => true, 'notnull' => false],
        ],
        'street' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'gfj w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'streetNr' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 16, 'tl_class' => 'gfj w50'],
            'sql' => ['type' => 'string', 'length' => 16, 'default' => ''],
        ],
        'zip' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'tl_class' => 'gfj w50'],
            'sql' => ['type' => 'string', 'length' => 10, 'default' => ''],
        ],
        'city' => [
            'search' => true,
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'gfj w50'],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'country' => [
            'default' => 'DE',
            'inputType' => 'select',
            'options_callback' => static fn (): array => System::getContainer()->get('contao.intl.countries')->getCountries(),
            'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'gfj w50'],
            'sql' => ['type' => 'string', 'length' => 2, 'default' => 'DE'],
        ],
    ],
];
