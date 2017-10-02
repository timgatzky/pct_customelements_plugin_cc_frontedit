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
define('TL_SCRIPT', 'app.php');

/**
 * Initialize the system
 */
define('TL_MODE', 'FE');

$_subfolder = '';
$_subs = array();
$_dirs = array_filter(explode('/', $_SERVER['SCRIPT_NAME']));
foreach($_dirs as $i => $part)
{
	if($part == 'system' && $_dirs[$i+1] == 'modules')
	{
		break;
	}
	$_subs[] = $part;
}
if(count($_subs) > 0)
{
	$_subfolder = '/'.implode('/', $_subs).'/';
}

// contao 3 structure
if( file_exists( realpath($_SERVER['DOCUMENT_ROOT']). $_subfolder . '/system/initialize.php') )
{
	require_once realpath($_SERVER['DOCUMENT_ROOT']). $_subfolder . '/system/initialize.php';
}
// contao 4 structure runs in a relative subfolder
else if( file_exists( realpath($_SERVER['DOCUMENT_ROOT'].'/../') . '/system/initialize.php') )
{
	require_once realpath($_SERVER['DOCUMENT_ROOT'].'/../') . '/system/initialize.php';
}
else
{
	throw new \Exception('Contaos initialize.php not found');
}

/**
 * Instantiate the controller
 */
$objController = new \PCT\Contao\BackendMain;
$objController->run();
