<?php
defined('_JEXEC') or die;

class ModINatHelper {
    public static function getObservations($params) {
        $user = $params->get('user_id', 'ak_ccm');
        $taxon = $params->get('taxon_id', '47170');
        $count = (int) $params->get('count', 5);
        $cacheTime = (int) $params->get('cache_time', 86400);

        $url = 'https://api.inaturalist.org/v1/observations?user_id=' . $user . '&taxon_id=' . $taxon . '&per_page=' . $count . '&order_by=observed_on&order=desc';
        $cacheFile = JPATH_SITE . '/cache/inaturalist_' . $user . '_' . $taxon . '.json';

        if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) > $cacheTime) {
            $json = file_get_contents($url);
            if ($json) {
                file_put_contents($cacheFile, $json);
            }
        } else {
            $json = file_get_contents($cacheFile);
        }

        if ($json) {
            return json_decode($json, true)['results'];
        }

        return [];
    }
}
