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

use Contao\DC_File;

$GLOBALS['TL_DCA']['tl_plakart_jobs_settings'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => DC_File::class,
        'closed'                      => true
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('gfjApi'),
        'default'                     => '{gfj_legend},gfjSchema,gfjApi;{expert_legend},gfjApiAnalysis'
    ),

    // Sub-palettes
    'subpalettes' => array
    (
        'gfjApi'                      => 'gfjJsonFileName,gfjApiAnalysis',
    ),

    // Fields
    'fields' => array
    (
        'gfjSchema' => array
        (
            'inputType'               => 'checkbox'
        ),
        'gfjApi' => array
        (
            'inputType'               => 'checkbox',
            'eval'                    => ['submitOnChange' => true]
        ),
        'gfjJsonFileName' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true,'tl_class'=>'w50'],
        ),
        'gfjApiAnalysis' => array
        (
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'clr'],
        )
    )

);
