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
    '{title_legend},name,type;{plakartjobs_legend},jobsApplicationForm,overviewPage;{image_legend},imgSize,fullsize;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID'
;

$GLOBALS['TL_DCA']['tl_module']['fields']['jobsApplicationForm'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_form.title',
    'eval' => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
    'sql' => ['type' => 'integer', 'length' => 10, 'notnull' => false, 'unsigned' => true]
];