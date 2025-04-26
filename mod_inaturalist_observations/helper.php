<?php

defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;

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

        // Sprache berücksichtigen
        $lang = Factory::getApplication()->getLanguage()->getTag();
        
        // Cache-Key: Benutzer, Taxon, Count, Sprache
        $cacheKey = 'inat_obs_' . md5($userId . $taxonId . $count . $lang);

        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('callback', ['defaultgroup' => 'mod_inaturalist']);

        return $cache->get(
            function () use ($userId, $taxonId, $count) {
                $http = HttpFactory::getHttp();

                try {
                    // Benutzerinfos laden (Avatar)
                    $userResponse = $http->get('https://api.inaturalist.org/v1/users/' . urlencode($userId));
                    $userBody = json_decode($userResponse->body, true);
                    $userAvatar = '';
                    if (!empty($userBody['results'][0]['icon'])) {
                        $userAvatar = $userBody['results'][0]['icon'];
                    }

                    // Beobachtungen laden
                    $url = 'https://api.inaturalist.org/v1/observations?user_id=' . urlencode($userId)
                        . '&order_by=observed_on&order=desc&per_page=' . $count
                        . '&locale=' . urlencode(substr($lang, 0, 2)); // Sprachcode auf 2 Buchstaben kürzen

                    if ($taxonId !== '') {
                        $url .= '&taxon_id=' . $taxonId;
                    }

                    $response = $http->get($url);
                    $body = json_decode($response->body, true);

                    return [
                        'observations' => $body['results'] ?? [],
                        'avatar' => $userAvatar,
                    ];
                } catch (Exception $e) {
                    return [
                        'observations' => [],
                        'avatar' => '',
                    ];
                }
            },
            [$cacheKey],
            $cacheSeconds
        );
    }
}
