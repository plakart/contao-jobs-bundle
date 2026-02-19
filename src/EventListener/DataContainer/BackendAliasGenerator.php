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

namespace Plakart\ContaoJobsBundle\EventListener\DataContainer;

use Plakart\ContaoJobsBundle\Helper\GenerateBackendAlias;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

class BackendAliasGenerator
{
    #[AsCallback(table: 'tl_plakart_jobs', target: 'fields.alias.save')]
    public function jobItemAliasGenerator($value, DataContainer $dc):string
    {
        $alias = new GenerateBackendAlias('tl_plakart_jobs');
        return $alias($value, $dc);
    }

    #[AsCallback(table: 'tl_plakart_jobs_categories', target: 'fields.alias.save')]
    public function jobCategoryAliasGenerator($value, DataContainer $dc):string
    {
        $alias = new GenerateBackendAlias('tl_plakart_jobs_categories');
        return $alias($value, $dc);
    }
}