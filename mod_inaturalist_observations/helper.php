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

        // Cache Duration wird jetzt in Stunden eingegeben -> umrechnen in Sekunden
        $cacheSeconds = (int) $params->get('cache_duration', 24) * 3600;

        if (!$userId) {
            return [];
        }

        $taxonId = '';
        if ($taxonFilter === 'custom' && is_numeric($customTaxon)) {
            $taxonId = $customTaxon;
        } elseif (is_numeric($taxonFilter)) {
            $taxonId = $taxonFilter;
        }

        // Generate a unique cache key based on username, taxon filter, custom taxon ID, count, and language
        $lang     = JFactory::getLanguage()->getTag();
        $cacheKey = 'inat_obs_' . md5($userId . '|' . $taxonFilter . '|' . $customTaxon . '|' . $count . '|' . $lang);

        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)
                   ->createCacheController('callback', ['defaultgroup' => 'mod_inaturalist']);

        return $cache->get(
            function () use ($userId, $taxonId, $count) {
                $http   = HttpFactory::getHttp();
                $url    = 'https://api.inaturalist.org/v1/observations?user_id=' . urlencode($userId)
                        . '&order_by=observed_on&order=desc&per_page=' . $count;

                if ($taxonId !== '') {
                    $url .= '&taxon_id=' . $taxonId;
                }

                try {
                    $response = $http->get($url);
                    $body     = json_decode($response->body, true);

                    return $body['results'] ?? [];
                } catch (Exception $e) {
                    return [];
                }
            },
            [$cacheKey],
            $cacheSeconds
        );
    }
}
