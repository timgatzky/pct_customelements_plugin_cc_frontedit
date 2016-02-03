<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2015 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2015
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements
 * @subpackage	pct_customelements_plugin_customcatalog
 * @subpackage	pct_customelements_plugin_cc_frontedit
 * @link		http://contao.org
 */


$objDcaHelper = \PCT\CustomElements\Helper\DcaHelper::getInstance()->setTable('tl_settings');

/**
 * Palettes
 */
$arrPalettes = $objDcaHelper->getPalettesAsArray('default');
$arrPalettes['customcatalog_edit_legend:hide'][] = 'customcatalog_edit_admin';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = $objDcaHelper->generatePalettes($arrPalettes);

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['customcatalog_edit_admin'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_settings']['customcatalog_edit_admin'],
	'inputType'		=> 'select',
	'foreignKey'	=> 'tl_user.username',
	'eval'			=> array('includeBlankOption'=>true),
	'sql'			=> "int(10) NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['customcatalog_reader_baseRecordIsFallback'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_settings']['customcatalog_reader_baseRecordIsFallback'],
	'inputType'		=> 'checkbox',
	'eval'			=> array('tl_class'=>'w50'),
	'sql'			=> "char(1) NOT NULL default ''",
);