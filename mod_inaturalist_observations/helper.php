<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class ModINatHelper
{
    public static function getObservations($params)
    {
        $userId       = trim($params->get('username'));
        $taxonFilter  = $params->get('taxon_filter', '');
        $customTaxon  = trim($params->get('taxon_custom'));
        $count        = (int) $params->get('count', 5);
        $cacheSeconds = (int) $params->get('cache_duration', 86400);

        if (!$userId) {
            return [];
        }

        $taxonId = '';
        if ($taxonFilter === 'custom' && is_numeric($customTaxon)) {
            $taxonId = $customTaxon;
        } elseif (is_numeric($taxonFilter)) {
            $taxonId = $taxonFilter;
        }

        $cacheKey = 'inat_obs_' . md5($userId . $taxonId . $count);
        $cache    = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
                   ->createCacheController('callback', ['defaultgroup' => 'mod_inaturalist']);

        return $cache->get(
            function () use ($userId, $taxonId, $count) {
                $http = HttpFactory::getHttp();
                $basePath = JPATH_CACHE . '/mod_inaturalist_observations';

                // Sicherstellen, dass Cache-Verzeichnis existiert
                if (!Folder::exists($basePath)) {
                    Folder::create($basePath);
                }

                try {
                    // 1. Benutzerinfos holen (für Avatar)
                    $userInfoUrl = 'https://api.inaturalist.org/v1/users/' . urlencode($userId);
                    $userResponse = $http->get($userInfoUrl);
                    $userBody = json_decode($userResponse->body, true);
                    $userAvatarUrl = $userBody['results'][0]['icon'] ?? '';

                    // Benutzerbild lokal speichern
                    $userAvatarLocal = '';
                    if ($userAvatarUrl) {
                        $userAvatarLocal = ModINatHelper::downloadImage($userAvatarUrl, $basePath, 'avatar_' . md5($userAvatarUrl) . '.jpg');
                    }

                    // 2. Beobachtungen holen
                    $url = 'https://api.inaturalist.org/v1/observations?user_id=' . urlencode($userId)
                        . '&order_by=observed_on&order=desc&per_page=' . $count;

                    if ($taxonId !== '') {
                        $url .= '&taxon_id=' . $taxonId;
                    }

                    $response = $http->get($url);
                    $body = json_decode($response->body, true);
                    $observations = $body['results'] ?? [];

                    // Für jede Beobachtung das Foto lokal speichern
                    foreach ($observations as &$observation) {
                        if (!empty($observation['photos'][0]['url'])) {
                            $photoUrl = str_replace('square', 'medium', $observation['photos'][0]['url']);
                            $photoFilename = 'obs_' . (int)$observation['id'] . '.jpg';
                            $localPhoto = ModINatHelper::downloadImage($photoUrl, $basePath, $photoFilename);

                            $observation['local_photo'] = $localPhoto;
                        } else {
                            $observation['local_photo'] = '';
                        }
                    }

                    return [
                        'avatar' => $userAvatarLocal,
                        'observations' => $observations,
                    ];

                } catch (Exception $e) {
                    return [];
                }
            },
            [$cacheKey],
            $cacheSeconds
        );
    }

    // Hilfsfunktion zum Herunterladen und Speichern eines Bildes
    protected static function downloadImage($url, $savePath, $filename)
    {
        try {
            $http = HttpFactory::getHttp();
            $response = $http->get($url);

            if ($response->code === 200) {
                $fullPath = $savePath . '/' . $filename;
                File::write($fullPath, $response->body);

                // Rückgabe: Pfad relativ zur Joomla-Seite (für <img src>)
                return 'cache/mod_inaturalist_observations/' . $filename;
            }
        } catch (Exception $e) {
            // Ignorieren, wenn Download fehlschlägt
        }

        return '';
    }
}
