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

use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Plakart\ContaoJobsBundle\Model\PlakartJobsCategoriesModel;
use Contao\Config;
use Contao\FilesModel;
use Contao\Environment;

class PrepareJobData
{
    public static function alias(PageModel $page, string $alias): string
    {
        return System::getContainer()->get('contao.routing.content_url_generator')->generate($page, ['parameters' => '/'.$alias]);
    }

    public static function image(string $singleSRC, string|null $size = null, bool $fullsize = false):object
    {
        $figureBuilder = System::getContainer()
            ->get('contao.image.studio')
            ->createFigureBuilder();

        $figureBuilder
            ->fromUuid($singleSRC)
            ->setSize($size)
            ->enableLightbox($fullsize)
        ;

        return $figureBuilder->buildIfResourceExists();
    }

    public static function categories(string $serialized):array
    {
        $categories = StringUtil::deserialize($serialized, true);
        $array = [];

        foreach($categories as $category) {
            $array[] = PlakartJobsCategoriesModel::findByIdOrAlias($category)->row();
        }

        return $array;
    }

    public static function employmentType(string $serialized):array
    {
        return StringUtil::deserialize($serialized, true);
    }

    public static function gfjCompanyName():string
    {
        return Config::get('company') ?? '';
    }

    public static function gfjCompanyStreetAddress(string|null $street = null, string|null $streetNr = null):string
    {
        if($street) {
            return trim($street . ' ' . ($streetNr ?? ''));
        }

        return trim((string) Config::get('street') . ' ' . (string) Config::get('streetNr'));
    }

    public static function gfjCompanyZip(string|null $zip = null):string
    {
        return $zip ?: (string) Config::get('zip');
    }

    public static function gfjCompanyCity(string|null $city = null):string
    {
        return $city ?: Config::get('city');
    }

    public static function gfjCompanyLogo():string
    {
        $src = Config::get('logo');
        if($src && $logo = FilesModel::findByUuid($src)) {
            return Environment::get('url').$logo->path;
        }

        return '';
    }

    public static function gfjCompanyUrl():string
    {
        return Config::get('url') ?? '';
    }

    /**
     * Builds the schema.org baseSalary structure (MonetaryAmount with a
     * QuantitativeValue). A min/max range takes precedence over a fixed value.
     *
     * @see https://developers.google.com/search/docs/appearance/structured-data/job-posting
     * @see https://schema.org/MonetaryAmount
     */
    public static function gfjBaseSalary(string|null $salary, string|null $min, string|null $max, string|null $unit):array|null
    {
        $value = ['@type' => 'QuantitativeValue'];

        if($min || $max) {
            if($min) $value['minValue'] = self::salaryNumber($min);
            if($max) $value['maxValue'] = self::salaryNumber($max);
        } elseif($salary) {
            $value['value'] = self::salaryNumber($salary);
        } else {
            return null;
        }

        if($unit) $value['unitText'] = $unit;

        return [
            '@type' => 'MonetaryAmount',
            'currency' => 'EUR',
            'value' => $value
        ];
    }

    private static function salaryNumber(string $value):float|string
    {
        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) ? (float) $normalized : $value;
    }

    public static function gfjDescription(string $text):string
    {
        $cleanText = strip_tags($text, '<ul><li><ol><br><b><i><strong><em><p><a><h2><h3>');
        return trim(preg_replace('/\s+/', ' ', StringUtil::restoreBasicEntities($cleanText)));
    }

}