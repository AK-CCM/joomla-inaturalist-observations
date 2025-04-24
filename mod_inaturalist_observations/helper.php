<?php

defined('_JEXEC') or die;

class ModINaturalistObservationsHelper
{
    public static function getObservations($params)
    {
        $user = trim($params->get('user_id'));
        $taxon = trim($params->get('taxon_id'));
        $count = (int) $params->get('count', 5);
        $cacheTime = (int) $params->get('cache_time', 86400); // 1 Tag

        // Kein Benutzername? Keine Anfrage möglich
        if (empty($user)) {
            return [];
        }

        // API-Endpunkt vorbereiten
        $url = "https://api.inaturalist.org/v1/observations?user_id=" . urlencode($user)
             . "&per_page=" . $count
             . "&order=desc"
             . "&order_by=observed_on";

        // Wenn ein taxon_id gewählt wurde, ergänze es in der URL
        if (!empty($taxon)) {
            $url .= "&taxon_id=" . urlencode($taxon);
        }

        // Joomla Cache verwenden
        $cache = \Joomla\CMS\Factory::getCache('mod_inaturalist_observations', '');
        $cache->setCaching(true);
        $cache->setLifeTime($cacheTime);

        // Daten abrufen oder aus dem Cache holen
        return $cache->get(function () use ($url) {
            $options = [
                'http' => [
                    'method'  => 'GET',
                    'header'  => "User-Agent: Joomla-iNaturalist-Module\r\n"
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                return [];
            }

            $data = json_decode($response, true);

            return $data['results'] ?? [];
        }, md5($url));
    }
}
