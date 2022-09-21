<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2015 Leo Feyer
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
namespace PCT\Contao;

use Contao\System;

/**
 * Class file
 * Override Contaos BackendMain class
 */
class BackendMain extends \Contao\BackendMain
{
	/**
	 * Init
	 */
	public function __construct()
	{
		if(TL_MODE == 'BE')
		{
			return parent::__construct();
		}
		
		\Contao\System::loadLanguageFile('default');
		
		if(FE_USER_LOGGED_IN || (boolean)$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === true)
		{
			$objUser = \Contao\FrontendUser::getInstance();
			
			// import the fe user as cached User class for further use
			$this->import('\Contao\FrontendUser', 'User');
			
			$this->User = new \PCT\Contao\_FrontendUser($objUser);
			// trick Contaos access level and simulate an admin here
			$this->User->admin = 1;
		}
	}
	
	
	/**
	 * Run the controller and print the html output
	 */
	public function run()
	{
		if(TL_MODE == 'BE')
		{
			return parent::run();
		}
		
		// add css to the be_main template
		$GLOBALS['TL_CSS'][] = PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/css/contao/main.css';
		
		// prepare the be_main template
		$objResponse = parent::run();
		// fetch the html output
		$strBuffer = $objResponse->getContent();
		// replace static paths
		$strBuffer = str_replace(PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/', '', $strBuffer);
		// form action must point to full path
		$strBuffer = str_replace('main.php?',PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/main.php?',$strBuffer);
		// set header
		header('Content-Type: text/html; charset=' . \Contao\Config::get('characterSet'));
		// print it and exit script
		echo $strBuffer;
		
		exit;
	}
}