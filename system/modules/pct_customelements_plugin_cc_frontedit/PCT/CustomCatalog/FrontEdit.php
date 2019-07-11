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
			$this->set('objModule',$objConfig->customcatalog->getModule());
			
			if(!$objConfig->module)
			{
				$objConfig->module = $objConfig->customcatalog->getModule();
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
	 * General check if editing is allowed and/or active for a particular entry
	 * @param string	Tablename
	 * @param integer	Entry id
	 * @return boolean
	 */
	public static function isEditable($strTable='', $intId=0)
	{
		// (first level) exclude the whole CC table 
		if(strlen($strTable) > 0 && $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable] === true && !is_array($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable]))
		{
			return false;
		}
		
		// (second level) exclude the entry
		if(strlen($strTable) > 0 && $intId > 0 && is_array($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable]) && count($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable]) > 0)
		{
			if(in_array($intId,$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable]))
			{
				return false;
			}
		}
		
		// load the data container to the frontend
		if(!$GLOBALS['TL_DCA'][$strTable] && strlen($strTable) > 0)
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
		
		// table is closed
		if((boolean)$GLOBALS['TL_DCA'][$strTable]['config']['closed'] === true)
		{
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Check user permissions
	 * @return boolean
	 */
	public static function checkPermissions()
	{
		if($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === true)
		{
			return true;
		}
		
		// check if editing is allowed for all or in general for FE Users only
		if( $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === false && !FE_USER_LOGGED_IN )
		{
			return false;
		}
		
		$objUser = new \PCT\Contao\_FrontendUser( \FrontendUser::getInstance() , array('customcatalog_edit_active' => 1));
		
		// check user rights
		if( (boolean)$objUser->get('customcatalog_edit_active') === false || (boolean)$objUser->get('customcatalog_edit_disable') === true )
		{
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Add an attribute value to the database set list
	 * @param value		
	 * @param string		The table name
	 * @param integer		The record ID
	 * @param string		The field name
	 * @param object		The DataContainer object
	 */
	public static function addToDatabaseSetlist($varValue,$objDC)
	{
		$strTable = $objDC->table;
		$intId = $objDC->id;
		$objAttribute = $objDC->objAttribute;
		$strField = $objDC->field;
		
		if(!\Database::getInstance()->tableExists($strTable)) {return;}
		if(!\Database::getInstance()->fieldExists($strField,$strTable)) {return;}
		
		if(!is_array($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable]))
		{
			$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable] = array();
		}
		
		// trigger the storeValue HOOK from CE
		if($objDC->objAttribute)
		{
			$saveDataAs = $objDC->objAttribute->get('saveDataAs') ?: 'data';
			$arr = \PCT\CustomElements\Core\Hooks::callstatic( 'storeValueHook',array($objDC->objAttribute->get('id'),array($saveDataAs=>$varValue)) );
			if($arr[$saveDataAs] != $varValue)
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
	public static function getDatabaseSetlist($strTable='')
	{
		if(strlen($strTable) > 0)
		{
			return is_array($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable]) ? $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'][$strTable] : array();
		}
		return is_array($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST']) ? $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['DB_SET_LIST'] : array();
	}
	
	
	/**
	 * Clear a database set list array by a table name
	 * @param string	The table name
	 */
	public static function clearDatabaseSetlist($strTable)
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