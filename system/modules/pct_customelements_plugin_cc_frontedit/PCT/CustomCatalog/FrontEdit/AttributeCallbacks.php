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
 * Attribute
 */
class AttributeCallbacks extends \PCT\CustomElements\Plugins\CustomCatalog\Core\Attribute
{
	/**
	 * Overwrite the regular render methode
	 */
	public function parseWidgetCallback($objWidget,$strField,$arrFieldDef,$objDC)
	{
		\FB::log($objWidget);
		return $objWidget->parse();
	}
}
 