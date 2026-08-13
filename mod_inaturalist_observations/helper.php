<?php

defined('_JEXEC') or die;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\Http\HttpFactory;

class ModINatHelper
{
    /**
     * Version des Daten-Caches.
     *
     * Bei Änderungen an der API-Abfrage oder Datenverarbeitung erhöhen,
     * damit alte Cache-Einträge nicht weiterverwendet werden.
     */
    private const CACHE_VERSION = 'v2';

    /**
     * Ruft die iNaturalist-Beobachtungen ab und bereitet sie für
     * die Darstellung im Modul auf.
     *
     * @param   \Joomla\Registry\Registry  $params  Modulparameter
     *
     * @return  array
     */
    public static function getData($params)
    {
        $userId       = trim((string) $params->get('username'));
        $taxonFilter  = $params->get('taxon_filter', '');
        $customTaxon  = trim((string) $params->get('taxon_custom'));
        $count        = max(1, (int) $params->get('count', 5));
        $cacheSeconds = max(0, (int) $params->get('cache_duration', 86400));

        if ($userId === '') {
            return [
                'observations' => [],
                'avatar'       => '',
                'username'     => '',
            ];
        }

        /*
         * Taxon-ID ermitteln.
         */
        $taxonId = '';

        if ($taxonFilter === 'custom' && is_numeric($customTaxon)) {
            $taxonId = (string) $customTaxon;
        } elseif (is_numeric($taxonFilter)) {
            $taxonId = (string) $taxonFilter;
        }

        /*
         * Sprache des aktuellen Frontend-Nutzers ermitteln.
         */
        $language   = Factory::getLanguage();
        $joomlaLang = $language->getTag();
        $locale     = substr($joomlaLang, 0, 2);

        /*
         * Cache-Lebensdauer.
         *
         * Die Modulkonfiguration verwendet Sekunden.
         * Der Joomla-Cache erwartet Minuten.
         */
        $cacheLifetime = max(1, (int) ceil($cacheSeconds / 60));

        /*
         * Eindeutigen Cache-Key erzeugen.
         *
         * CACHE_VERSION verhindert, dass Einträge aus älteren
         * Versionen des Helpers weiterverwendet werden.
         */
        $cacheKey = self::CACHE_VERSION . '_inat_obs_' . md5(
            $userId
            . '|'
            . $taxonId
            . '|'
            . $count
            . '|'
            . $locale
        );

        /*
         * HTTP-Client erzeugen.
         *
         * Joomla\Http\HttpFactory::getHttp() ist eine
         * Instanzmethode.
         */
        $httpFactory = new HttpFactory();
        $http        = $httpFactory->getHttp();

        /*
         * Callback zum Abruf der iNaturalist-Daten.
         */
        $loadObservations = function () use (
            $http,
            $userId,
            $taxonId,
            $count,
            $locale
        ) {
            try {
                $query = [
                    'user_id'  => $userId,
                    'order_by' => 'observed_on',
                    'order'    => 'desc',
                    'per_page' => $count,
                    'locale'   => $locale,
                ];

                if ($taxonId !== '') {
                    $query['taxon_id'] = $taxonId;
                }

                $url = 'https://api.inaturalist.org/v1/observations?'
                    . http_build_query(
                        $query,
                        '',
                        '&',
                        PHP_QUERY_RFC3986
                    );

                $response = $http->get($url);

                if ($response->getStatusCode() !== 200) {
                    return [];
                }

                $body = (string) $response->getBody();

                if ($body === '') {
                    return [];
                }

                $data = json_decode($body, true);

                if (
                    json_last_error() !== JSON_ERROR_NONE
                    || !is_array($data)
                    || !isset($data['results'])
                    || !is_array($data['results'])
                ) {
                    return [];
                }

                return $data;
            } catch (\Throwable $e) {
                return [];
            }
        };

        /*
         * iNaturalist-Daten laden.
         */
        if ($cacheSeconds > 0) {
            $cacheControllerFactory = Factory::getContainer()
                ->get(CacheControllerFactoryInterface::class);

            $cacheController = $cacheControllerFactory->createCacheController(
                'callback',
                [
                    'defaultgroup' => 'mod_inaturalist',
                    'caching'      => true,
                    'lifetime'     => $cacheLifetime,
                ]
            );

            $results = $cacheController->get(
                $loadObservations,
                [],
                $cacheKey
            );
        } else {
            $results = $loadObservations();
        }

        if (!is_array($results)) {
            $results = [];
        }

        $observations = $results['results'] ?? [];

        if (!is_array($observations)) {
            $observations = [];
        }

        /*
         * Verzeichnis für lokalen Bildcache erstellen.
         */
        $cachePath = JPATH_SITE . '/cache/mod_inaturalist_observations/';

        if (!is_dir($cachePath)) {
            if (
                !mkdir($cachePath, 0755, true)
                && !is_dir($cachePath)
            ) {
                $cachePath = '';
            }
        }

        /*
         * Beobachtungsfotos lokal zwischenspeichern.
         */
        $cachedObservations = [];

        foreach ($observations as $observation) {
            if (
                $cachePath !== ''
                && !empty($observation['photos'][0]['url'])
            ) {
                /*
                 * iNaturalist liefert standardmäßig z. B.
                 * eine "square"-URL.
                 *
                 * Für die Darstellung verwenden wir "medium".
                 */
                $photoUrl = str_replace(
                    'square',
                    'medium',
                    (string) $observation['photos'][0]['url']
                );

                $filename = md5($photoUrl) . '.jpg';

                $localFilename = $cachePath . $filename;

                $localRelPath =
                    'cache/mod_inaturalist_observations/' . $filename;

                /*
                 * Bild nur herunterladen, wenn es noch nicht
                 * im lokalen Cache vorhanden ist.
                 */
                if (!is_file($localFilename)) {
                    try {
                        $response = $http->get($photoUrl);

                        if ($response->getStatusCode() === 200) {
                            $body = (string) $response->getBody();

                            if ($body !== '') {
                                file_put_contents(
                                    $localFilename,
                                    $body,
                                    LOCK_EX
                                );
                            }
                        }
                    } catch (\Throwable $e) {
                        // Fehler beim Bilddownload ignorieren.
                    }
                }

                /*
                 * Nur einen gültigen lokalen Bildpfad übergeben.
                 */
                if (is_file($localFilename)) {
                    $observation['local_photo'] = $localRelPath;
                }
            }

            $cachedObservations[] = $observation;
        }

        /*
         * Benutzer-Avatar laden.
         */
        $avatarUrl = '';

        if (
            $cachePath !== ''
            && !empty($observations[0]['user']['icon_url'])
        ) {
            $avatarRemote =
                (string) $observations[0]['user']['icon_url'];

            $avatarFilename = md5($avatarRemote) . '.jpg';

            $localAvatarFilename =
                $cachePath . $avatarFilename;

            $localAvatarRelPath =
                'cache/mod_inaturalist_observations/'
                . $avatarFilename;

            if (!is_file($localAvatarFilename)) {
                try {
                    $response = $http->get($avatarRemote);

                    if ($response->getStatusCode() === 200) {
                        $body = (string) $response->getBody();

                        if ($body !== '') {
                            file_put_contents(
                                $localAvatarFilename,
                                $body,
                                LOCK_EX
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    // Fehler beim Avatar-Download ignorieren.
                }
            }

            if (is_file($localAvatarFilename)) {
                $avatarUrl = $localAvatarRelPath;
            }
        }

        /*
         * Daten an das Modul zurückgeben.
         */
        return [
            'observations' => $cachedObservations,
            'avatar'       => $avatarUrl,
            'username'     => $userId,
        ];
    }
}
