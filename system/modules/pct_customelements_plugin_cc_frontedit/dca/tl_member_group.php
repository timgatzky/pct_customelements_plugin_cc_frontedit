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
 * Load the tl_user DataContainer
 */
if(!is_array($GLOBALS['TL_DCA']['tl_user_group']['fields']))
{
	\Controller::loadDataContainer('tl_user_group');
	\Controller::loadLanguageFile('tl_user_group');
}

/**
 * Table tl_member_group
 */
$objDcaHelper = \PCT\CustomElements\Plugins\CustomCatalog\Helper\DcaHelper::getInstance()->setTable('tl_member_group');

/**
 * Palettes
 */
$arrPalettes = $objDcaHelper->getPalettesAsArray('default');
$arrPalettes['frontedit_legend:hide'][] = 'customcatalog_edit_active';
$GLOBALS['TL_DCA'][$objDcaHelper->getTable()]['palettes']['default'] = $objDcaHelper->generatePalettes($arrPalettes);

/**
 * Subpalettes
 */
$objDcaHelper->addSubpalette('customcatalog_edit_active',array('pagemounts','alpty','filemounts','fop'));

/**
 * Fields
 */
$objDcaHelper->addFields(array
(
	'customcatalog_edit_active' => array
	(
		'label'           		=> &$GLOBALS['TL_LANG']['tl_module']['customcatalog_edit_active'],
		'exclude'         		=> true,
		'inputType'       		=> 'checkbox',
		'eval'            		=> array('tl_class'=>'','submitOnChange'=>true),
		'sql'			  		=> "char(1) NOT NULL default ''",
	),
	'pagemounts' 		=> $GLOBALS['TL_DCA']['tl_user_group']['fields']['pagemounts'],	
	'alpty'				=> $GLOBALS['TL_DCA']['tl_user_group']['fields']['alpty'],
	'filemounts' 		=> $GLOBALS['TL_DCA']['tl_user_group']['fields']['filemounts'],
));

