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
 * FrontendPctTableTree
 */
class FrontendPctTableTree extends \Contao\BackendPctTableTree
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
	}
	
	public function run()
	{
		return parent::run();
	}
}	