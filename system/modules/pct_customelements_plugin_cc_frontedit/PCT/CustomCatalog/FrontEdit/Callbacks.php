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

use Contao\Input;
use PCT\CustomCatalog\FrontEdit;

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
		if(in_array(Input::get('act'),array('edit','editAll','overrideAll')) && $objCC->getTable() == Input::get('table'))
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
		if(!in_array(Input::get('act'), $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['multipleOperations']))
		{
			return $arrOptions;
		}
		
		$objSession = FrontEdit::getSession();

		$arrSession = $objSession->get('contao_frontend');
		if(count($arrSession['CURRENT']['IDS']) > 0)
		{
			$arrIds = $arrSession['CURRENT']['IDS'];
			if(Input::get('act') == 'fe_overrideAll')
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
	 * Show only the entry of interest for lists when editing one single entry
	 * @param array
	 * @param object
	 * @return array
	 */
	public function showCurrentEditEntryOnly($arrOptions,$objCC)
	{
		global $objPage;
		
		// check if current page is different from jump to page
		if(Input::get('act') != 'edit' || $objCC->getOrigin()->customcatalog_jumpTo > 0 && $objPage->id == $objCC->getOrigin()->customcatalog_jumpTo)
		{
			return $arrOptions;
		}
		
		// reset all filters
		$arrOptions['columns'] = array( array('column'=>'id','operation'=>'IN','value'=>Input::get('id')) );
		
		return $arrOptions;
	}
	
	
	/**
	 * Frontend ajax listener
	 * @called from generatePage HOOK
	 */
	public function ajaxListener()
	{
		$objSession = FrontEdit::getSession();
		
		// store scroll offset
		if(Input::post('ajax') && Input::post('scrollOffset'))
		{
			$objSession->set('FRONTEND_SCROLLOFFSET',Input::post('scrollOffset'));
		}
		
		if($objSession->get('FRONTEND_SCROLLOFFSET') && !Input::post('ajax'))
		{
			$GLOBALS['TL_JQUERY'][] = '<script>CC_FrontEdit.scrollTo("'.$objSession->get('FRONTEND_SCROLLOFFSET').'");</script>';
			$objSession->remove('FRONTEND_SCROLLOFFSET');
		}
		
		// store the html buffers
		#if(Input::post('ajax') && Input::post('action') == 'cc_frontedit_html_buffer')
		#{
		#	$field = Input::post('field');
		#	$arrSession = \Session::getInstance()->get($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']);
		#	$arrSession['BUFFER'][$field] = Input::post('buffer');
		#	\Session::getInstance()->set($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName'],$arrSession);
		#}
				
		// remove the regular call to tabletree.js. It's loaded by the tags widget
		if(Input::get('act') && in_array(PCT_TABLETREE_PATH.'/assets/js/tabletree.js', $GLOBALS['TL_JAVASCRIPT']))
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
 