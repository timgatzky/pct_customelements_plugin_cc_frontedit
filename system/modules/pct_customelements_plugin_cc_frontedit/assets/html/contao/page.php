<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

// Set the script name
define('TL_SCRIPT', 'pct_customelements_plugin_cc_frontedit/assets/html/contao/page.php');

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');

// Apache server
if(strlen(strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'apache')) > 0 || strlen(strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'litespeed')) > 0 || strlen(strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'nginx')) > 0)
{
	$path_to_initialize = str_replace(substr($_SERVER['SCRIPT_FILENAME'], strpos($_SERVER['SCRIPT_FILENAME'],'system/modules')),'',$_SERVER['SCRIPT_FILENAME']).'system/initialize.php';
}

if(!file_exists($path_to_initialize) || strlen(strpos($path_to_initialize,'initialize.php')) < 1)
{
	throw new \Exception('Contaos initialize.php not found in: '.$path_to_initialize);
}

require_once $path_to_initialize;

/**
 * Instantiate the controller
 */
$objController = new \Contao\Controllers\FrontendPage;
$objController->run();
