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
use Contao\System;

class PlakartJobsModel extends Model
{
    protected static $strTable = 'tl_plakart_jobs';

    public static function findPublished(array $options = [])
    {
        if(self::shouldShowUnpublished()) {
            return static::findAll($options);
        }

        return static::findBy('published', 1, $options);
    }

    public static function findByCategory(string $category, array $options = [])
    {}

    private static function shouldShowUnpublished(): bool
    {
        $tokenChecker = System::getContainer()->get('contao.security.token_checker');
        return $tokenChecker->isPreviewMode();
    }
}