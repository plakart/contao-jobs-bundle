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

namespace Plakart\ContaoJobsBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * The back end module "jobs" has been renamed to "jobsOffers", because Contao
 * 5.7 introduced a core module with the same name. This migration carries over
 * the module permissions of users and user groups.
 */
class BackendModulePermissionMigration extends AbstractMigration
{
    private const TABLES = ['tl_user', 'tl_user_group'];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(self::TABLES)) {
            return false;
        }

        foreach (self::TABLES as $table) {
            if ($this->connection->fetchOne("SELECT TRUE FROM $table WHERE modules LIKE '%\"jobs\"%' LIMIT 1")) {
                return true;
            }
        }

        return false;
    }

    public function run(): MigrationResult
    {
        foreach (self::TABLES as $table) {
            $rows = $this->connection->fetchAllKeyValue("SELECT id, modules FROM $table WHERE modules LIKE '%\"jobs\"%'");

            foreach ($rows as $id => $modules) {
                $modules = StringUtil::deserialize($modules, true);
                $modules = array_map(static fn ($module) => 'jobs' === $module ? 'jobsOffers' : $module, $modules);

                $this->connection->update($table, ['modules' => serialize(array_values(array_unique($modules)))], ['id' => $id]);
            }
        }

        return $this->createResult(true);
    }
}
