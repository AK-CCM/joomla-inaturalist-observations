<?php

defined('_JEXEC') or die;

class ModINatObservationsHelper
{
    public static function getObservations($params)
    {
        // Basis-Konfiguration
        $userId = trim($params->get('user_id'));
        $taxonFilter = $params->get('taxon_filter', '');
        $customTaxonId = trim($params->get('custom_taxon_id'));
        $count = (int) $params->get('count', 5);
        $cacheTime = (int) $params->get('cache_time', 86400);

        // Taxon-ID bestimmen
        $taxonId = '';
        if ($taxonFilter === 'custom' && is_numeric($customTaxonId)) {
            $taxonId = $customTaxonId;
        } elseif (is_numeric($taxonFilter)) {
            $taxonId = $taxonFilter;
        }

        // Cache-Setup
        $cache = JFactory::getCache('mod_inaturalist_observations', '');
        $cache->setCaching(true);
        $cache->setLifeTime($cacheTime);

        // Caching-Schlüssel
        $cacheKey = md5("inat_{$userId}_{$taxonId}_{$count}");

        return $cache->get(
            function () use ($userId, $taxonId, $count) {
                return ModINatObservationsHelper::fetchObservations($userId, $taxonId, $count);
            },
            $cacheKey
        );
    }

    protected static function fetchObservations($userId, $taxonId, $count)
    {
        $baseUrl = 'https://api.inaturalist.org/v1/observations';
        $query = http_build_query([
            'user_login' => $userId,
            'per_page'   => $count,
            'order_by'   => 'observed_on',
            'order'      => 'desc',
            'taxon_id'   => $taxonId ?: null,
            'locale'     => 'en', // optional: could be dynamic later
        ]);

        $url = $baseUrl . '?' . $query;

        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            return $data['results'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }
}
