<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2014, Premium Contao Webworks, Premium Contao Themes
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_tabltree
 * @link		http://contao.org
 */

// Set the script name
define('TL_SCRIPT', 'app.php');

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
$objPageTableTree = new \Contao\Controllers\FrontendPctTableTree;
$objPageTableTree->run();
