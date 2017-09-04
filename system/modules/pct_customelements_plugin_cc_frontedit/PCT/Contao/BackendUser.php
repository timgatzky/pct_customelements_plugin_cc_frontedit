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
namespace PCT\Contao;

/**
 * Class file
 * Override Contaos BackendUser class
 */
class BackendUser extends \Contao\BackendUser
{
	public function __construct()
	{
		#\Debug::log('construct');
		$this->import('Database');
		$this->isAdmin = true;
		$this->strIp = \Environment::get('ip');
		$this->intId = 1;
		$this->strHash = '5fc8ca0e6b87f0627bc7b62376ebdf18f241681a'; #'123456';
		$this->admin = 1;
	}
	
	public function authenticate()
	{
		// Generate the session
		#$this->regenerateSessionId();
		#$this->generateSession();
		
		return true;
		
		#throw new \Exception('--- STOP ---');
	}
	
	public function hasAccess($t,$a)
	{
		return true;
	}
}