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
	'PCT\CustomCatalog\FrontEdit\RowTemplate'								=> $path.'/PCT/CustomCatalog/FrontEdit/RowTemplate.php',
	'PCT\CustomCatalog\FrontEdit\TemplateAttribute'							=> $path.'/PCT/CustomCatalog/FrontEdit/TemplateAttribute.php',
	
	// Models
	'PCT\CustomElements\Models\FrontEditModel'								=> $path.'/PCT/CustomElements/Models/FrontEditModel.php',

	// Frontend
	'PCT\CustomElements\Plugins\CustomCatalog\Frontend\ModuleFrontEdit'		=> $path.'/PCT/CustomElements/Plugins/CustomCatalog/Frontend/ModuleFrontEdit.php',


));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	// frontend
	'mod_customcatalogeditor'		=> $path.'/templates',
	'customcatalog_default_edit'	=> $path.'/templates',
	
	// frontedit
	'editheader'					=> $path.'/templates/frontedit',
));