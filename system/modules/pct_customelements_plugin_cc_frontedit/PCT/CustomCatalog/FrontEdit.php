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
 * Imports
 */
use PCT\CustomCatalog\FrontEdit\CustomCatalogFactory as CustomCatalogFactory;


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
		/**
		 * Info: Configuration object:
		 * @property object $customcatalog 						The CustomCatalog working on
		 * @property object (DatabaseResult) $activeRecord 		The active row / entry working on
		 * @property object $module								The contao module e.g. the list module
		 * @property object $template							The output template object 
		*/
		if($objConfig !== null)
		{
			$this->setConfig($objConfig);
		}
	}
	
	
	/**
	 * Apply a configuration
	 * @param object	Configuration object
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
		
		$strAliasField = $objCC->getAliasField();
		$strAlias = $objCC->getCustomElement()->get('alias');
		
		$arrButtons = array();
		$i = 0;
		foreach($arrDefaultDCA['list']['operations'] as $key => $button)
		{
			if(in_array($key,$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['ignoreButtons']))
			{
				continue;
			}
			
			$strJumpTo = \Controller::generateFrontendUrl( $objPage->row() );
		
			if($key == 'edit' && $objModule->customcatalog_edit_jumpTo > 0)
			{
				$strJumpTo = \Controller::generateFrontendUrl( \PageModel::findByPk($objModule->customcatalog_edit_jumpTo)->row() );
			}
			
			$title = sprintf($button['label'][1],$objRow->id);
			$href = $objFunction->addToUrl($button['href'].'&amp;do='.$strAlias.'&amp;table='.$objCC->getTable().'&amp;id='.$objRow->id.($objRow->pid > 0 ? '&amp;pid='.$objRow->pid : ''), $strJumpTo);
			// add the items parameter to the url
			$href = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.(strlen($strAliasField) > 0 ? $objRow->{$strAliasField} : $objRow->id) ,$href);
			// add the request token
			if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
			{
				$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
			}
			
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
			
			$attributes = $button['attributes'];
			if($key == 'delete')
			{
				$attributes = sprintf($attributes,$objRow->id);
			}
			
			// html
			$button['html'] = sprintf('<a href="%s" title="%s" class="%s" %s>%s</a>',$href,$title,$class,$attributes,$linkText);
			
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
	
	
	public static function isEditable($strTable='', $intId='')
	{
		// check if modes are active
		if(!in_array(\Input::get('act'), $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['defaultOperations']))
		{
			return false;
		}
		
		return true;
	}
	
	
	public function generateWidgetByAttribute($objAttribute)
	{
		\FB::log($objAttribute);
	}
	
	
	/**
	 * POST and GET action listener
	 * Apply operations
	 * called from generatePage Hook
	 */
	public function applyOperationsOnGeneratePage($objPage)
	{
		// check in general if editing is active and allowed
		if(!$this->isEditable())
		{
			return;
		}
		
		// check request token 
		if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'] && ( !\Input::get('rt') == REQUEST_TOKEN || strlen(\Input::get('rt') < 1) ) )
		{
			header('HTTP/1.1 400 Bad Request');
			die_nicely('be_referer', 'Invalid request token. Please <a href="javascript:window.location.href=window.location.href">go back</a> and try again.');
		}
		
		$objSystem = new \PCT\CustomElements\Plugins\CustomCatalog\Core\SystemIntegration();
		
		// load the data container to the frontend
		if(!$GLOBALS['TL_DCA'][$strTable])
		{
			$objSystem->loadCustomCatalog(\Input::get('table'),true);
		}
		
		$objCC = CustomCatalogFactory::findCurrent();
		
		if($objCC === null)
		{
			return;
		}
		
		#$this->import('FrontendUser','User');
		
		$objUser = new \StdClass;
		$objUser->id = 1;
		
		// Create a datacontainer
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper($objCC->getTable());
		$objDC->User = $objUser;
				
		// DELETE
		if(\Input::get('act') == 'delete')
		{
			$objDC->delete();
		}
		
		// COPY
		$blnDoNotSwitchToEdit = true;
		if(\Input::get('act') == 'copy')
		{
			$objDC->copy($blnDoNotSwitchToEdit);
			\Controller::redirect( \Controller::getReferer() );
		}
	}
}