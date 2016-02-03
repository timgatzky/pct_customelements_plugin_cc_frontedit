<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2015
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements
 * @subpackage	pct_customelements_plugin_customcatalog
 * @subpackage	pct_customelements_customcatalog_formedit
 * @link		http://contao.org
 */

$path = 'system/modules/pct_customelements_plugin_cc_frontedit';

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'PCT\CustomCatalog',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Core
	'PCT\CustomCatalog\FrontEdit'											=> $path.'/PCT/CustomCatalog/FrontEdit.php',
	'PCT\CustomCatalog\FrontEdit\CustomCatalog'								=> $path.'/PCT/CustomCatalog/FrontEdit/CustomCatalog.php',
	'PCT\CustomCatalog\FrontEdit\Callbacks'									=> $path.'/PCT/CustomCatalog/FrontEdit/Callbacks.php',
	'PCT\CustomCatalog\FrontEdit\Controller'								=> $path.'/PCT/CustomCatalog/FrontEdit/Controller.php',
	'PCT\CustomCatalog\FrontEdit\Hooks'										=> $path.'/PCT/CustomCatalog/FrontEdit/Hooks.php',
	
	'PCT\CustomCatalog\FrontEdit\RowTemplate'								=> $path.'/PCT/CustomCatalog/FrontEdit/RowTemplate.php',
	'PCT\CustomCatalog\FrontEdit\TemplateAttribute'							=> $path.'/PCT/CustomCatalog/FrontEdit/TemplateAttribute.php',
	'PCT\CustomCatalog\FrontEdit\FrontendTemplate'							=> $path.'/PCT/CustomCatalog/FrontEdit/FrontendTemplate.php',
	
	// Frontend
	'PCT\CustomElements\Plugins\FrontEdit\Frontend\ModuleReader'			=> $path.'/PCT/CustomElements/Plugins/FrontEdit/Frontend/ModuleReader.php',
	'PCT\CustomElements\Plugins\FrontEdit\Frontend\ModuleList'				=> $path.'/PCT/CustomElements/Plugins/FrontEdit/Frontend/ModuleList.php',
	
	// Pages
	'PCT\CustomCatalog\FrontEdit\PageNoPermission'							=> $path.'/PCT/CustomCatalog/FrontEdit/PageNoPermission.php',
	
	'PCT\Contao\FrontendUser'												=> $path.'/PCT/Contao/FrontendUser.php',
	
	// Controllers
	'Contao\Controllers\FrontendFile'										=> $path.'/Contao/Controllers/FrontendFile.php',
	'Contao\Controllers\FrontendPage'										=> $path.'/Contao/Controllers/FrontendPage.php',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_customcatalogfrontedit'		=> $path.'/templates',
	'customcatalog_default_edit'		=> $path.'/templates',

	// frontedit
	'buttons'							=> $path.'/templates/frontedit',
	
	'cc_edit_nopermission'				=> $path.'/templates/pages',
));