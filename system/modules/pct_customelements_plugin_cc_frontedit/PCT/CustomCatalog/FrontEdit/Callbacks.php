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
	 * Show even unpublished entries in edit mode or list edit modes
	 * @param array
	 * @param object
	 * @return array
	 */
	public function bypassPublishedSettings($arrOptions,$objCC)
	{
		$strPublishedField = $objCC->getPublishedField();
		
		// return if there is no published field
		if(strlen($objCC->getPublishedField()) < 1)
		{
			return $arrOptions;
		}
		
		$bypass = false;
		
		// always show unpublished entries in edit mode
		if(in_array(\Input::get('act'),array('edit','editAll','overrideAll')) && $objCC->getTable() == \Input::get('table'))
		{
			$bypass = true;
		}
		
		// lists
		if($objCC->getOrigin()->customcatalog_edit_showUnpublished)
		{
			$bypass = true;
		}
		
		if($bypass == true)
		{
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
		}
		return $arrOptions;
	}
}
 