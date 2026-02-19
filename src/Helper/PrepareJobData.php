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
        return $street ? $street : (Config::get('company') . ' ' . $streetNr ? $streetNr : Config::get('streetNr'));
    }

    public static function gfjCompanyZip(string|null $zip = null):string
    {
        return $zip ?? Config::get('zip');
    }

    public static function gfjCompanyCity(string|null $city = null):string
    {
        return $city ?? Config::get('city');
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

    public static function gfjDescription(string $text):string
    {
        $cleanText = strip_tags($text, '<ul><li><ol><br><b><i><strong><em><p><a><h2><h3>');
        return trim(preg_replace('/\s+/', ' ', StringUtil::restoreBasicEntities($cleanText)));
    }

}