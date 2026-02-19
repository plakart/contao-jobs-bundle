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
use Contao\DataContainer;
use Contao\Message;
use Contao\System;
use Contao\Config;

#[AsCallback(table: 'tl_plakart_jobs_settings', target: 'config.onsubmit')]
#[AsCallback(table: 'tl_plakart_jobs_settings', target: 'config.onload')]
class JobsSettingsWatcher
{
    public function __invoke(DataContainer|null $dc = null): void
    {
        if(Config::get('gfjApi')) {
            $fileName = trim((string) (Config::get('gfjJsonFileName') ?? ''));
            $kernelProjectDir = System::getContainer()->getParameter('kernel.project_dir');

            if ($fileName === '') {
                Message::addError('Keine Google Credentials Datei gefunden. Bitte Dateiname und anschließend die Datei in den /var Ordner hochladen.');
                return;
            }

            // einfache Sicherheitsprüfung
            if (str_contains($fileName, '/') || str_contains($fileName, '..')) {
                Message::addError('Ungültiger Dateiname.');
                return;
            }

            $projectDir = rtrim($kernelProjectDir, '/');
            $jsonPath = $projectDir . '/var/' . $fileName;

            if (!is_file($jsonPath)) {
                Message::addError(sprintf(
                    'Die Datei "%s" wurde nicht in /var gefunden.',
                    $fileName
                ));
                return;
            }

            Message::addConfirmation('Google Credentials Datei gefunden.');
        }
    }
}
