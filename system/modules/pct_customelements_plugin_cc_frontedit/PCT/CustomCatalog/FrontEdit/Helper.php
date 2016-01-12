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
 * Import
 */
use \PCT\CustomElements\Helper\Functions as Functions;

/**
 * Class file
 * Helper
 */
class Helper
{
	/**
	 * Generate the paste into button array
	 * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getPasteAfterButton($arrRow,$strTable,$arrClipboard=array())
	{
		if(count($arrClipboard) < 1)
		{
			$arrSession = \Session::getInstance()->get('CLIPBOARD');
			$arrClipboard = $arrSession[$strTable];
		}
		
		$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		#$image = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteinto'][1], $objRow->id));
		
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$html = \Image::getHtml('pasteafter_.gif');
		}
		else
		{
			$href = Functions::addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : ''));
			$html = '<a href="'.$href.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id)).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> 'pasteafter.gif',
			'icon_html' => $image,
		);
		
		return $arrReturn;
	}
	
	
	/**
	 * Generate the paste into button array
	 * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getPasteIntoButton($arrRow,$strTable,$arrClipboard=array())
	{
		if(count($arrClipboard) < 1)
		{
			$arrSession = \Session::getInstance()->get('CLIPBOARD');
			$arrClipboard = $arrSession[$strTable];
		}
			
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteinto'][1], $objRow->id));
			
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$html = \Image::getHtml('pasteafter_.gif');
		}
		else
		{
			$href = Functions::addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : ''));
			$html = '<a href="'.$href.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id)).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> 'pasteinto.gif',
			'icon_html' => $image,
		);
		
		return $arrReturn;	
	}
	

}
 