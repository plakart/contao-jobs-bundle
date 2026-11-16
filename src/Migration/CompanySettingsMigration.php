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

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Contao\Validator;
use Doctrine\DBAL\Connection;

/**
 * The company data used to be part of the job settings (localconfig.php). It is
 * now managed in its own table, so this migration creates a company from the
 * existing settings.
 */
class CompanySettingsMigration extends AbstractMigration
{
    private const LEGACY_KEYS = ['company', 'street', 'streetNr', 'zip', 'city', 'gfjCountry', 'url', 'logo'];

    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
    ) {
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['tl_plakart_jobs_companies'])) {
            return false;
        }

        if ($this->connection->fetchOne('SELECT TRUE FROM tl_plakart_jobs_companies LIMIT 1')) {
            return false;
        }

        $this->framework->initialize();

        return '' !== trim((string) Config::get('company'));
    }

    public function run(): MigrationResult
    {
        $this->framework->initialize();

        $logo = Config::get('logo');

        if (\is_string($logo) && Validator::isStringUuid($logo)) {
            $logo = StringUtil::uuidToBin($logo);
        } elseif (!\is_string($logo) || !Validator::isBinaryUuid($logo)) {
            $logo = null;
        }

        $this->connection->insert('tl_plakart_jobs_companies', [
            'tstamp' => time(),
            'title' => (string) Config::get('company'),
            'street' => (string) Config::get('street'),
            'streetNr' => (string) Config::get('streetNr'),
            'zip' => (string) Config::get('zip'),
            'city' => (string) Config::get('city'),
            'country' => strtoupper((string) (Config::get('gfjCountry') ?: 'DE')),
            'url' => (string) Config::get('url'),
            'logo' => $logo,
        ]);

        foreach (self::LEGACY_KEYS as $key) {
            Config::remove("\$GLOBALS['TL_CONFIG']['$key']");
        }

        return $this->createResult(true);
    }
}
