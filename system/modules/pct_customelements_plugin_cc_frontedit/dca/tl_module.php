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
 * Table tl_module
 */
$objDcaHelper = \PCT\CustomElements\Plugins\CustomCatalog\Helper\DcaHelper::getInstance()->setTable('tl_module');

/**
 * Config
 */
#$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('PCT\CustomElements\Plugins\CustomCatalog\Backend\TableModule', 'modifyDca');


/**
 * Palettes
 */
// customcataloglist
$arrPalettes = $objDcaHelper->getPalettesAsArray('customcataloglist');
$arrPalettes['config_legend'][] = 'customcatalog_edit_jumpTo';
$GLOBALS['TL_DCA']['tl_module']['palettes']['customcataloglist'] = $objDcaHelper->generatePalettes($arrPalettes);
// customcatalogfrontedit
$arrPalettes = $objDcaHelper->getPalettesAsArray('customcatalogreader');
$arrPalettes = $objDcaHelper->removePalette('comment_legend:hide');
$GLOBALS['TL_DCA']['tl_module']['palettes']['customcatalogfrontedit'] = $objDcaHelper->generatePalettes($arrPalettes);

/**
 * Fields
 */
$objDcaHelper->addFields(array
(
	// config_legend
	'customcatalog_edit_jumpTo' => array
	(
		'label'           		=> &$GLOBALS['TL_LANG']['tl_module']['customcatalog_edit_jumpTo'],
		'exclude'         		=> true,
		'inputType'       		=> 'pageTree',
		'eval'            		=> array('tl_class'=>''),
		'sql'			  		=> "int(10) NOT NULL default '0'",
	),
#	'customcatalog_edit_operations'	=> array
#	(
#		
#	),
));
