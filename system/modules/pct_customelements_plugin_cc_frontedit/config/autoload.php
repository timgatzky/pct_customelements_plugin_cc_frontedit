<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2017
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
	'PCT\CustomCatalog\FrontEdit\SystemIntegration'							=> $path.'/PCT/CustomCatalog/FrontEdit/SystemIntegration.php',
	// Frontend
	'PCT\CustomElements\Plugins\FrontEdit\Frontend\ModuleReader'			=> $path.'/PCT/CustomElements/Plugins/FrontEdit/Frontend/ModuleReader.php',
	'PCT\CustomElements\Plugins\FrontEdit\Frontend\ModuleList'				=> $path.'/PCT/CustomElements/Plugins/FrontEdit/Frontend/ModuleList.php',
	// Helper
	'PCT\CustomElements\Plugins\FrontEdit\Helper\DataContainerHelper'		=> $path.'/PCT/CustomElements/Plugins/FrontEdit/Helper/DataContainerHelper.php',
	'PCT\Contao\_FrontendUser'												=> $path.'/PCT/Contao/_FrontendUser.php',
	// Controllers
	'Contao\Controllers\FrontendFile'										=> $path.'/Contao/Controllers/FrontendFile.php',
	'Contao\Controllers\FrontendPage'										=> $path.'/Contao/Controllers/FrontendPage.php',
	'Contao\Controllers\FrontendPctTableTree'								=> $path.'/Contao/Controllers/FrontendPctTableTree.php',
	// Contao >= 4
	'PCT\Contao\BackendMain'												=> $path.'/PCT/Contao/BackendMain.php',
	'PCT\Contao\BackendUser'												=> $path.'/PCT/Contao/BackendUser.php',
	'PCT\Contao\Picker\PickerBuilder'										=> $path.'/PCT/Contao/Picker/PickerBuilder.php',
	'PCT\Contao\Picker\PagePickerProvider'									=> $path.'/PCT/Contao/Picker/PagePickerProvider.php',
	'PCT\Contao\Picker\FilePickerProvider'									=> $path.'/PCT/Contao/Picker/FilePickerProvider.php',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_customcatalogfrontedit'		=> $path.'/templates',
	'customcatalog_default_edit'		=> $path.'/templates',
	'buttons'							=> $path.'/templates/frontedit',
	'cc_edit_nopermission'				=> $path.'/templates/pages',
	'js_cc_frontedit_ajaxhelper'		=> $path.'/templates/js',
));