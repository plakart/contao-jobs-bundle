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

use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class PlakartJobsModel extends Model
{
    public const FEATURED_ALL = 'all_items';
    public const FEATURED_ONLY = 'featured';
    public const FEATURED_NONE = 'unfeatured';

    protected static $strTable = 'tl_plakart_jobs';

    /**
     * Finds all jobs that are visible in the front end. In the front end preview
     * unpublished jobs are included as well.
     *
     * @return Collection<static>|null
     */
    public static function findPublished(array $options = []): Collection|null
    {
        return static::findPublishedByFilter([], self::FEATURED_ALL, $options);
    }

    /**
     * @param array<string> $categories Category aliases, an empty array disables the filter
     * @param string        $featured   One of the FEATURED_* constants
     *
     * @return Collection<static>|null
     */
    public static function findPublishedByFilter(array $categories = [], string $featured = self::FEATURED_ALL, array $options = []): Collection|null
    {
        [$columns, $values] = self::buildFilter($categories, $featured);

        if ([] === $columns) {
            return static::findAll($options);
        }

        return static::findBy($columns, $values, $options);
    }

    /**
     * @param array<string> $categories
     */
    public static function countPublishedByFilter(array $categories = [], string $featured = self::FEATURED_ALL): int
    {
        [$columns, $values] = self::buildFilter($categories, $featured);

        if ([] === $columns) {
            return static::countAll();
        }

        return static::countBy($columns, $values);
    }

    /**
     * Finds a job by its ID or alias. Outside of the front end preview only
     * published jobs are returned.
     */
    public static function findPublishedByIdOrAlias(int|string $idOrAlias): static|null
    {
        $job = static::findByIdOrAlias($idOrAlias);

        if (null === $job || (!static::isPreviewMode([]) && !$job->isVisible())) {
            return null;
        }

        return $job;
    }

    /**
     * Returns true if the job is published and within its start/stop period.
     */
    public function isVisible(): bool
    {
        return self::isRowVisible($this->row());
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function isRowVisible(array $row): bool
    {
        if (!($row['published'] ?? false)) {
            return false;
        }

        $now = time();
        $start = (int) ($row['start'] ?? 0);
        $stop = (int) ($row['stop'] ?? 0);

        return ($start <= 0 || $start <= $now) && ($stop <= 0 || $stop > $now);
    }

    /**
     * @param array<string> $categories
     *
     * @return array{0: array<string>, 1: array<mixed>}
     */
    private static function buildFilter(array $categories, string $featured): array
    {
        $t = static::$strTable;
        $columns = [];
        $values = [];

        if (!static::isPreviewMode([])) {
            $time = Date::floorToMinute();
            $columns[] = "$t.published=1 AND ($t.start='' OR $t.start<=$time) AND ($t.stop='' OR $t.stop>$time)";
        }

        if (self::FEATURED_ONLY === $featured) {
            $columns[] = "$t.featured=1";
        } elseif (self::FEATURED_NONE === $featured) {
            $columns[] = "$t.featured=0";
        }

        // Categories are stored as a serialized array of aliases
        $categories = array_values(array_filter(array_map('strval', $categories), static fn (string $alias): bool => '' !== $alias));

        if ([] !== $categories) {
            $columns[] = '('.implode(' OR ', array_fill(0, \count($categories), "$t.categories LIKE ?")).')';

            foreach ($categories as $alias) {
                $values[] = '%"'.addcslashes($alias, '%_\\').'"%';
            }
        }

        return [$columns, $values];
    }
}
