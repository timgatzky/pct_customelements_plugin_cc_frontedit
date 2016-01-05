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
namespace PCT\CustomCatalog;

/**
 * Class file
 * FrontEdit
 */
class FrontEdit extends \PCT\CustomElements\Models\FrontEditModel
{
	/**
	 * The configuration object
	 * @param object
	 */
	protected $objConfig = null;
	
	
	/**
	 * Instantiate
	 * @param object	Configuration object containing nessessary information
	 */
	public function __construct($objConfig=null)
	{
		if($objConfig !== null)
		{
			$this->setConfig($objConfig);
		}
	}
	
	
	/**
	 * Apply configuration
	 */
	public function setConfig($objConfig)
	{
		if($objConfig->customcatalog !== null)
		{
			$this->set('objCustomCatalog',$objConfig->customcatalog);
			$this->set('objModule',$objConfig->customcatalog->getModel());
			
			if(!$objConfig->module)
			{
				$objConfig->module = $objConfig->customcatalog->getModel();
			}
		}
		
		if($objConfig->activeRecord !== null)
		{
			$this->set('objActiveRecord',$objConfig->activeRecord);
		}
		
		if($objConfig->module !== null)
		{
			$this->set('objModule',$objConfig->module);
		}
		
		$this->set('objConfig',$objConfig);
	}
	
	
	public function getConfig()
	{
		return $this->get('objConfig');
	}
	
	
#	/**
#	 * 
#	 */
#	public function addEditButtonsToTemplate($objTemplate, $objConfig, )
#	{
#		
#	}
	
	
	
	/**
	 * Generates the edit buttons list by an active record data (database result) or an array
	 * @param object||array		DatabaseResult || Array
	 * @return string			Html output
	 */
	public function addButtonsToTemplateByRow($objTemplate, $varRow, $objConfig=null)
	{
		global $objPage;
		
		$objRow = null;
		if(is_array($varRow))
		{
			foreach($varRow as $k => $v)
			{
				$objRow->{$k} = $v;
			}
		}
		else if(is_object($varRow))
		{
			$objRow = $varRow;
		}
		else
		{
			return '';
		}
		
		// apply a config object
		if($objConfig !== null)
		{
			$this->setConfig($objConfig);
		}
		
		$objConfig = $this->getConfig();
		
		$objFunction = \PCT\CustomElements\Helper\Functions::getInstance();
		$objCC = $objConfig->customcatalog;
		$objModule = $objConfig->module;
		$arrDefaultDCA = \PCT\CustomElements\Plugins\CustomCatalog\Helper\DcaHelper::getDefaultDataContainerArray();
		
		\System::loadLanguageFile('tl_pct_customcatalog');
		
		$strEditJumpTo = '';
		if($objModule->customcatalog_edit_jumpTo > 0)
		{
			$strEditJumpTo = \Controller::generateFrontendUrl( \PageModel::findByPk($objModule->customcatalog_edit_jumpTo)->row() );
		}
		else
		{
			$strEditJumpTo = \Controller::generateFrontendUrl( $objPage->row() );
		}
		
		$arrButtons = array();
		$i = 0;
		foreach($arrDefaultDCA['list']['operations'] as $key => $button)
		{
			$title = sprintf($button['label'][1],$objRow->id);
			$href = ampersand($objFunction->addToUrl($button['href'].'&amp;id='.$objRow->id.($objRow->pid > 0 ? '&amp;pid='.$objRow->pid : ''), $strEditJumpTo));
			$linkImage = \Image::getHtml('system/themes/default/images/'.$button['icon'],$title);
			$linkText = (strlen($linkImage) > 0 ? $linkImage : $button['label'][0]);
			
			$arr = array('operation',$key);
			($i%2 == 0 ? $arr[] = 'even' : $arr[] = 'odd');
			if($i == 0) {$arr[] = 'first';}
			if($i >= count($arrDefaultDCA['list']['operations'])-1) {$arr[] = 'last';}
			
			$class = implode(' ', $arr);
			
			$button['class'] = $class;
			$button['href'] = $href;
			$button['linkImage'] = $linkImage;
			
			// html
			$button['html'] = sprintf('<a href="%s" title="%s" class="%s">%s</a>',$href,$title,$class,$linkText);
			
			$arrButtons[$key] = $button;
			
			$i++;
		}
		
		// Hook: Modify buttons
		if (isset($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons']) && count($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons']) > 0)
		{
			foreach($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons,$this);
			}
		}
		
		if(count($arrButtons) < 1)
		{
			return '';
		}
		
		// render buttons
		$objTemplate->empty = (count($arrButtons) < 1 ? true : false);
		$objTemplate->module = $objModule;
		$objTemplate->config = $objConfig;
		$objTemplate->customcatalog = $objCC;
		$objTemplate->activeRecord = $objRow;
		$objTemplate->buttons = $arrButtons;
		
		return $objTemplate;
	}
}