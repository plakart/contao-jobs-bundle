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
use Contao\CoreBundle\DataContainer\RecordLabel;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * Handles the assignment of companies to jobs: with a single company it is
 * always preselected, with several companies one has to be chosen and is shown
 * in the job list.
 */
class JobCompanyListener
{
    /**
     * @var array<int, string>|null
     */
    private array|null $companies = null;

    public function __construct(private readonly Connection $connection)
    {
    }

    #[AsCallback(table: 'tl_plakart_jobs', target: 'config.onload')]
    public function configureField(): void
    {
        $companies = $this->getCompanies();
        $field = &$GLOBALS['TL_DCA']['tl_plakart_jobs']['fields']['company'];

        if (1 === \count($companies)) {
            $field['default'] = array_key_first($companies);
            $field['eval']['includeBlankOption'] = false;
        } elseif (\count($companies) > 1) {
            $field['eval']['mandatory'] = true;
        }
    }

    /**
     * @return array<int, string> id => title
     */
    #[AsCallback(table: 'tl_plakart_jobs', target: 'fields.company.options')]
    public function getCompanies(): array
    {
        return $this->companies ??= $this->connection->fetchAllKeyValue('SELECT id, title FROM tl_plakart_jobs_companies ORDER BY title');
    }

    /**
     * Preselects the company if there is only one.
     */
    #[AsCallback(table: 'tl_plakart_jobs', target: 'fields.company.load')]
    public function loadCompany(mixed $value): mixed
    {
        $companies = $this->getCompanies();

        if (1 === \count($companies) && !isset($companies[(int) $value])) {
            return array_key_first($companies);
        }

        return $value;
    }

    /**
     * Adds the company to the label in the job list if there are several companies.
     */
    #[AsCallback(table: 'tl_plakart_jobs', target: 'list.label.label')]
    public function addCompanyToLabel(array $row, string $label, DataContainer|null $dc = null): object|string
    {
        $companies = $this->getCompanies();
        $company = $companies[(int) ($row['company'] ?? 0)] ?? null;

        if (\count($companies) >= 2 && null !== $company) {
            $label .= ' <span class="label-info">· '.htmlspecialchars($company, ENT_QUOTES | ENT_SUBSTITUTE).'</span>';
        }

        // Contao 6 escapes plain strings, HTML labels have to be returned as RecordLabel
        if (class_exists(RecordLabel::class)) {
            return RecordLabel::fromHtml($label);
        }

        return $label;
    }
}
