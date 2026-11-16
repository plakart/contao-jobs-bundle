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
$GLOBALS['TL_LANG']['tl_plakart_jobs']['title_legend'] = 'Titel und Alias';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['date_legend'] = 'Datum und Uhrzeit';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['job_legend'] = 'Job-Details';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['meta_legend'] = 'Metadaten';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['teaser_legend'] = 'Unterüberschrift und Teaser';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['image_legend'] = 'Bild-Einstellungen';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['expert_legend'] = 'Experten-Einstellungen';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['publish_legend'] = 'Veröffentlichung';

// Fields
$GLOBALS['TL_LANG']['tl_plakart_jobs']['title'] = ['Titel', 'Bitte geben Sie den Titel des Stellenangebots ein.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['featured'] = ['Job hervorheben', 'Das Stellenangebot in einer Liste hervorgehobener Jobs anzeigen.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['alias'] = ['Job-Alias', 'Der Job-Alias ist eine eindeutige Referenz, die anstelle der numerischen ID aufgerufen werden kann.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['date'] = ['Datum', 'Bitte geben Sie das Datum des Stellenangebots ein.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['time'] = ['Uhrzeit', 'Bitte geben Sie die Uhrzeit des Stellenangebots ein.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['categories'] = ['Kategorien', 'Bitte wählen Sie eine oder mehrere Kategorien aus.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['street'] = ['Straße', 'Die Straße des Arbeitsortes.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['streetNr'] = ['Hausnummer', 'Die Hausnummer des Arbeitsortes.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['zip'] = ['Postleitzahl', 'Die Postleitzahl des Arbeitsortes.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['city'] = ['Ort', 'Der Ort des Arbeitsortes.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentType'] = ['Beschäftigungsart', 'Bitte wählen Sie eine oder mehrere Beschäftigungsarten aus.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentValidThrough'] = ['Bewerbungsfrist', 'Das Datum, bis zu dem das Stellenangebot gültig ist.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalary'] = ['Gehalt (fester Wert)', 'Das feste Grundgehalt als Zahl ohne Tausendertrennzeichen, z. B. 3500. Wird ignoriert, wenn eine Gehaltsspanne angegeben ist.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryMin'] = ['Gehalt (min.)', 'Die Untergrenze der Gehaltsspanne als Zahl ohne Tausendertrennzeichen.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryMax'] = ['Gehalt (max.)', 'Die Obergrenze der Gehaltsspanne als Zahl ohne Tausendertrennzeichen.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryPer'] = ['Gehalt pro', 'Der Zeitraum, auf den sich das Gehalt bezieht.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['pageTitle'] = ['Seitentitel', 'Hier können Sie einen individuellen Seitentitel eingeben.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['robots'] = ['Robots-Tag', 'Hier können Sie festlegen, wie Suchmaschinen das Stellenangebot behandeln.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['description'] = ['Meta-Beschreibung', 'Hier können Sie eine individuelle Meta-Beschreibung eingeben.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['subheadline'] = ['Unterüberschrift', 'Hier können Sie eine Unterüberschrift eingeben.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['teaser'] = ['Teaser', 'Der Teaser kann in der Job-Liste anstelle des vollständigen Beitrags angezeigt werden.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['addImage'] = ['Ein Bild hinzufügen', 'Dem Stellenangebot ein Bild hinzufügen.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['cssClass'] = ['CSS-Klasse', 'Hier können Sie eine oder mehrere CSS-Klassen eingeben.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['published'] = ['Job veröffentlichen', 'Das Stellenangebot auf der Webseite anzeigen.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['start'] = ['Anzeigen ab', 'Das Stellenangebot erst ab diesem Zeitpunkt auf der Webseite anzeigen.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['stop'] = ['Anzeigen bis', 'Das Stellenangebot nur bis zu diesem Zeitpunkt auf der Webseite anzeigen.'];

// References
$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentType_options'] = [
    'FULL_TIME'  => 'Vollzeit',
    'PART_TIME'  => 'Teilzeit',
    'CONTRACTOR' => 'Freie Mitarbeit',
    'TEMPORARY'  => 'Befristet',
    'INTERN'     => 'Praktikum',
    'VOLUNTEER'  => 'Ehrenamtlich',
    'PER_DIEM'   => 'Tagesbasis',
    'OTHER'      => 'Sonstiges',
];

$GLOBALS['TL_LANG']['tl_plakart_jobs']['employmentSalaryPer_options'] = [
    'HOUR'  => 'Stunde',
    'DAY'   => 'Tag',
    'WEEK'  => 'Woche',
    'MONTH' => 'Monat',
    'YEAR'  => 'Jahr',
];

// Operations
$GLOBALS['TL_LANG']['tl_plakart_jobs']['toggle'] = ['Job veröffentlichen/unveröffentlichen', 'Job ID %s veröffentlichen/unveröffentlichen'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['feature'] = ['Job hervorheben/zurückstellen', 'Job ID %s hervorheben/zurückstellen'];

// Locations and contact persons
$GLOBALS['TL_LANG']['tl_plakart_jobs']['contact_legend'] = 'Ansprechpartner';
$GLOBALS['TL_LANG']['tl_plakart_jobs']['locations'] = ['Standorte', 'Bitte wählen Sie einen oder mehrere Standorte aus. Ist kein Standort gewählt, gilt die unten angegebene Adresse bzw. die Adresse des Unternehmens.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['contacts'] = ['Ansprechpartner', 'Bitte wählen Sie einen oder mehrere Ansprechpartner aus.'];
$GLOBALS['TL_LANG']['tl_plakart_jobs']['company'] = ['Unternehmen', 'Das einstellende Unternehmen. Gibt es nur ein Unternehmen, ist es automatisch ausgewählt.'];
