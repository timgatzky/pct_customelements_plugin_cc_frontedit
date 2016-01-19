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
use \PCT\CustomElements\Helper\ControllerHelper as ControllerHelper;

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
	
	
	/**
	 * Generate the cut button
	  * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getCutButton($arrRow,$strTable,$arrClipboard=array())
	{
		if(count($arrClipboard) < 1)
		{
			$arrSession = \Session::getInstance()->get('CLIPBOARD');
			$arrClipboard = $arrSession[$strTable];
		}
			
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image = \Image::getHtml('cut.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['cut'][1], $objRow->id));
			
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$html = \Image::getHtml('cut_.gif');
		}
		else
		{
			$href = 'act=paste&amp;mode=cut';
			$html = '<a href="'.$href.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['cut'][1], $objRow->id)).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> 'cut.gif',
			'icon_html' => $image,
		);
		
		return $arrReturn;	
	}

	
	
	
	/**
	 * Generate toggle visibility button
	 * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getToggleVisibilityButton($arrRow,$strTable,$arrClipboard=array())
	{
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strTable);
		if(!$objCC)
		{
			return '';
		}
		
		if (\Input::get('tid'))
		{
			$this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
			\Controller::redirect( \Controller::getReferer() );
		}
		
		
		$strPublishedField = $objCC->getPublishedField();
		
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image_on = \Image::getHtml('visible.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $objRow->id));
		$image_off =  \Image::getHtml('invisible.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $objRow->id));
		$icon_on = 'system/themes/default/images/visible.gif';
		$icon_off = 'system/themes/default/images/invisible.gif';
		
		// Check permissions AFTER checking the tid, so hacking attempts are logged
		#if (!$this->User->isAdmin && !$this->User->hasAccess('create', 'pct_customcatalogsp'))
		#{
		#	return '';
		#}
		
		$image = $image_on;
		if (!$arrRow[$strPublishedField])
		{
			$image = $image_off;
		}
			
		$href = 'tid='.$arrRow['id'].'&amp;state='.($arrRow[$strPublishedField] ? '' : 1);
		
		$attributes = array
		(
			'onclick="CC_FrontEdit.toggleVisibility(this); return false;"',
			'data-state="'.($arrRow[$strPublishedField] ? '' : 1).'"',
			'data-icon="'.$icon_on.'"',
			'data-icon-disabled="'.$icon_off.'"',
			'data-table="'.$objCC->getTable().'"',
			'data-field="'.$strPublishedField.'"',
		);
		
		$arrReturn = array
		(
			'html' 	=> '',
			'href'	=> $href,
			'icon'	=> $arrRow[$strPublishedField] ? 'visible.gif' : 'invisible.gif',
			'icon_html' => $image,
			'attributes' => implode(' ', $attributes)
		);
		
		return $arrReturn;	
	}
	
	
	/**
	 * Toggle the published setting of an entry
	 * @param integer
	 * @param 
	 */
	protected function toggleVisibility($intId, $blnVisible)
	{
		$strTable = \Input::get('table');
		
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strTable);
		if(!$objCC)
		{
			return;
		}
		
		$strField = $objCC->getPublishedField();
		
		// Check permissions to edit
		$objInput = \Input::getInstance();
		$objInput->setGet('id', $intId);
		$objInput->setGet('act', 'toggle');
		
		// Check permissions to publish
		#if (!$this->User->isAdmin && !$this->User->hasAccess($strTable.'::'.$strField, 'alexf'))
		#{
		#   $this->log('Not enough permissions to publish/unpublish item ID "'.$intId.'"', $strTable.' toggleVisibility', TL_ERROR);
		#   $this->redirect('contao/main.php?act=error');
		#}

		#$objVersions = new \Versions($strTable, $intId);
		#$objVersions->initialize();
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['save_callback']))
		{
		   foreach ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['save_callback'] as $callback)
		   {
		   		$objCaller = new $callback[0];
		   		$blnVisible = $objCaller->$callback[1]($blnVisible, $this);
		   }
		}
		
		// Update the database
		\Database::getInstance()->prepare("UPDATE ".$strTable." %s WHERE id=?")->set(array('tstamp'=>time(),$strField=>$blnVisible ? '':1))->execute($intId);

		#$objVersions->create();
		
		\System::log('A new version of record "'.$strTable.'.id='.$intId.'" has been created', $strTable.' toggleVisibility()', TL_GENERAL);
	}

}
 