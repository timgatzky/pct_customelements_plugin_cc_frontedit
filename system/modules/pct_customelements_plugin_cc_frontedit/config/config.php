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
 * @subpackage	pct_customelements_plugin_cc_frontedit
 * @link		http://contao.org
 */

/**
 * Constants
 */ 
define(PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH,'system/modules/pct_customelements_plugin_cc_frontedit');
define(PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_VERSION,'1.0.0');


/**
 * Globals
 */
$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['allowedOperations'] 	= array('edit','delete','copy','show','paste','select','create');
$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['ignoreButtons']		= array('show');
$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] = true;

/**
 * Register plugin
 */
$GLOBALS['PCT_CUSTOMELEMENTS']['PLUGINS']['cc_frontedit'] = array
(
	'tables' 	=> array('tl_pct_customelement','tl_pct_customelement_group','tl_pct_customelement_attribute'),
	'requires'	=> array('pct_customelements'=>'1.6.0'),
);


/**
 * Check if plugin is active
 */
$blnInitialize = true;
if( TL_MODE == 'BE' && count(\Session::getInstance()->getData()) > 0 )
{
	if(!in_array('cc_frontedit',\PCT\CustomElements\Core\PluginFactory::getActivePlugins()) && !in_array(\Input::get('do'), array('repository_manager','composer')) )
	{
		$blnInitialize = false;
	}
}

if($blnInitialize)
{
	/**
	 * Front end modules
	 */
	$GLOBALS['FE_MOD']['pct_customcatalog_node']['customcatalogfrontedit'] 	= 'PCT\CustomElements\Plugins\CustomCatalog\Frontend\ModuleFrontEdit';
	// new customcataloglist class
	$GLOBALS['FE_MOD']['pct_customcatalog_node']['customcataloglist'] 		= 'PCT\CustomElements\Plugins\CustomCatalog\Frontend\ModuleFrontEditList';
}


/**
 * Hooks
 */
if($blnInitialize)
{
	$GLOBALS['CUSTOMCATALOG_HOOKS']['getEntries'][] 		= array('PCT\CustomCatalog\FrontEdit\TemplateAttribute','__override');
	$GLOBALS['CUSTOMCATALOG_HOOKS']['getEntries'][] 		= array('PCT\CustomCatalog\FrontEdit\RowTemplate','__override');
	#$GLOBALS['CUSTOMCATALOG_HOOKS']['renderCatalog'][] 		= array('PCT\CustomCatalog\FrontEdit\FrontendTemplate','__override');
	#$GLOBALS['TL_HOOKS']['getFrontendModule'][] 			= array('PCT\CustomCatalog\FrontEdit\FrontendTemplate', 'overrideByModule');
	#$GLOBALS['TL_HOOKS']['getContentElement'][] 			= array('PCT\CustomCatalog\FrontEdit\FrontendTemplate', 'overrideByContentElement');
	#$GLOBALS['TL_HOOKS']['parseTemplate'][] 			= array('PCT\CustomCatalog\FrontEdit\FrontendTemplate', 'parseTemplateCallback');

	#$GLOBALS['CUSTOMELEMENTS_HOOKS']['prepareRendering'][]  = array('PCT\CustomCatalog\FrontEdit\Attribute','renderCallback');
	$GLOBALS['TL_HOOKS']['generatePage'][] 					= array('PCT\CustomCatalog\FrontEdit','applyOperationsOnGeneratePage');
}