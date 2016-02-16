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
 * Override Contaos BackendPage controller class and make it accessible from the front end
 */
class FrontendPage extends \Contao\BackendPage
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
				
		$this->User = new \PCT\Contao\_FrontendUser($this->User, array('customcatalog_edit_active' => 1));
		
		// set pagemounts
		$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = \PageModel::findPublishedRootPages()->fetchEach('id');
		$GLOBALS['loadDataContainer']['tl_page'] = true;
		if($this->User->pagemounts)
		{
			$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = deserialize($this->User->pagemounts);
		}
	}
	
	public function run()
	{
		return parent::run();
	}
}