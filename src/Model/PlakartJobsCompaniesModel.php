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

namespace Plakart\ContaoJobsBundle\Model;

use Contao\Model;

class PlakartJobsCompaniesModel extends Model
{
    protected static $strTable = 'tl_plakart_jobs_companies';

    /**
     * Returns the company if there is exactly one. It is then used for all
     * jobs without having to be assigned explicitly.
     */
    public static function findOnlyOne(): static|null
    {
        $companies = static::findAll(['limit' => 2]);

        return 1 === $companies?->count() ? $companies->current() : null;
    }

    /**
     * Returns the company of a job: the assigned one or, if there is only a
     * single company, that one.
     */
    public static function findForJob(PlakartJobsModel $job): static|null
    {
        if ((int) $job->company > 0 && $company = static::findById((int) $job->company)) {
            return $company;
        }

        return static::findOnlyOne();
    }
}
