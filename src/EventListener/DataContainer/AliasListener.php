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
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * Generates the alias from the title if none has been entered.
 */
#[AsCallback(table: 'tl_plakart_jobs', target: 'fields.alias.save')]
#[AsCallback(table: 'tl_plakart_jobs_categories', target: 'fields.alias.save')]
class AliasListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Slug $slug,
    ) {
    }

    public function __invoke(mixed $value, DataContainer $dc): string
    {
        $value = (string) $value;
        $table = $this->connection->quoteIdentifier($dc->table);

        $aliasExists = fn (string $alias): bool => false !== $this->connection->fetchOne(
            "SELECT id FROM $table WHERE alias = ? AND id != ?",
            [$alias, $dc->id],
        );

        if ('' === $value) {
            // The active record already contains the title submitted with the current request
            $title = $dc->activeRecord?->title ?? $dc->getCurrentRecord()['title'] ?? '';

            return $this->slug->generate((string) $title, [], $aliasExists);
        }

        if (preg_match('/^[1-9]\d*$/', $value)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $value));
        }

        if ($aliasExists($value)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }
}
