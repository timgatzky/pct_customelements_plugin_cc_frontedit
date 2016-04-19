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
	/**
	 * Make the reviseTable method public
	 */
	public function reviseTable()
	{
		if(!$this->Session)
		{
			$this->Session = \Session::getInstance();
		}
		if(!$this->Database)
		{
			$this->Database = \Database::getInstance();
		}
		return parent::reviseTable();
	}
}