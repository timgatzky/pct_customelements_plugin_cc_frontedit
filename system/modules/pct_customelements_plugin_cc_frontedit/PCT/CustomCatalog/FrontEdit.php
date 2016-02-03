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
use PCT\CustomCatalog\FrontEdit\Controller as Helper;


/**
 * Class file
 * FrontEdit
 */
class FrontEdit extends \PCT\CustomCatalog\FrontEdit\Controller
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
	 * General check if editing is allowed and/or active
	 * @param string	Tablename
	 * @param integer	A certain entry id that should be checked
	 * @return boolean
	 */
	public function isEditable($strTable='', $intId=0)
	{
		// clearing the clipboard is allowed
		if(strlen($strTable) > 0 && \Input::get('clear_clipboard') != '')
		{
			return true;
		}
		
		// fronedit is most likely not active
		if(!\Input::get('do'))
		{
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Check user permissions
	 * @param string	Tablename
	 * @param integer	A certain entry id that should be checked
	 * @return boolean
	 */
	public function checkPermissions($strTable='', $intId=0)
	{
		// check if editing is allowed for all or in general for FE Users only
		if( $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === false && !FE_USER_LOGGED_IN )
		{
			return false;
		}
		
		$objUser = new \PCT\Contao\FrontendUser( \FrontendUser::getInstance() , array('customcatalog_edit_active' => 1));
		
		// check user rights
		if( !$objUser->get('customcatalog_edit_active') || $objUser->customcatalog_edit_disable )
		{
			return false;
		}
		
		// check permissions for a particular entry
		if(strlen($strTable) > 0 && $intId > 0)
		{
			return $this->isEditable($strTable, $intId);
		}
				
		return true;
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
	
	
	/**
	 * Clear helper session
	 * @param string
	 */
	public function clearSession($strTable='')
	{
		if(strlen($strTable) > 0)
		{
			$arrSession = \Session::getInstance()->get($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']);
			unset($arrSession[$strTable]);
			\Session::getInstance()->set($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName'],$arrSession);
		}
		else
		{
			\Session::getInstance()->remove($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']);
		}
	}
}