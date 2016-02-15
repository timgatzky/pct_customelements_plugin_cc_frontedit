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
		
		// remove all preActions callbacks except the one from the pct_tabletree_widget to avoid unwanted calls to the backend that might cause Contao to force a backend login
		if(count($GLOBALS['TL_HOOKS']['executePreActions']) > 0)
		{
			$tmp = array();
			foreach($GLOBALS['TL_HOOKS']['executePreActions'] as $i => $callback)
			{
				if(strlen(strpos($callback[0],'PCT\Widgets\TableTree\TableTreeHelper')) > 0 && strlen(strpos($callback[1],'preActions')) > 0 )
				{
					$tmp[] = $callback;
				}
			}
			$GLOBALS['TL_HOOKS']['executePreActions'] = $tmp;
		}
	}
	
	public function run()
	{
		return parent::run();
	}
}	