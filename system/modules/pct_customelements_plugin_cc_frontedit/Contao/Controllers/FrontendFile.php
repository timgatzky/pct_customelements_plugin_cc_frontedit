<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2015 Leo Feyer
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
namespace Contao\Controllers;

/**
 * Class file
 * Override Contaos BackendFile controller class and make it accessible from the front end
 */
class FrontendFile extends \Contao\BackendFile
{
	public function __construct()
	{
		if(TL_MODE == 'BE')
		{
			return parent::__construct();
		}
		
		$this->import('FrontendUser','User');
		$this->import('Database');
		$this->import('Session');
	}
	
	public function run()
	{
		$GLOBALS['TL_DCA']['tl_files']['list']['sorting']['root'] = array($GLOBALS['TL_CONFIG']['uploadPath']);
		$GLOBALS['loadDataContainer']['tl_files'] = true;
		if($this->User->filemounts)
		{
			$objFiles = \FilesModel::findMultipleByUuids(array_map('StringUtil::binToUuid',deserialize($this->User->filemounts)));
			$GLOBALS['TL_DCA']['tl_files']['list']['sorting']['root'] = $objFiles->fetchEach('path');
		}
		return parent::run();
	}
}	