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

// Legends
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfj_legend'] = 'Google for Jobs';
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['expert_legend'] = 'Experten-Einstellungen';

// Fields
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjSchema'] = ['Strukturierte Daten ausgeben', 'Bettet das Schema.org-Markup (JobPosting) für Google for Jobs in die Detailseite ein.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjApi'] = ['Google Indexing API verwenden', 'Meldet neue, geänderte und gelöschte Stellenangebote automatisch an die Google Indexing API.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjJsonFileName'] = ['Name der JSON-Schlüsseldatei', 'Der Dateiname der Google-Service-Account-Schlüsseldatei (JSON).'];
$GLOBALS['TL_LANG']['tl_plakart_jobs_settings']['gfjApiAnalysis'] = [
    'Analyse-Modus (detailliertes Logging)',
    'Aktiviert ausführliche Google-Indexing-Logs zur Analyse. Im Live-Betrieb deaktivieren.',
];
