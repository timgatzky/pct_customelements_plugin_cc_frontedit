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
 * Namespace
 */
namespace PCT\CustomCatalog\FrontEdit;

/**
 * Class file
 * Callbacks
 */
class Callbacks
{
	/**
	 * Show even unpublished entries in edit mode
	 * @param array
	 * @param object
	 * @return array
	 */
	public function unsetPublishedOptions($arrOptions,$objCC)
	{
		$strPublishedField = $objCC->getPublishedField();
		
		if($objCC->getTable() != \Input::get('table') || strlen($objCC->getPublishedField()) < 1)
		{
			return $arrOptions;
		}
		
		if(\Input::get('act') != 'edit')
		{
			return $arrOptions;
		}
		
		$tmp = array();
		foreach($arrOptions['columns'] as $i => $option)
		{
			if($option['column'] == $strPublishedField)
			{
				continue;
			}
			$tmp[] = $option;
		}
		
		$arrOptions['columns'] = $tmp;
		
		return $arrOptions;
	}
}
 