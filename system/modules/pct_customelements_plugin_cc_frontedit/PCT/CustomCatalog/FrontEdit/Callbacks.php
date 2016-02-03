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
		if(strlen($objCC->getPublishedField()) < 1 || !$objCC->getOrigin()->customcatalog_edit_active)
		{
			return $arrOptions;
		}
		
		$bypass = false;
		
		// always show unpublished entries in edit modes
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
	
	
	/**
	 * Show only entries selected in editAll, overrideAll mode
	 * @param array
	 * @param object
	 * @return array
	 */
	public function showSelectedEntriesOnly($arrOptions,$objCC)
	{
		if(!in_array(\Input::get('act'), $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['multipleOperations']))
		{
			return $arrOptions;
		}
		
		$arrSession = \Session::getInstance()->getData();
		if(count($arrSession['CURRENT']['IDS']) > 0)
		{
			$arrIds = $arrSession['CURRENT']['IDS'];
			if(\Input::get('act') == 'fe_overrideAll')
			{
				$arrIds = $arrIds[0];	
			}
		}
		else
		{
			$arrIds = array(-1);
		}
		
		array_insert($arrOptions['columns'], 0, array( array('column'=>'id','operation'=>'IN','value'=>$arrIds) ) );
		
		return $arrOptions;
	}
	
	
	/**
	 * Frontend ajax listener
	 * @called from generatePage HOOK
	 */
	public function ajaxListener()
	{
		$objSession = \Session::getInstance();
		
		// store scroll offset
		if(\Input::post('ajax') && \Input::post('scrollOffset'))
		{
			\Session::getInstance()->set('FRONTEND_SCROLLOFFSET',\Input::post('scrollOffset'));
		}
		
		// remove the regular call to tabletree.js. It's loaded by the tags widget
		if(\Input::get('act') && in_array(PCT_TABLETREE_PATH.'/assets/js/tabletree.js', $GLOBALS['TL_JAVASCRIPT']))
		{
			foreach($GLOBALS['TL_JAVASCRIPT'] as $i => $k)
			{
				if($k == PCT_TABLETREE_PATH.'/assets/js/tabletree.js')
				{
					unset($GLOBALS['TL_JAVASCRIPT'][$i]);
				}
			}
		}
	}

}
 