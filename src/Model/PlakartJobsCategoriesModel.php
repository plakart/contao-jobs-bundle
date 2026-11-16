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

class PlakartJobsCategoriesModel extends Model
{
    protected static $strTable = 'tl_plakart_jobs_categories';

    /**
     * @return array<string, string> alias => title
     */
    public static function findAllReturnAsOptionsArray(): array
    {
        $options = [];

        foreach (self::findAll(['order' => 'title ASC']) ?? [] as $category) {
            $options[(string) $category->alias] = (string) $category->title;
        }

        return $options;
    }
}
