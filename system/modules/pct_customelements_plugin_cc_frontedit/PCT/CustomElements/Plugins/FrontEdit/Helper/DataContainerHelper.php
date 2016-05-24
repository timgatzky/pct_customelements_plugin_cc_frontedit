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
 */
 
/**
 * Namespace
 */
namespace PCT\CustomElements\Plugins\FrontEdit\Helper;

/**
 * Class file
 * DataContainerHelper
 */
class DataContainerHelper extends \PCT\CustomElements\Helper\DataContainerHelper
{
	public function __construct($strTable='', $arrModule=array(), $blnHardLoaded=false)
	{
		parent::__construct($strTable,$arrModules,$blnHardLoaded);
		if($this->Database === null)
		{
			$this->Database = \Database::getInstance();
		}
		if($this->Session === null)
		{
			$this->Session = \Session::getInstance();
		}
	}
	
	/**
	 * Make the reviseTable method public
	 */
	public function reviseTable()
	{
		return parent::reviseTable();
	}
}