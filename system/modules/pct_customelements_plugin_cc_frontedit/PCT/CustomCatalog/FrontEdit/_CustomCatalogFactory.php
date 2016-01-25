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
class CustomCatalogFactory extends \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory
{
	/**
	 * @inherit doc
	 */
	public static function findByModel($objModel)
	{
		$objCC = parent::findByModel($objModel);
		if($objCC === null)
		{
			return null;
		}
		
		// convert
		$objReturn = self::__override($objCC);
		$objReturn->reset();
		$objReturn->setData($objCC->getData());
		$objReturn->setOrigin($objModel);
		
		return $objReturn;
	}
	
	
	/**
	 * Convert one object to new class object
	 * @param object	Input object
	 * @param string	New output object class
	 * @return object	New object with all data from the input object
	 */
	protected function __override($objInput,$strNewClass='\PCT\CustomCatalog\FrontEdit\CustomCatalog')
	{
		if(strlen($strNewClass) < 1)
		{
			$strNewClass = '\PCT\CustomCatalog\FrontEdit\CustomCatalog';
		}
		
		if(!class_exists($strNewClass))
		{
			return null;
		}
		
		// create new class object
		$_new = new $strNewClass;
		
		foreach($objInput as $key => $val) 
		{
	        $_new->{$key} = $val;
	    }
	    
	    return $_new;
	}
}
 