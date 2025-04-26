<?php
defined('_JEXEC') or die;
require_once __DIR__ . '/helper.php';
use Joomla\CMS\Helper\ModuleHelper;
$observations = ModINatHelper::getObservations($params);
require ModuleHelper::getLayoutPath('mod_inaturalist_observations', $params->get('layout', 'default'));
