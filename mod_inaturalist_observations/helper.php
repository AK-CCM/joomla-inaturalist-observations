<?php

defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class ModINatHelper
{
    public static function getData($params)
    {
        $userId       = trim($params->get('username'));
        $taxonFilter  = $params->get('taxon_filter', '');
        $customTaxon  = trim($params->get('taxon_custom'));
        $count        = (int) $params->get('count', 5);
        $cacheSeconds = (int) $params->get('cache_duration', 86400);

        if (!$userId) {
            return ['observations' => [], 'avatar' => '', 'username' => $userId];
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

        $results = $cache->get(
            function () use ($userId, $taxonId, $count) {
                $http = HttpFactory::getHttp();
                $url = 'https://api.inaturalist.org/v1/observations?user_id=' . urlencode($userId)
                     . '&order_by=observed_on&order=desc&per_page=' . $count;

                if ($taxonId !== '') {
                    $url .= '&taxon_id=' . $taxonId;
                }

                try {
                    $response = $http->get($url);
                    return json_decode($response->body, true);
                } catch (Exception $e) {
                    return [];
                }
            },
            [$cacheKey],
            $cacheSeconds
        );

        $observations = $results['results'] ?? [];

        $cachedObservations = [];

        $cachePath = JPATH_SITE . '/cache/mod_inaturalist_observations/';
        if (!Folder::exists($cachePath)) {
            Folder::create($cachePath);
        }

        foreach ($observations as $observation) {
            if (!empty($observation['photos'][0]['url'])) {
                $photoUrl = str_replace('square', 'medium', $observation['photos'][0]['url']);
                $localFilename = $cachePath . md5($photoUrl) . '.jpg';
                $localRelPath = 'cache/mod_inaturalist_observations/' . md5($photoUrl) . '.jpg';

                if (!File::exists($localFilename)) {
                    try {
                        $http = HttpFactory::getHttp();
                        $response = $http->get($photoUrl);
                        if ($response->code === 200) {
                            File::write($localFilename, $response->body);
                        }
                    } catch (Exception $e) {
                        // Fehler ignorieren
                    }
                }

                $observation['local_photo'] = $localRelPath;
            }
            $cachedObservations[] = $observation;
        }

        // Benutzer-Avatar laden
        $avatarUrl = '';
        if (!empty($observations[0]['user']['icon_url'])) {
            $avatarRemote = $observations[0]['user']['icon_url'];
            $localAvatarFilename = $cachePath . md5($avatarRemote) . '.jpg';
            $localAvatarRelPath = 'cache/mod_inaturalist_observations/' . md5($avatarRemote) . '.jpg';

            if (!File::exists($localAvatarFilename)) {
                try {
                    $http = HttpFactory::getHttp();
                    $response = $http->get($avatarRemote);
                    if ($response->code === 200) {
                        File::write($localAvatarFilename, $response->body);
                    }
                } catch (Exception $e) {
                    // Fehler ignorieren
                }
            }

            $avatarUrl = $localAvatarRelPath;
        }

        return [
            'observations' => $cachedObservations,
            'avatar' => $avatarUrl,
            'username' => $userId,
        ];
    }
}
