<?php

defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;

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

        // Fetch user information (including user avatar)
        $http = HttpFactory::getHttp();
        $userInfoUrl = 'https://api.inaturalist.org/v1/users/' . urlencode($userId);

        // Generate a unique cache key based on username, taxon filter, custom taxon ID, count, language, and user image
        $lang = JFactory::getLanguage()->getTag();
        $cacheKey = 'inat_obs_' . md5($userId . '|' . $taxonFilter . '|' . $customTaxon . '|' . $count . '|' . $lang);

        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
            ->createCacheController('callback', ['defaultgroup' => 'mod_inaturalist']);

        return $cache->get(
            function () use ($userId, $taxonId, $count, $http, $userInfoUrl) {
                try {
                    // Fetch user information for the avatar
                    $userResponse = $http->get($userInfoUrl);
                    $userBody = json_decode($userResponse->body, true);
                    $userAvatar = $userBody['results'][0]['icon'] ?? '';

                    // Now fetch the observations
                    $url = 'https://api.inaturalist.org/v1/observations?user_id=' . urlencode($userId)
                        . '&order_by=observed_on&order=desc&per_page=' . $count;

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
                    return [];
                }
            },
            [$cacheKey],
            $cacheSeconds
        );
    }
}
