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
 * RowTemplate
 */
class RowTemplate extends \PCT\CustomElements\Plugins\CustomCatalog\Core\RowTemplate
{
	/**
	 * Override the classes
	 * @param array
	 * @param object
	 * @return array
	 * @called from $GLOBALS['CUSTOMCATALOG_HOOKS'][getEntries]
	 */
	public function __override($arrEntries)
	{
		if(count($arrEntries) < 1 || !is_array($arrEntries))
		{
			return $arrEntries;
		}
		$arrReturn = array();
		foreach($arrEntries as $i => $objRowTemplate)
		{
			$_this = new self();
			foreach($objRowTemplate as $key => $val) 
			{
	            $_this->{$key} = $val;
	        }
	        $arrReturn[$i] = $_this;
		}
		return $arrReturn;
	}
	
	
	/**
	 * Check if the frontend user has access to edit an entry
	 * @param string	A table name
	 * @param integer	Id of an entry
	 * @return boolean
	 */
	public function editable($strTable='', $intId='')
	{
		// module settings
		if(!$this->getCustomCatalog()->getOrigin()->customcatalog_edit_active)
		{
			return false;
		}
		
		// user level
		if(FE_USER_LOGGED_IN && !$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'])
		{
			$objUser = new \PCT\Contao\FrontendUser( \FrontendUser::getInstance() );
			if(!$objUser->hasGroupAccess(deserialize($this->getCustomCatalog()->getOrigin()->reg_groups)))
			{
				return false;
			}
		}
		
		// custom catalog level	
		if(!\PCT\CustomCatalog\FrontEdit::checkPermissions($strTable, $intId))
		{
			return false;
		}
				
		return true;
	}
	
	
	/**
	 * Create the operations buttons list for an entry
	 * @param string		The output template
	 * @return string
	 */
	public function buttons($strTemplate='buttons')
	{
		$objFrontEdit = new \PCT\CustomCatalog\FrontEdit();
		
		// config object
		$objConfig = new \StdClass;
		$objConfig->customcatalog = $this->getCustomCatalog();
		$objConfig->activeRecord = $this->get('objActiveRecord');
		
		$objTemplate = $objFrontEdit->addButtonsToTemplateByRow(new \FrontendTemplate($strTemplate), $this->get('objActiveRecord'), $objConfig);
		
		return $objTemplate->parse();
	}
}