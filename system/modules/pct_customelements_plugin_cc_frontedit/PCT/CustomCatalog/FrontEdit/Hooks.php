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
 * Hooks
 */
class Hooks extends \PCT\CustomElements\Plugins\CustomCatalog\Core\Hooks
{
	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;


	/**
	 * Instantiate this class and return it (Factory)
	 * @param string
	 * @return object
	 * @throws Exception
	 */
	public static function getInstance($strTable='')
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new self();
		}
		return self::$objInstance;
	}
	
	
	/**
	 * Call the storeDatabase HOOK
	 * Triggered any time FrontEdit updates the database of a certain table
	 * @param array		The current database set array
	 * @param string	The table name
	 * @param object	The module
	 * @return array
	 * Triggered in: PCT\CustomElements\Plugins\CustomCatalog\Core\Filter
	 */
	public function storeDatabaseHook($arrSet,$strTable,$objModule)
	{
		if (isset($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['storeDatabase']) && count($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['storeDatabase']) > 0)
		{
			foreach($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['storeDatabase'] as $callback)
			{
				$arrSet = \System::importStatic($callback[0])->{$callback[1]}($arrSet,$strTable,$objModule);
			}
		}

		return $arrSet;
	}
}