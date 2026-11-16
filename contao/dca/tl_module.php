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

$GLOBALS['TL_DCA']['tl_module']['palettes']['jobs_reader'] =
    '{title_legend},name,type;{plakartjobs_legend},jobsApplicationForm,overviewPage;{image_legend},imgSize,fullsize,jobsContactImgSize;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID'
;

$GLOBALS['TL_DCA']['tl_module']['fields']['jobsApplicationForm'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_form.title',
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
    'sql' => ['type' => 'integer', 'length' => 10, 'notnull' => false, 'unsigned' => true]
];

$GLOBALS['TL_DCA']['tl_module']['fields']['jobsContactImgSize'] = [
    'exclude' => true,
    'inputType' => 'imageSize',
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'],
    'options_callback' => static fn (): array => \Contao\System::getContainer()->get('contao.image.sizes')->getAllOptions(),
    'sql' => ['type' => 'string', 'length' => 255, 'notnull' => true, 'default' => ''],
];
