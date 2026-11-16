# Contao Jobs Bundle

Stellenangebote in Contao pflegen, auf der Website ausgeben und an
Google for Jobs melden.

## Features

- **Stellenangebote** mit Teaser, Bild, Inhaltselementen, Gehalt, Beschäftigungsart und Bewerbungsfrist
- **Kategorien, Standorte, Ansprechpartner und Unternehmen** zentral anlegen und Jobs zuweisen
- **Job-Liste** als Inhaltselement mit Kategorie-Filter, Hervorhebung, Sortierung und Pagination
- **Job-Leser** als Frontend-Modul mit optionalem Bewerbungsformular
- **Formularfeld „Stellenangebote“**, das den Job automatisch an das Bewerbungsformular übergibt
- **Google for Jobs**: `JobPosting`-Markup (JSON-LD), Sitemap-Einträge und Google Indexing API
- **Insert-Tag** `{{job_url::ID}}` für Links auf die Detailseite
- Twig-Templates, lauffähig unter **Contao 5.3, 5.7 und 6**

## Voraussetzungen

- PHP 8.3 oder neuer
- Contao 5.3, 5.7 oder 6.x

## Installation

```bash
composer require plakart/contao-jobs-bundle
vendor/bin/contao-console contao:migrate
```

## Einrichtung in Kürze

1. Unter **Jobs → Unternehmen** das einstellende Unternehmen anlegen (bei Bedarf mehrere).
2. Unter **Jobs** Kategorien, Standorte und Ansprechpartner anlegen (optional) und Jobs erfassen.
3. Ein Frontend-Modul **Job-Leser** anlegen und auf einer Detailseite einbinden.
4. Auf der Übersichtsseite das Inhaltselement **Job-Liste** einfügen und die Detailseite als Weiterleitungsseite wählen.

## Erklärung der Funktionen

### Backend-Module (eigene Kategorie „Jobs“)

| Modul | Beschreibung |
|---|---|
| Stellenangebote | Stellenangebote samt Inhaltselementen |
| Kategorien | Kategorien zum Filtern der Job-Liste |
| Standorte | Standorte, die einem Job (auch mehrere) zugewiesen werden |
| Ansprechpartner | Ansprechpartner mit Position, E-Mail, Telefon und Bild |
| Unternehmen | Einstellende Unternehmen mit Adresse, Website und Logo |
| Einstellungen | Google-for-Jobs-Optionen |

### Inhaltselement „Job-Liste“

- Filter nach Kategorien
- alle, nur hervorgehobene oder keine hervorgehobenen Jobs
- Sortierung nach Datum oder Titel
- Gesamtzahl und Elemente pro Seite (Pagination)

Angezeigt werden nur veröffentlichte Jobs innerhalb ihres Anzeigezeitraums.

### Unternehmen

Gibt es nur ein Unternehmen, gilt es automatisch für alle Stellenangebote und
ist im Job vorausgewählt. Bei mehreren Unternehmen wird jedem Job eines
zugewiesen (Pflichtfeld); die Job-Liste im Backend zeigt es als Label an und
lässt sich danach filtern. Das Unternehmen erscheint als `hiringOrganization`
im Google-for-Jobs-Markup und steht in den Templates als `company` bereit.

Beim Update werden die bisherigen Unternehmensangaben aus den Einstellungen
per Migration als Unternehmen übernommen.

### Frontend-Modul „Job-Leser“

- Detailansicht mit Inhaltselementen, Standorten, Ansprechpartnern und optionalem Bewerbungsformular
- Seitentitel, Meta-Beschreibung und Robots-Tag aus dem Job
- In der Frontend-Vorschau werden auch unveröffentlichte Jobs angezeigt, es wird kein 404 ausgelöst

### Formularfeld „Stellenangebote“

Auf einer Job-Detailseite wird der aktuelle Job als verstecktes Feld übergeben,
überall sonst erscheint ein Auswahlmenü mit allen veröffentlichten Jobs. In
E-Mails und gespeicherten Daten steht der Titel des Jobs.

### Insert-Tags

| Insert-Tag | Ausgabe |
|---|---|
| `{{job_url::ID}}` | URL der Detailseite eines Jobs (statt der ID ist auch der Alias möglich) |
| `{{job_url::ID::absolute}}` | Absolute URL inklusive Domain |

Als Detailseite dient die Weiterleitungsseite der Job-Liste. Gibt es mehrere,
wird bevorzugt die Seite innerhalb des aktuellen Startpunkts verwendet.

### Google for Jobs

- **Strukturierte Daten:** Unter **Jobs → Einstellungen** „Strukturierte Daten ausgeben“ aktivieren. Die mit dem Google-Symbol markierten Felder fließen in das Markup ein.
- **Standort:** Zugewiesene Standorte werden als `jobLocation` ausgegeben. Ohne Standort gilt die Adresse im Job, ersatzweise die Adresse des Unternehmens.
- **Sitemap:** Veröffentlichte Jobs ohne `noindex` werden automatisch in die Sitemap aufgenommen.
- **Indexing API:** Service-Account-Schlüssel (JSON) nach `var/` hochladen, dann „Google Indexing API verwenden“ aktivieren und den Dateinamen eintragen. Änderungen werden beim Speichern, Löschen und Wiederherstellen gemeldet, zusätzlich läuft täglich ein Cronjob.

## Templates

Alle Templates sind Twig-Templates und lassen sich wie gewohnt überschreiben:

- `content_element/plakart_jobs_list.html.twig`
- `frontend_module/plakart_jobs_reader.html.twig`
- `form_job_select_field.html.twig`

In der Job-Liste steht die URL der Detailseite als `job.url` zur Verfügung.
`job.alias` enthält aus Gründen der Abwärtskompatibilität ebenfalls die URL,
sobald eine Weiterleitungsseite gewählt ist.

## Lizenz

LGPL-3.0-or-later, siehe [LICENSE](LICENSE).
