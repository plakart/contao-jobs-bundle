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

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;

/**
 * Provides the options for the category, location and contact person selects.
 */
class JobOptionsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<string, string> alias => title
     */
    #[AsCallback(table: 'tl_plakart_jobs', target: 'fields.categories.options')]
    #[AsCallback(table: 'tl_content', target: 'fields.jobsCategories.options')]
    public function categories(): array
    {
        return $this->connection->fetchAllKeyValue('SELECT alias, title FROM tl_plakart_jobs_categories ORDER BY title');
    }

    /**
     * @return array<int, string> id => label
     */
    #[AsCallback(table: 'tl_plakart_jobs', target: 'fields.locations.options')]
    public function locations(): array
    {
        $options = [];

        foreach ($this->connection->fetchAllAssociative('SELECT id, title, zip, city FROM tl_plakart_jobs_locations ORDER BY title') as $row) {
            $address = trim($row['zip'].' '.$row['city']);
            $options[(int) $row['id']] = $row['title'].('' !== $address ? " ($address)" : '');
        }

        return $options;
    }

    /**
     * @return array<int, string> id => label
     */
    #[AsCallback(table: 'tl_plakart_jobs', target: 'fields.contacts.options')]
    public function contacts(): array
    {
        $options = [];

        foreach ($this->connection->fetchAllAssociative('SELECT id, name, position FROM tl_plakart_jobs_contacts ORDER BY name') as $row) {
            $options[(int) $row['id']] = $row['name'].('' !== $row['position'] ? " ({$row['position']})" : '');
        }

        return $options;
    }
}
