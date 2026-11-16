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

namespace Plakart\ContaoJobsBundle\Widget\Frontend;

use Contao\Input;
use Contao\Widget;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;

/**
 * Form field to select the job an application refers to. On a job detail page
 * the current job is submitted as a hidden field, otherwise a select menu with
 * all published jobs is shown.
 */
class JobSelectField extends Widget
{
    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'form_job_select_field';
    protected $strPrefix = 'widget widget-select job_select_field';

    private PlakartJobsModel|false|null $currentJob = false;

    /**
     * Returns the job of the current detail page (if any).
     */
    public function getCurrentJob(): PlakartJobsModel|null
    {
        if (false === $this->currentJob) {
            $alias = Input::get('auto_item');
            $this->currentJob = $alias ? PlakartJobsModel::findPublishedByIdOrAlias($alias) : null;
        }

        return $this->currentJob;
    }

    /**
     * @return array<int, string> id => title
     */
    public function getJobOptions(): array
    {
        $options = [];

        foreach (PlakartJobsModel::findPublished(['order' => 'tl_plakart_jobs.title ASC']) ?? [] as $job) {
            $options[(int) $job->id] = (string) $job->title;
        }

        return $options;
    }

    public function generate(): string
    {
        // Not used, the widget is rendered by its template
        return '';
    }

    protected function validator($varInput)
    {
        $varInput = parent::validator($varInput);

        if ($varInput && null === PlakartJobsModel::findPublishedByIdOrAlias((int) $varInput)) {
            $this->addError($GLOBALS['TL_LANG']['MSC']['plakart_jobs']['invalidJob'] ?? 'Invalid job.');
        }

        return $varInput;
    }
}
