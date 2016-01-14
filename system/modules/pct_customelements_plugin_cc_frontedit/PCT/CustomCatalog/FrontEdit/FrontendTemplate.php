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
 * CustomCatalog
 */
class FrontendTemplate extends \PCT\CustomElements\Plugins\CustomCatalog\Core\FrontendTemplate
{
	/**
	 * Load default language file
	 */
	public function __construct($strTemplate='')
	{
		\System::loadLanguageFile('default');
		
		return parent::__construct($strTemplate);
	}
	
	/**
	 * Generate the global new element button
	 * @return string
	 */
	public function newElementButton()
	{
		global $objPage;
		$objCC = $this->getCustomCatalog();
		
		$objFunction = \PCT\CustomElements\Helper\Functions::getInstance();
		
		$strAlias = $objCC->getCustomElement()->get('alias');
		$strTable = $objCC->getTable();
		
		$image = \Image::getHtml('new.gif',$GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['new'][0]);
		$href = $objFunction->addToUrl('do='.$strAlias.'&table='.$strTable,\Controller::generateFrontendUrl($objPage->row()));
		
		// add the edit jump to page id to the url
		if($objCC->getOrigin()->customcatalog_edit_jumpTo)
		{
			$href = $objFunction->addToUrl('jumpto='.$objCC->getOrigin()->customcatalog_edit_jumpTo,$href);
		}
		
		if(in_array($objCC->get('list_mode'),array(4,5,'5.1')))
		{
			$href = $objFunction->addToUrl('&act=paste&mode=create',$href);
		}
		
		// add the request token
		if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
		{
			$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
		}
		
		$title = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['new'][1];
		$linkText = $image.$GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['new'][0];
		$class = 'header_new';
		
		return sprintf('<a href="%s", class="%s" title="%s">%s</a>',$href,$class,$title,$linkText);
	}
	
	
	/**
	 * Generate the edit all / select button
	 * @return string
	 */
	public function editAllButton()
	{
		global $objPage;
		$objCC = $this->getCustomCatalog();
		$objFunction = \PCT\CustomElements\Helper\Functions::getInstance();
		
		$strAlias = $objCC->getCustomElement()->get('alias');
		$strTable = $objCC->getTable();
		
		$image = \Image::getHtml('all.gif',$GLOBALS['TL_LANG']['MSC']['all'][0]);
		$href = $objFunction->addToUrl('do='.$strAlias.'&table='.$strTable.'&act=select',\Controller::generateFrontendUrl($objPage->row()));
		
		// add the request token
		if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
		{
			$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
		}
		
		$title = $GLOBALS['TL_LANG']['MSC']['all'][1];
		$linkText = $image.$GLOBALS['TL_LANG']['MSC']['all'][0];
		$class = 'header_edit_all';
		
		return sprintf('<a href="%s", class="%s" title="%s">%s</a>',$href,$class,$title,$linkText);
	}
	
	
	/**
	 * Generate the clear clipboard button or back button if editAll mode
	 * @return string
	 */
	public function clearClipboardButton()
	{
		global $objPage;
		
		$objCC = $this->getCustomCatalog();
		$objFunction = \PCT\CustomElements\Helper\Functions::getInstance();
		$strAlias = $objCC->getCustomElement()->get('alias');
		$strTable = $objCC->getTable();
		
		$arrSession = \Session::getInstance()->get('CLIPBOARD_HELPER');
		
		// generate back button
		if(\Input::get('act') == 'select')
		{
			return $this->backButton();
		}
		// coming from create event
		else if(\Input::get('act') == 'edit' && $arrSession[$strTable]['mode'] == 'oncreate')
		{
			return $this->backButton(true,true);
		}
		
		
		$image = \Image::getHtml('clipboard.gif',$GLOBALS['TL_LANG']['MSC']['clearClipboard']);
		$href = $objFunction->addToUrl('do='.$strAlias.'&table='.$strTable.'&clear_clipboard=1',\Controller::generateFrontendUrl($objPage->row()));
		
		// add the request token
		if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
		{
			$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
		}
		
		$title = $GLOBALS['TL_LANG']['MSC']['clearClipboard'];
		$linkText = $image.$GLOBALS['TL_LANG']['MSC']['clearClipboard'];
		$class = 'header_clipboard';
		
		return sprintf('<a href="%s", class="%s" title="%s">%s</a>',$href,$class,$title,$linkText);
	}
	
	
	/**
	 * Generate the select all checkbox
	 * @return string 
	 */
	public function selectAllCheckbox()
	{
		$objCheckbox = new \FormCheckbox();
		$objCheckbox->label = $GLOBALS['TL_LANG']['MSC']['selectAll'];
		$objCheckbox->name = 'tl_select_trigger';
		$objCheckbox->id = 'tl_select_trigger';
		$objCheckbox->class = tl_tree_checkbox;
		$objCheckbox->attribute = 'asdf';
		return $objCheckbox->generate();
	}
	
	
	/**
	 * Generate the back button
	 * @param boolean	Go back to referer or reload
	 * @return string
	 */
	public function backButton($blnGoToReferer=false,$blnClearClipboard=false)
	{
		global $objPage;
		$image = \Image::getHtml('back.gif',$GLOBALS['TL_LANG']['MSC']['goBack']);
		$title = $GLOBALS['TL_LANG']['MSC']['back'];
		$linkText = $image.$GLOBALS['TL_LANG']['MSC']['goBack'];
		$class = 'header_back';
		$href = ( $blnGoToReferer ? \Controller::getReferer() : \Controller::generateFrontendUrl($objPage->row()) );
		
		if($blnClearClipboard)
		{
			// remove parameters from url
			foreach(array('act','jumpto','mode') as $v)
			{
				$href = \PCT\CustomElements\Helper\Functions::removeFromUrl($v,$href);
			}
			// add the clear clipboard parameter
			$href = \PCT\CustomElements\Helper\Functions::addToUrl('clear_clipboard=1',$href);
		}
		
		return sprintf('<a href="%s", class="%s" title="%s">%s</a>',$href,$class,$title,$linkText);
	}
}
 