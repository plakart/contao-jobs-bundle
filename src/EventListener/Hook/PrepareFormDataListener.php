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

namespace Plakart\ContaoJobsBundle\EventListener\Hook;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\FormFieldModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;

#[AsHook('prepareFormData')]
class PrepareFormDataListener
{
    public function __invoke(
        array &$submittedData,
        array $labels,
        array $fields,
        Form $form,
        array &$files = []
    ): void {

        $jobFieldNames = [];
        $formFields = FormFieldModel::findByPid((int) ($form->id ?? 0));

        if (null !== $formFields) {
            while ($formFields->next()) {
                if ('job_select_field' !== (string) $formFields->type) {
                    continue;
                }

                $jobFieldNames[] = (string) $formFields->name;
            }
        }

        foreach ($jobFieldNames as $fieldName) {
            if (!\array_key_exists($fieldName, $submittedData)) {
                continue;
            }

            $jobId = (int) ($submittedData[$fieldName] ?? 0);

            if ($jobId <= 0) {
                continue;
            }

            $job = PlakartJobsModel::findByPk($jobId);

            if (null === $job) {
                continue;
            }

            $submittedData[$fieldName] = (string) $job->title;
        }
    }
}
