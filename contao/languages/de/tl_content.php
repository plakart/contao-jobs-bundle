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
$GLOBALS['TL_LANG']['tl_content']['plakartjobs_legend'] = 'Job-Einstellungen';

// Fields
$GLOBALS['TL_LANG']['tl_content']['jumpTo'] ??= ['Weiterleitungsseite', 'Bitte wählen Sie die Seite, zu der Besucher beim Anklicken eines Stellenangebots weitergeleitet werden.'];
$GLOBALS['TL_LANG']['tl_content']['jobsCategories'] = ['Nach Kategorien filtern', 'Nur Stellenangebote aus den ausgewählten Kategorien anzeigen.'];
$GLOBALS['TL_LANG']['tl_content']['jobsSortBy'] = ['Sortieren nach', 'Bitte wählen Sie die Sortierreihenfolge der Stellenangebote.'];

// References
$GLOBALS['TL_LANG']['tl_content']['sortByOptions'] = [
    'date_asc'  => 'Datum aufsteigend',
    'date_desc' => 'Datum absteigend',
    'name_asc'  => 'Titel aufsteigend (A–Z)',
    'name_desc' => 'Titel absteigend (Z–A)',
];

$GLOBALS['TL_LANG']['tl_content']['jobsFeatured'] = ['Hervorgehobene Jobs', 'Hier legen Sie fest, wie hervorgehobene Stellenangebote behandelt werden.'];
$GLOBALS['TL_LANG']['tl_content']['jobsFeaturedOptions'] = [
    'all_items' => 'Alle Jobs anzeigen',
    'featured' => 'Nur hervorgehobene Jobs anzeigen',
    'unfeatured' => 'Hervorgehobene Jobs überspringen',
];
