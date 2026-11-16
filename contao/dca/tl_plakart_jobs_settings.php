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
        'default'                     => '{global_legend},company,street,streetNr,zip,city,gfjCountry,url,logo;{gfj_legend},gfjSchema,gfjApi;{expert_legend},gfjApiAnalysis'
    ),

    // Sub-palettes
    'subpalettes' => array
    (
        'gfjApi'                      => 'gfjJsonFileName,gfjApiAnalysis',
    ),

    // Fields
    'fields' => array
    (
        'company' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['tl_class' => 'w50', 'mandatory' => true],
        ),
        'street' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['tl_class' => 'clr w25', 'mandatory' => true]
        ),
        'streetNr' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['tl_class' => 'w25', 'mandatory' => true]
        ),
        'zip' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['tl_class' => 'w25', 'mandatory' => true]
        ),
        'city' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['tl_class' => 'w25', 'mandatory' => true]
        ),
        'gfjCountry' => array
        (
            'default'                 => 'DE',
            'inputType'               => 'select',
            'options_callback'        => static fn (): array => \Contao\System::getContainer()->get('contao.intl.countries')->getCountries(),
            'eval'                    => ['tl_class' => 'clr w50', 'chosen' => true, 'includeBlankOption' => true]
        ),
        'url' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['tl_class' => 'clr w50', 'mandatory' => true]
        ),
        'logo' => array
        (
            'inputType'               => 'fileTree',
            'eval'                    => ['tl_class' => 'clr widget', 'mandatory' => true, 'filesOnly' => true, 'extensions' => '%contao.image.valid_extensions%']
        ),
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
