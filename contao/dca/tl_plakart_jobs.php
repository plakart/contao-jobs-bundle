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

$GLOBALS['TL_DCA']['tl_plakart_jobs'] = array
(
    // Config
    'config' => array
    (
        'dataContainer' => DC_TABLE::class,
        'switchToEdit' => true,
        'enableVersioning' => true,
        'markAsCopy' => 'title',
        'ctable' => ['tl_content'],
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'tstamp' => 'index',
                'alias' => 'index',
                'published,featured,start,stop' => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => DataContainer::MODE_SORTED,
            'fields'                  => array('date DESC'),
            'headerFields'            => array('title', 'tstamp'),
            'panelLayout'             => 'search,filter,sort,limit',
            'defaultSearchField'      => 'title'
        ),
        'label' => array
        (
            'fields' => array('title', 'date', 'time'),
            'format' => '%s <span class="label-info">[%s %s]</span>',
        ),
        'operations' => array
        (
            'edit',
            'children',
            'copy',
            'cut',
            'delete',
            'toggle' => array
            (
                'href'                => 'act=toggle&amp;field=published',
                'icon'                => 'visible.svg',
                'showInHeader'        => true
            ),
            'feature' => array
            (
                'href'                => 'act=toggle&amp;field=featured',
                'icon'                => 'featured.svg'
            ),
            'show'
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('addImage', 'addForm', 'overwriteMeta'),
        'default'                     => '
            {title_legend},title,featured,alias;
            {date_legend},date,time;
            {job_legend},categories,street,streetNr,zip,city,employmentSalary,employmentSalaryPer,employmentValidThrough,employmentType;
            {meta_legend},pageTitle,robots,description;
            {teaser_legend},subheadline,teaser;
            {image_legend},addImage;
            {expert_legend:hide},cssClass;
            {publish_legend},published,start,stop'
    ),

    // Sub-palettes
    'subpalettes' => array
    (
        'addImage'                    => 'singleSRC,fullsize'
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
        'featured' => array
        (
            'toggle'                  => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => ['type' => 'boolean', 'default' => false]
        ),
        'alias' => array
        (
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => ['type' => 'binary', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
        'date' => array
        (
            'default'                 => time(),
            'filter'                  => true,
            'sorting'                 => true,
            'flag'                    => DataContainer::SORT_MONTH_BOTH,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'date', 'mandatory'=>true, 'doNotCopy'=>true, 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
            'sql'                     => ['type' => 'integer', 'length' => 10, 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ),
        'time' => array
        (
            'default'                 => time(),
            'flag'                    => DataContainer::SORT_MONTH_DESC,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'time', 'mandatory'=>true, 'doNotCopy'=>true, 'tl_class'=>'w50'],
            'sql'                     => ['type' => 'integer', 'length' => 10, 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ),
        'categories' => array
        (
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => \Plakart\ContaoJobsBundle\Model\PlakartJobsCategoriesModel::findAllReturnAsOptionsArray(),
            'eval'                    => ['multiple'=>true, 'chosen' => true, 'tl_class' => 'clr', 'includeBlankOption' => true],
            'sql'                     => ['type' => 'blob', 'notnull' => false]
        ),
        'street' => array
        (
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'gfj w50'],
            'sql'                     => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
        'streetNr' => array
        (
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['basicEntities'=>true, 'maxlength'=>5, 'tl_class'=>'gfj w50'],
            'sql'                     => ['type' => 'string', 'length' => 5, 'notnull' => true, 'default' => '']
        ),
        'zip' => array
        (
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['basicEntities'=>true, 'maxlength'=>10, 'tl_class'=>'gfj w50'],
            'sql'                     => ['type' => 'string', 'length' => 10, 'notnull' => true, 'default' => '']
        ),
        'city' => array
        (
            'search'                  => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['basicEntities'=>true, 'maxlength'=>255, 'tl_class'=>'gfj w50'],
            'sql'                     => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
        'employmentType' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'checkbox',
            'options'                 => ['FULL_TIME', 'PART_TIME', 'CONTRACTOR', 'TEMPORARY', 'INTERN', 'VOLUNTEER', 'PER_DIEM', 'OTHER'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentType_options'],
            'eval'                    => ['tl_class'=>'gfj clr', 'multiple' => true],
            'sql'                     => ['type' => 'blob', 'notnull' => false]
        ),
        'employmentValidThrough' => array
        (
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'gfj w50 wizard'],
            'sql'                     => ['type' => 'string', 'length' => 10, 'notnull' => true, 'default' => '']
        ),
        'employmentSalary' => array
        (
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['basicEntities'=>true, 'maxlength'=>12, 'tl_class'=>'gfj w50'],
            'sql'                     => ['type' => 'string', 'length' => 12, 'notnull' => true, 'default' => '']
        ),
        'employmentSalaryPer' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options'                 => ['HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryPer_options'],
            'eval'                    => ['tl_class'=>'gfj w50', 'includeBlankOption' => true],
            'sql'                     => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => 'MONTH']
        ),
        'pageTitle' => array
        (
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength'=>255, 'decodeEntities'=>true, 'tl_class'=>'w50'],
            'sql'                     => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
        'robots' => array
        (
            'search'                  => true,
            'inputType'               => 'select',
            'options'                 => ['index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow'],
            'eval'                    => ['tl_class'=>'w50', 'includeBlankOption' => true],
            'sql'                     => ['type' => 'string', 'length' => 32, 'notnull' => true, 'default' => '']
        ),
        'description' => array
        (
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['style'=>'height:60px', 'decodeEntities'=>true, 'tl_class'=>'clr'],
            'sql'                     => ['type' => 'text', 'notnull' => false]
        ),
        'subheadline' => array
        (
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['maxlength'=>255, 'tl_class'=>'long'],
            'sql'                     => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
        'teaser' => array
        (
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'basicEntities'=>true, 'tl_class'=>'clr'),
            'sql'                     => ['type' => 'text', 'notnull' => false]
        ),
        'addImage' => array
        (
            'inputType'               => 'checkbox',
            'eval'                    => ['submitOnChange'=>true],
            'sql'                     => ['type' => 'boolean', 'default' => false]
        ),
        'singleSRC' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
            'inputType'               => 'fileTree',
            'eval'                    => ['fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>'%contao.image.valid_extensions%', 'mandatory'=>true],
            'sql'                     => ['type' => 'binary', 'length' => 16, 'notnull' => true, 'default' => '']
        ),
        'fullsize' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class'=>'w50'],
            'sql'                     => ['type' => 'boolean', 'default' => false]
        ),
        'cssClass' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['tl_class'=>'w50'],
            'sql'                     => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => '']
        ),
        'published' => array
        (
            'toggle'                  => true,
            'filter'                  => true,
            'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
            'inputType'               => 'checkbox',
            'eval'                    => ['doNotCopy'=>true],
            'sql'                     => ['type' => 'boolean', 'default' => false]
        ),
        'start' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
            'sql'                     => ['type' => 'string', 'length' => 10, 'notnull' => true, 'default' => '']
        ),
        'stop' => array
        (
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'datim', 'datepicker'=>true, 'tl_class'=>'w50 wizard'],
            'sql'                     => ['type' => 'string', 'length' => 10, 'notnull' => true, 'default' => '']
        )
    )
);