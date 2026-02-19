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

namespace Plakart\ContaoJobsBundle\Helper;

use Contao\DataContainer;
use Contao\System;
use Contao\Database;
use Exception;

class GenerateBackendAlias
{
    protected string $table;
    protected object $Database;

    public function __construct(string $dcTable) {
        $this->table = $dcTable;
        $this->Database = Database::getInstance();
    }

    public function __invoke(mixed $varValue, DataContainer $dc):string
    {

        $aliasExists = function (string $alias) use ($dc): bool
        {
            return $this->Database->prepare("SELECT id FROM ".$this->table." WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate alias if there is none
        if (!$varValue)
        {
            $varValue = System::getContainer()->get('contao.slug')->generate((string) $dc->activeRecord->title, [], $aliasExists);
        }
        elseif (preg_match('/^[1-9]\d*$/', $varValue))
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        }

        return $varValue;
    }
}