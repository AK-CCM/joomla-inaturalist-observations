<?php
defined('_JEXEC') or die;
require_once __DIR__ . '/helper.php';
$observations = ModINatHelper::getObservations($params);
require JModuleHelper::getLayoutPath('mod_inaturalist_observations');
