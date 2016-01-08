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
	 * @return boolean
	 */
	public function editable()
	{
		// return if no user is logged in
		#if(!FE_USER_LOGGED_IN)
		#{
		#	return false;
		#}
		
		$this->import('FrontendUser','User');
		
		return true;
	}
	
	
	/**
	 * Create the operations buttons list for an entry
	 * @param string		The output template
	 * @return string
	 */
	public function getEditButtons($strTemplate='buttons')
	{
		$objFrontEdit = new \PCT\CustomCatalog\FrontEdit();
		
		// config object
		$objConfig = new \StdClass;
		$objConfig->customcatalog = $this->getCustomCatalog();
		$objConfig->activeRecord = $this->get('objActiveRecord');
		
		$objTemplate = $objFrontEdit->addButtonsToTemplateByRow(new \FrontendTemplate($strTemplate), $this->get('objActiveRecord'), $objConfig);
		
		return $objTemplate->parse();
	}
	
	
	/**
	 * @shortcut
	 */
	public function editButtons() {return $this->getEditButtons();}
	
	
	
}