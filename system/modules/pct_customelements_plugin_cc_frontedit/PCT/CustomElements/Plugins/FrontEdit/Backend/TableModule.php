<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2017
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements
 * @subpackage	pct_customelements_plugin_customcatalog
 * @subpackage	pct_customelements_plugin_cc_frontedit
 * @link		http://contao.org
 */
 
 
/**
 * Namespace
 */
namespace PCT\CustomElements\Plugins\FrontEdit\Backend;


/**
 * Class file
 * TableModule
 */
class TableModule extends \Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}
	
	
	/**
	 * Modify the dca on load
	 */
	public function modifyDca(\DataContainer $objDC)
	{
		$objActiveRecord = $objDC->activeRecord;
		if(!$objActiveRecord)
		{
			$objActiveRecord = \Database::getInstance()->prepare("SELECT * FROM ".$objDC->table." WHERE id=?")->limit(1)->execute($objDC->id);
		}
		
		if( in_array($objActiveRecord->type, array('customcataloglist','customcatalogreader')) )
		{
			// load filter module templates
			$GLOBALS['TL_DCA'][$objDC->table]['fields']['customcatalog_mod_template']['options_callback'] = array(get_class($this),'getFrontEditModuleTemplates');
		}
	}
	
	
	/**
	 * Return all custom catalog module templates as array
	 * @param object 
	 */
	public function getFrontEditModuleTemplates(\DataContainer $objDC)
	{
		return array_merge($this->getTemplateGroup('mod_customcatalog'), $this->getTemplateGroup('mod_customcatalogfrontedit') );
	}	
}