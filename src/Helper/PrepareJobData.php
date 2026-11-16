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

use Contao\CoreBundle\Image\Studio\Figure;
use Contao\FilesModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Plakart\ContaoJobsBundle\Model\PlakartJobsCategoriesModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsCompaniesModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsContactsModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsLocationsModel;
use Plakart\ContaoJobsBundle\Model\PlakartJobsModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PrepareJobData
{
    private const DEFAULT_COUNTRY = 'DE';

    /**
     * Returns the job data as an array for the templates: the database row plus
     * the resolved image, categories, employment types, locations, contacts and
     * company.
     *
     * @return array<string, mixed>
     */
    public static function forTemplate(PlakartJobsModel $job, array|string|null $imageSize = null, array|string|null $contactImageSize = null): array
    {
        $data = $job->row();

        $data['figure'] = $job->addImage && $job->singleSRC ? self::image($job->singleSRC, $imageSize, (bool) $job->fullsize) : null;
        $data['categories'] = self::categories($job->categories);
        $data['employmentType'] = self::employmentType($job->employmentType);
        $data['locations'] = self::locations($job->locations);
        $data['contacts'] = self::contacts($job->contacts, $contactImageSize);
        $data['company'] = self::company($job);

        return $data;
    }

    /**
     * Generates the URL of a job on the given reader page.
     */
    public static function url(PageModel $page, string $alias, bool $absolute = false): string
    {
        return System::getContainer()->get('contao.routing.content_url_generator')->generate(
            $page,
            ['parameters' => '/'.ltrim($alias, '/')],
            $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH,
        );
    }

    /**
     * @deprecated use PrepareJobData::url() instead
     */
    public static function alias(PageModel $page, string $alias): string
    {
        return self::url($page, $alias);
    }

    public static function image(string|null $singleSRC, array|string|null $size = null, bool $fullsize = false): Figure|null
    {
        if (!$singleSRC) {
            return null;
        }

        return System::getContainer()
            ->get('contao.image.studio')
            ->createFigureBuilder()
            ->fromUuid($singleSRC)
            ->setSize($size ?: null)
            ->enableLightbox($fullsize)
            ->buildIfResourceExists()
        ;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function categories(string|null $serialized): array
    {
        $categories = [];

        foreach (StringUtil::deserialize($serialized, true) as $alias) {
            if ($category = PlakartJobsCategoriesModel::findByIdOrAlias($alias)) {
                $categories[] = $category->row();
            }
        }

        return $categories;
    }

    /**
     * @return list<string>
     */
    public static function employmentType(string|null $serialized): array
    {
        return array_values(array_filter(StringUtil::deserialize($serialized, true)));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function locations(string|null $serialized): array
    {
        $locations = [];

        foreach (self::findInOrder(PlakartJobsLocationsModel::class, $serialized) as $location) {
            $locations[] = $location->row();
        }

        return $locations;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function contacts(string|null $serialized, array|string|null $imageSize = null): array
    {
        $contacts = [];

        foreach (self::findInOrder(PlakartJobsContactsModel::class, $serialized) as $contact) {
            $row = $contact->row();
            $row['figure'] = $contact->addImage ? self::image($contact->singleSRC, $imageSize) : null;

            $contacts[] = $row;
        }

        return $contacts;
    }

    /**
     * Returns the company of the job (the assigned one or the only existing
     * one) including the absolute URL of its logo.
     *
     * @return array<string, mixed>|null
     */
    public static function company(PlakartJobsModel $job): array|null
    {
        if (!$company = PlakartJobsCompaniesModel::findForJob($job)) {
            return null;
        }

        $row = $company->row();
        $row['logoUrl'] = '';

        if ($company->logo && $logo = FilesModel::findByUuid($company->logo)) {
            $request = System::getContainer()->get('request_stack')->getCurrentRequest();
            $path = implode('/', array_map('rawurlencode', explode('/', $logo->path)));

            $row['logoUrl'] = ($request?->getSchemeAndHttpHost() ?? '').'/'.$path;
        }

        return $row;
    }

    /**
     * Builds the schema.org jobLocation. Assigned locations take precedence over
     * the address fields of the job, which in turn fall back to the company address.
     *
     * @param array<string, mixed> $job The job data as returned by forTemplate()
     *
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public static function gfjJobLocation(array $job): array
    {
        $places = [];

        foreach ($job['locations'] ?? [] as $location) {
            $place = ['@type' => 'Place'];

            if ($location['title'] ?? '') {
                $place['name'] = $location['title'];
            }

            $place['address'] = self::gfjPostalAddress(
                trim(($location['street'] ?? '').' '.($location['streetNr'] ?? '')),
                (string) ($location['city'] ?? ''),
                (string) ($location['zip'] ?? ''),
                strtoupper((string) ($location['country'] ?? '') ?: self::DEFAULT_COUNTRY),
            );

            $places[] = $place;
        }

        if ([] === $places) {
            $company = $job['company'] ?? [];
            $street = ($job['street'] ?? '')
                ? trim($job['street'].' '.($job['streetNr'] ?? ''))
                : trim(($company['street'] ?? '').' '.($company['streetNr'] ?? ''));

            return [
                '@type' => 'Place',
                'address' => self::gfjPostalAddress(
                    $street,
                    (string) (($job['city'] ?? '') ?: ($company['city'] ?? '')),
                    (string) (($job['zip'] ?? '') ?: ($company['zip'] ?? '')),
                    strtoupper((string) ($company['country'] ?? '') ?: self::DEFAULT_COUNTRY),
                ),
            ];
        }

        return 1 === \count($places) ? $places[0] : $places;
    }

    /**
     * Builds the complete schema.org JobPosting structure.
     *
     * @param array<string, mixed> $job  The job data as returned by forTemplate()
     * @param string               $html The rendered job description (teaser and content elements)
     *
     * @return array<string, mixed>
     *
     * @see https://developers.google.com/search/docs/appearance/structured-data/job-posting
     */
    public static function gfjJobPosting(array $job, string $html): array
    {
        $gfj = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => StringUtil::decodeEntities((string) $job['title']),
            'description' => self::gfjDescription($html),
            'datePosted' => date('Y-m-d', (int) $job['date']),
        ];

        if ($job['employmentType'] ?? []) {
            $gfj['employmentType'] = 1 === \count($job['employmentType']) ? $job['employmentType'][0] : $job['employmentType'];
        }

        if ($company = $job['company'] ?? null) {
            $gfj['hiringOrganization'] = array_filter([
                '@type' => 'Organization',
                'name' => StringUtil::decodeEntities((string) $company['title']),
                'sameAs' => (string) $company['url'],
                'logo' => (string) $company['logoUrl'],
            ]);
        }

        $gfj['jobLocation'] = self::gfjJobLocation($job);

        if ($job['employmentValidThrough'] ?? '') {
            // Valid until the end of the selected day
            $gfj['validThrough'] = date('Y-m-d\T23:59:59P', (int) $job['employmentValidThrough']);
        }

        $baseSalary = self::gfjBaseSalary(
            $job['employmentSalary'] ?? null,
            $job['employmentSalaryMin'] ?? null,
            $job['employmentSalaryMax'] ?? null,
            $job['employmentSalaryPer'] ?? null,
        );

        if ($baseSalary) {
            $gfj['baseSalary'] = $baseSalary;
        }

        return $gfj;
    }

    /**
     * Builds the schema.org baseSalary structure (MonetaryAmount with a
     * QuantitativeValue). A min/max range takes precedence over a fixed value.
     *
     * @see https://schema.org/MonetaryAmount
     */
    public static function gfjBaseSalary(string|null $salary, string|null $min, string|null $max, string|null $unit): array|null
    {
        $value = ['@type' => 'QuantitativeValue'];

        if ($min || $max) {
            if ($min) {
                $value['minValue'] = self::salaryNumber($min);
            }

            if ($max) {
                $value['maxValue'] = self::salaryNumber($max);
            }
        } elseif ($salary) {
            $value['value'] = self::salaryNumber($salary);
        } else {
            return null;
        }

        if ($unit) {
            $value['unitText'] = $unit;
        }

        return [
            '@type' => 'MonetaryAmount',
            'currency' => 'EUR',
            'value' => $value,
        ];
    }

    public static function gfjDescription(string $text): string
    {
        $cleanText = strip_tags($text, '<ul><li><ol><br><b><i><strong><em><p><a><h2><h3>');

        return trim((string) preg_replace('/\s+/', ' ', StringUtil::restoreBasicEntities($cleanText)));
    }

    /**
     * @return array<string, string>
     */
    private static function gfjPostalAddress(string $street, string $city, string $zip, string $country): array
    {
        return array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $street,
            'addressLocality' => $city,
            'postalCode' => $zip,
            'addressCountry' => $country,
        ]);
    }

    /**
     * Loads the models of the serialized ID list and keeps the selected order.
     *
     * @template T of \Contao\Model
     *
     * @param class-string<T> $modelClass
     *
     * @return list<T>
     */
    private static function findInOrder(string $modelClass, string|null $serialized): array
    {
        $ids = array_values(array_filter(array_map('intval', StringUtil::deserialize($serialized, true))));

        if ([] === $ids) {
            return [];
        }

        $byId = [];

        foreach ($modelClass::findMultipleByIds($ids) ?? [] as $model) {
            $byId[(int) $model->id] = $model;
        }

        return array_values(array_filter(array_map(static fn (int $id) => $byId[$id] ?? null, $ids)));
    }

    private static function salaryNumber(string $value): float|string
    {
        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) ? (float) $normalized : $value;
    }
}
