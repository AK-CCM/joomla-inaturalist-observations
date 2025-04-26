<?php
defined('_JEXEC') or die;
require_once __DIR__ . '/helper.php';
use Joomla\CMS\Helper\ModuleHelper;
$data = ModINatHelper::getData($params);
$avatar = $data['avatar'] ?? '';
require ModuleHelper::getLayoutPath('mod_inaturalist_observations', $params->get('layout', 'default'));
