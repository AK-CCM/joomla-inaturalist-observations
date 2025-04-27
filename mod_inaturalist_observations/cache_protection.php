<?php

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;

class mod_inaturalist_observationsInstallerScript
{
    public function install($parent)
    {
        $cachePath = JPATH_SITE . '/cache/mod_inaturalist_observations';

        // Ordner anlegen, falls nicht vorhanden
        if (!Folder::exists($cachePath)) {
            Folder::create($cachePath);
        }

        // .htaccess kopieren
        $sourceHtaccess = __DIR__ . '/cache_protection/htaccess.txt';
        $targetHtaccess = $cachePath . '/.htaccess';
        if (File::exists($sourceHtaccess) && !File::exists($targetHtaccess)) {
            File::copy($sourceHtaccess, $targetHtaccess);
        }

        // robots.txt kopieren
        $sourceRobots = __DIR__ . '/cache_protection/robots.txt';
        $targetRobots = $cachePath . '/robots.txt';
        if (File::exists($sourceRobots) && !File::exists($targetRobots)) {
            File::copy($sourceRobots, $targetRobots);
        }
    }
}
