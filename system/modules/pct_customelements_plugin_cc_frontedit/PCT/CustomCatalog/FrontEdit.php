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
use PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory as CustomCatalogFactory;
use PCT\CustomCatalog\FrontEdit\Helper as Helper; // just a helper class for outsourcing code


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
	
	
	/**
	 * Return the current config object
	 * @return object|null
	 */
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
		$objHelper = new Helper();
		
		$objCC = $objConfig->customcatalog;
		$objModule = $objConfig->module;
		$arrDefaultDCA = \PCT\CustomElements\Plugins\CustomCatalog\Helper\DcaHelper::getDefaultDataContainerArray();
		$objMultilanguage = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage;
		
		$strAliasField = $objCC->getAliasField();
		$strAlias = $objCC->getCustomElement()->get('alias');
		$strTable = $objCC->getTable();
		$strLanguage = $objMultilanguage->getActiveBackendLanguage($strTable);
		
		\System::loadLanguageFile('tl_pct_customcatalog');
		\System::loadLanguageFile('tl_content');
		\System::loadLanguageFile($strTable);
		
		// load the data container to the frontend
		if(!$GLOBALS['TL_DCA'][$strTable])
		{
			$objSystem = new \PCT\CustomElements\Plugins\CustomCatalog\Core\SystemIntegration();
			
			// fallback CC <= 1.4.14
			if(version_compare(PCT_CUSTOMCATALOG_VERSION, '1.4.14','<'))
			{
				$c = $GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'];
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = true;
				
				$objSystem->loadCustomCatalog($strTable,true);
				
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = $c;
			}
			else
			{
				$objSystem->loadDCA($strTable);
			}
		}
		
		// Create a datacontainer
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper($strTable);
		$objDC->User = $objUser;
		
		$arrOperations = $arrDefaultDCA['list']['operations'];
		$arrListOperations = deserialize($objCC->get('list_operations'));
		
		// include the toggle button
		if(strlen($objCC->getPublishedField()) > 0 && in_array('toggle', $arrListOperations))
		{
			array_insert($arrOperations,count($arrOperations),array('toggle' => $objHelper->getToggleVisibilityButton($objRow->row(),$strTable) ));
		}
		
		// include the cut button
		if(in_array($objCC->get('list_mode'),array(4,5,'5.1')) && in_array('cut', $arrListOperations))
		{
			array_insert($arrOperations,count($arrOperations),array('cut' => $objHelper->getCutButton($objRow->row(),$strTable) ));
		}
		
		// reorder
		if(count($arrListOperations) > 0)
		{
			$tmp = array();
			foreach($arrListOperations as $i => $key)
			{
				if(isset($arrOperations[$key]))
				{
					$tmp[$key] = $arrOperations[$key];
				}
			}
			$arrOperations = $tmp;
			unset($tmp);
		}
		
		$arrButtons = array();
		$i = 0;
		foreach($arrOperations as $key => $button)
		{
			if(in_array($key,$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['ignoreButtons']))
			{
				continue;
			}
			
			$strJumpTo = \Controller::generateFrontendUrl( $objPage->row() );
			
			// overwrite the jumpTo page when editing should be done on a different page
			if($key == 'edit' && $objModule->customcatalog_jumpTo > 0)
			{
				$strJumpTo = \Controller::generateFrontendUrl( \PageModel::findByPk($objModule->customcatalog_jumpTo)->row() );
			}
			
			$href = (isset($button['href']) ? $button['href'] : '');
			// copy,cut button in mode 4,5,5.1 should call the paste button
			if(in_array($objCC->get('list_mode'),array(4,5,'5.1')))
			{
				if($key == 'copy')
				{
					$href = 'act=paste&amp;mode=copy';
				}
				else if($key == 'cut')
				{
					$href = 'act=paste&amp;mode=cut';
				}
			}
			
			$title = sprintf($button['label'][1],$objRow->id);
			$href = $objFunction->addToUrl($href.'&amp;do='.$strAlias.'&amp;table='.$strTable.'&amp;id='.$objRow->id.($objRow->pid > 0 ? '&amp;pid='.$objRow->pid : ''), $strJumpTo);
			// add the items parameter to the url
			$href = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.(strlen($strAliasField) > 0 ? $objRow->{$strAliasField} : $objRow->id) ,$href);
			// add the request token
			if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
			{
				$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
			}
			// simulate a switchToEdit
			if($key == 'copy' && $objModule->customcatalog_jumpTo > 0)
			{
				$href = $objFunction->addToUrl('jumpto='.$objModule->customcatalog_jumpTo, $href);
			}
			// multilanguage, add the langpid
			if($objCC->get('multilanguage'))
			{
				$langpid = $objMultilanguage->getBaseRecordId($row['id'],$strTable,$strLanguage);
				$href = $objFunction->addToUrl('langpid='.$langpid,href);
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
			$attributes .= ' data-module="'.$objModule->id.'" data-id="'.$objRow->id.'"';
			
			// html
			$button['html'] = sprintf('<a href="%s" title="%s" class="%s" %s>%s</a>',$href,$title,$class,$attributes,$linkText);
			
			// trigger the button callbacks
			if(is_array($button['button_callback']))
			{
				$button['html'] = \System::importStatic($button['button_callback'][0])->{$button['button_callback'][1]}($objRow->row(),$href,$title,$icon,$attributes,$strTable);
			}
			
			$arrButtons[$key] = $button;
			
			$i++;
		}
		
		// append the clipboard buttons
		if(\Input::get('act') == 'paste')
		{
			array_insert($arrButtons,count($arrButtons),array('paste_after' => $objHelper->getPasteAfterButton($objRow->row(),$strTable) ));
			
			if($objCC->get('list_mode') == 5)
			{
				array_insert($arrButtons,count($arrButtons),array('paste_into'=> $objHelper->getPasteIntoButton($objRow->row(),$strTable) ));
			}
		}
		
		// append the multiple select checkbox
		if(\Input::get('act') == 'select')
		{
			$html = '<input data-module="'.$objModule->id.'" id="ids_'.$objRow->id.'" class="tl_tree_checkbox checkbox" type="checkbox" value="'.$objRow->id.'" name="IDS[]">';
			$select = array('html'=>$html,'class'=>'select');
			array_insert($arrButtons,count($arrButtons),array('select'=>$select));
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
		
		$objTemplate->empty = (count($arrButtons) < 1 ? true : false);
		$objTemplate->module = $objModule;
		$objTemplate->config = $objConfig;
		$objTemplate->customcatalog = $objCC;
		$objTemplate->activeRecord = $objRow;
		$objTemplate->buttons = $arrButtons;
		
		return $objTemplate;
	}
	
	
	/**
	 * General check if editing is allowed and/or active
	 * @param string	Tablename
	 * @param integer	A certain entry id that should be checked
	 * @return boolean
	 */
	public static function isEditable($strTable='', $intId='')
	{
		// clearing the clipboard is allowed
		if(strlen($strTable) > 0 && \Input::get('clear_clipboard') != '')
		{
			return true;
		}
		
		// check if editing is allowed for all or in general for FE Users only
		else if( isset($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll']) && $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === false && !FE_USER_LOGGED_IN )
		{
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Generate the widget by an attribute
	 * @param object	Attribute object
	 * @return string	Html widget output
	 */
#	public function generateWidgetByAttribute($objAttribute)
#	{
#		
#		
#		
#		\FB::log($objAttribute);
#	}
	
	
	/**
	 * Generates a button and returns the html anchor element
	 * @param string
	 * @param string	Optional the table name
	 * @param integer	Optional an entry id
	 * @return string
	 */
#	public function generateButton($strButton,$strTable='',$intId=0)
#	{
#		$objConfig = $this->getConfig();
#				
#		switch($strButton)
#		{
#			case 'new':
#			case 'new_element':
#				
#				break;
#			case 'editAll':
#			case 'edit_all':
#				break;
#				
#			default:
#				return '';
#				break;
#		}
#		
#		return '';
#	}
	
	
	/**
	 * Simulate the switchToEdit function
	 * Handle the DC switchToEdit functionality as known from the backend
	 */
	public function simulateSwitchToEdit()
	{
		$arrSession = \Session::getInstance()->get('CLIPBOARD_HELPER');
		
		$strTable = \Input::get('table');
		
		// !switchToEdit on CREATE
		if($arrSession[$strTable]['mode'] == 'create' && \Input::get('jumpto') > 0 && \Input::get('act') == 'edit')
		{
			$objFunction = new \PCT\CustomElements\Helper\Functions;
			$parse = parse_url(\Environment::get('request'));
			$redirect = $objFunction->addToUrl($parse['query'].'&jumpto=&',\Controller::generateFrontendUrl( \PageModel::findByPk(\Input::get('jumpto'))->row() ) );
			// add the items parameter to the url
			if(!\Input::get($GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter']))
			{
				$redirect = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.\Input::get('id'),$redirect);
			}	
			
			// remove session
			$arrSession[$strTable]['mode'] = 'on'.$arrSession[$strTable]['mode'];
			$arrSession[$strTable]['ref'] = \Controller::getReferer();
			
			\Session::getInstance()->set('CLIPBOARD_HELPER',$arrSession);
			
			// redirect to edit page
			\Controller::redirect($redirect);
		}
		// !switchToEdit on COPY
		else if($arrSession[$strTable]['mode'] == 'copy' && \Input::get('jumpto') > 0 && \Input::get('act') == 'copy')
		{
			$intNew = $arrSession[$strTable]['id'];
			$objFunction = new \PCT\CustomElements\Helper\Functions;
			$parse = parse_url(\Environment::get('request'));
			$redirect = $objFunction->addToUrl($parse['query'].'&id='.$intNew.'&act=edit&jumpto=&',\Controller::generateFrontendUrl( \PageModel::findByPk(\Input::get('jumpto'))->row() ) );
			// add/rewrite the items parameter to the url
			$redirect = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.$intNew,$redirect);
			
			// remove session
			$arrSession[$strTable]['mode'] = 'on'.$arrSession[$strTable]['mode'];
			$arrSession[$strTable]['ref'] = \Controller::getReferer();
			
			\Session::getInstance()->set('CLIPBOARD_HELPER',$arrSession);
			
			// redirect to edit page
			\Controller::redirect($redirect);
		}
	}
	
	
	/**
	 * POST and GET action listener
	 * Apply operations
	 * called from generatePage Hook
	 */
	public function applyOperationsOnGeneratePage($objPage)
	{
		// check if the table is allowed to be edited
		if(!$this->isEditable() || !in_array(\Input::get('act'), $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['allowedOperations']))
		{
			return;
		}
		
		// check request token 
		if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'] && \Input::get('rt') != REQUEST_TOKEN)
		{
			header('HTTP/1.1 400 Bad Request');
			die_nicely('be_referer', 'Invalid request token. Please <a href="javascript:window.location.href=window.location.href">go back</a> and try again.');
		}
		
		$strTable = \Input::get('table') ?: \Input::get('do');
		
		// check if the table is allowed to be edited
		if(!$this->isEditable($strTable))
		{
			return;
		}
		
		// load the data container to the frontend
		if(!$GLOBALS['TL_DCA'][$strTable])
		{
			$objSystem = new \PCT\CustomElements\Plugins\CustomCatalog\Core\SystemIntegration();
			
			// fallback CC <= 1.4.14
			if(version_compare(PCT_CUSTOMCATALOG_VERSION, '1.4.14','<'))
			{
				$c = $GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'];
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = true;
				
				$objSystem->loadCustomCatalog($strTable,true);
				
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = $c;
			}
			else
			{
				$objSystem->loadDCA($strTable);
			}
		}
		
		$objCC = CustomCatalogFactory::findCurrent();
		
		if($objCC === null)
		{
			return;
		}
			
		if(!defined(CURRENT_ID)) {define(CURRENT_ID, \Input::get('id'));}
		
		\System::importStatic('FrontendUser','User');
		
		$objUser = new \StdClass;
		$objUser->id = 1;
		
		// Create a datacontainer
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper($objCC->getTable());
		$objDC->User = $objUser;
		
		
		
		// !CREATE
		if(\Input::get('act') == 'create')
		{
			$objDC->create();
		}
		
		$blnDoNotSwitchToEdit = true;
		
		// !DELETE
		if(\Input::get('act') == 'delete')
		{
			$objDC->delete();
		}
		// !CUT
		else if(\Input::get('act') == 'cut')
		{
			$objDC->cut(true);
			if($blnDoNotSwitchToEdit)
			{
				\Controller::redirect( \Controller::getReferer() );
			}
		}
		// !CUT ALL
		else if(\Input::get('act') == 'cutAll')
		{
			$arrClipboard = \Session::getInstance()->get('CLIPBOARD');
			if (is_array($arrClipboard[$strTable]['id']))
			{
				foreach($arrClipboard[$strTable]['id'] as $id)
				{
					$objDC->intId = $id;
					$objDC->cut(true);
					\Input::setGet('pid', $id);
					\Input::setGet('mode', 1);
				}
			}
						
			\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
		}
		// !COPY
		else if(\Input::get('act') == 'copy')
		{
			$intNew = $objDC->copy(true);
			
			if(\Input::get('switchToEdit') || \Input::get('jumpto') > 0)
			{
				$arrClipboard[$strTable] = array
				(
					'id' 		=> $intNew,
					'mode' 		=> 'copy',
				);

				// set the clipboard helper to avoid that the DCA deletes the regular clipboard session
				\Session::getInstance()->set('CLIPBOARD_HELPER',$arrClipboard);
				
				// reload the page to make the session take effect
				if($blnDoNotSwitchToEdit)
				{
					\Controller::reload();
				}
			}
			
			if($blnDoNotSwitchToEdit)
			{
				\Controller::redirect( \Controller::getReferer() );
			}
		}
		// !COPY ALL
		else if(\Input::get('act') == 'copyAll')
		{
			#$objDC->copyAll();
			$arrClipboard = \Session::getInstance()->get('CLIPBOARD');

			if (is_array($arrClipboard[$strTable]['id']))
			{
				foreach($arrClipboard[$strTable]['id'] as $id)
				{
					$objDC->intId = $id;
					$id = $objDC->copy(true);
					\Input::setGet('pid', $id);
					\Input::setGet('mode', 1);
				}
			}
						
			if($blnDoNotSwitchToEdit)
			{
				\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
			}
		}
		// !EDIT ALL, OVERRIDE ALL
		else if(\Input::get('act') == 'editAll')
		{
			
		}	
	
		
		// !PASTE set the clipboard session
		else if(\Input::get('act') == 'paste')
		{
			$reload = false;
			$objSession = \Session::getInstance();
			$arrClipboard = $objSession->get('CLIPBOARD');
			
			if(count($arrClipboard[$strTable]) < 1)
			{
				$reload = true;
			}
			
			$ids = \Input::get('id');
			
			$arrCurrent = $objSession->get('CURRENT');
			if(count($arrCurrent['IDS']) > 0 && is_array($arrCurrent['IDS']))
			{
				$ids = $arrCurrent['IDS'];
			}
			
			$arrClipboard[$strTable] = array
			(
				'id' 		=> $ids,
				'mode' 		=> \Input::get('mode'),
			);
			
			$objSession->set('CLIPBOARD',$arrClipboard);
			
			// set the clipboard helper to avoid that the DCA deletes the regular clipboard session
			$objSession->set('CLIPBOARD_HELPER',$arrClipboard);
			
			if($reload)
			{
				\Controller::reload();
			}
		}
		
		// !CLIPBOARD clear
		if(strlen($strTable) > 0 && \Input::get('clear_clipboard'))
		{
			$objSession = \Session::getInstance();
			$arrSession = $objSession->get('CLIPBOARD');
			
			$arrSession[$strTable] = array();
			
			$objSession->set('CLIPBOARD',$arrSession);
			$objSession->set('CURRENT',array());
			
			\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
		}
	}
	
	
	/**
	 * Check if the front end user also has a running back end user session
	 * @param boolean
	 * @return boolean
	 */
	public function hasBackendSession($blnForceLookup=false)
	{
		if(TL_MODE != 'FE')
		{
			return true;
		}
		
		if(defined(FE_BE_USER_LOGGED_IN) && !$blnForceLookup)
		{
		   return true;
		}
		
		$objBackendSession = \Database::getInstance()->prepare("SELECT * FROM tl_session WHERE name=? AND ip=?")->limit(1)->execute('BE_USER_AUTH',\Environment::get('ip'));
		if($objBackendSession->numRows > 0)
		{
			if(!defined(FE_BE_USER_LOGGED_IN))
			{
			   define(FE_BE_USER_LOGGED_IN, true);
			}
			
			return true;
		}
				
		define(FE_BE_USER_LOGGED_IN, false);
		return false;
	}
	
	
	/**
	 * Add an attribute value to the database set list
	 * @param value		
	 * @param string		The table name
	 * @param integer		The record ID
	 * @param string		The field name
	 * @param object		The DataContainer object
	 */
	public function addToDatabaseSetlist($varValue,$objDC)
	{
		$strTable = $objDC->table;
		$intId = $objDC->id;
		$objAttribute = $objDC->objAttribute;
		$strField = $objDC->field;
		
		if(!is_array($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable]))
		{
			$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable] = array();
		}
		
		// trigger the storeValue HOOK from CE
		if($objDC->objAttribute)
		{
			$saveDataAs = $objDC->objAttribute->get('saveDataAs') ?: 'data';
			$arr = \PCT\CustomElements\Core\Hooks::callstatic( 'storeValueHook',array($objDC->objAttribute->get('id'),array($saveDataAs=>$varValue)) );
			if($arr[$saveDataAs] != $value)
			{
				$varValue = $arr[$saveDataAs];
			}
		}	
		
		// respect the save_callback
		if(is_array($GLOBALS['TL_DCA'][$strTable][$strField]['save_callback']) && count($GLOBALS['TL_DCA'][$strTable][$strField]['save_callback']) > 0)
		{
			foreach($GLOBALS['TL_DCA'][$strTable][$strField]['save_callback'] as $callback)
			{
				$varValue = \System::importStatic($callback[0])->{$callback[1]}($varValue,$objDC);
			}
		}
		
		$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable][$intId][$strField] = $varValue;
	}
	
	
	/**
	 * Return the database set list for a table
	 * @param string	The table name
	 * @return array||null
	 */
	public function getDatabaseSetlist($strTable='')
	{
		if(strlen($strTable) > 0)
		{
			return $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable];
		}
		return $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'];
	}
	
	
	/**
	 * Clear a database set list array by a table name
	 * @param string	The table name
	 */
	public function clearDatabaseSetlist($strTable)
	{
		$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable] = array();
			
	}
}