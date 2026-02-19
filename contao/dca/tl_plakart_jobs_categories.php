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

$GLOBALS['TL_DCA']['tl_plakart_jobs_categories'] = array
(
    // Config
    'config' => array
    (
        'dataContainer' => DC_TABLE::class,
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'tstamp' => 'index',
                'alias' => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => DataContainer::MODE_SORTED,
            'fields'                  => array('title ASC'),
            'panelLayout'             => 'search',
            'defaultSearchField'      => 'title'
        ),
        'label' => array
        (
            'fields' => array('title'),
            'format' => '%s',
        ),
        'operations' => array
        (
            'edit',
            'copy',
            'delete',
            'show'
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend},title,alias;'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => ['type' => 'integer', 'length' => 10, 'notnull' => false, 'unsigned' => true, 'autoincrement' => true]
        ),
        'tstamp' => array
        (
            'sql'                    => ['type' => 'integer', 'length' => 10, 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ),
        'title' => array
        (
            'search'                  => true,
            'sorting'                 => true,
            'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
        'alias' => array
        (
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => ['type' => 'binary', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
    )
);