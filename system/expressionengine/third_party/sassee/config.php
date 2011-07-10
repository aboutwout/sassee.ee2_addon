<?php

if ( ! defined('SASSEE_VERSION'))
{
  define('SASSEE_VERSION', '0.9');
  define('SASSEE_NAME', 'Sassee');
  define('SASSEE_DESCRIPTION', 'A SASS parser for ExpressionEngine.');  
  define('SASSEE_DOCUMENTATION', 'http://support.baseworks.nl/discussions/sassee');
  define('SASSEE_DEBUG', FALSE);
}

$config['name'] = SASSEE_NAME;
$config['version'] = SASSEE_VERSION;
$config['description'] = SASSEE_DESCRIPTION;
$config['nsm_addon_updater']['versions_xml'] = '';