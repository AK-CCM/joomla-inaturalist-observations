# iNaturalist Observations Module for Joomla!

This module embeds the recent [iNaturalist.org](https://www.inaturalist.org) observations of a specific user in your Joomla! v4/5 based website. You can optionally filter the results by taxon groups such as plants, fungi & lichens, animals, or a custom taxon (ID needed). The module uses server-side caching to reduce API calls, is fully GDPR compliant, and includes multilingual support.

<!-- ![Example Screenshot](screenshot.png) Optional -->

## 🌿 Features

- Displays latest observations from a specific iNaturalist user
- Optional filter by taxon group or custom taxon ID
- Configurable number of observations
- Server-side caching (configurable duration)
- Fully GDPR compliant
- Multilingual (English and German)
- GPL v3 licensed

## 📦 Installation

1. Download the latest ZIP package from the [Releases](https://github.com/AK-CCM/joomla-inaturalist-observations/releases) section.
2. Install the module via the Joomla! admin interface under *Extensions > Manage > Install*.
3. Go to *Extensions > Modules*, find **iNaturalist Observations**, and publish it in a suitable position.
4. Configure the module options:
   - Enter the iNaturalist username whose observations should be displayed. (required)
   - Optionally select an organism group e.g. Plants, Animals, Fungi including Lichens. (default: All Organisms)
   - Or choose **Custom** and enter a taxon ID to filter by that specific taxon.
   - Set how many recent observations should be displayed. (default: 5)
   - Define the cache duration in seconds. (default: 86400 seconds = 1 day)

## 🔒 Schutz von Cache-Inhalten

Damit zwischengespeicherte Texte und Bilder von Suchmaschinen weder indexiert noch aufgerufen werden können, werden beim Installieren des Moduls im Cache-Verzeichnis eine `robots.txt` und eine `.htaccess` erstellt.

### Apache Webserver

Falls die automatische Einrichtung der .htaccess-Datei mangels Berechtigungen nicht funktioniert, bitte die folgenden Zeilen manuell in die `.htaccess`-Datei im Cache-Verzeichnis einfügen:

```
# Verhindert den direkten Zugriff auf alle Dateien im Cache-Verzeichnis
<Files *>
    Order Deny,Allow
    Deny from all
</Files>

# Verhindert das Indexieren und Anzeigen der Inhalte durch Suchmaschinen
<IfModule mod_headers.c>
    Header set X-Robots-Tag "noindex, nofollow, noarchive, nosnippet"
</IfModule>
```

### nginx Webserver

Der nginx Webserver unterstützt keine .htaccess-Dateien. Hier bitte folgende Regeln in die zentrale Konfigurationsdatei `/etc/nginx/nginx.conf` einfügen, um das Cache-Verzeichnis zu schützen:
```
location /cache/mod_inaturalist_observations/ {
    # Verhindert den Zugriff auf das Cache-Verzeichnis
    deny all;  
    # Gibt den HTTP-Fehlercode 403 zurück, wenn auf das Verzeichnis zugegriffen wird
    return 403;
    # Verhindert das Indexieren und Anzeigen der Inhalte durch Suchmaschinen
    add_header X-Robots-Tag "noindex, nofollow, noarchive, nosnippet";
}
```

## ⚙️ Module Options

| **Parameter**  | **Description**                                 | **Default**      |
|----------------|-------------------------------------------------|------------------|
| `Username`     | iNaturalist username to fetch observations from | *(required)*     |
| `Taxon Filter` | Filter by taxon group or custom taxon ID        | *All Organisms*  |
| `Count`        | Number of recent observations to display        | `5`              |
| `Cache Time`   | Cache duration in hours                         | `24`             |

## 🌍 Localization

- 🇬🇧 `en-GB` (English)
- 🇩🇪 `de-DE` (Deutsch)

## 📜 License

This module is released under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

---

## ⚖️ Joomla! Trademark Disclaimer

This repository is not affiliated with or endorsed by the Joomla! Project. It is neither supported nor guaranteed by Joomla! or Open Source Matters.
