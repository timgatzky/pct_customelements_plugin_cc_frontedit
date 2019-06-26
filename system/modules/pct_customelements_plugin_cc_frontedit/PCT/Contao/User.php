<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2019
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
 * User
 * Make the regular Contao User class more flexible
 */
class User extends \Contao\User
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @inheritdoc
	 */
	public function setUserFromDb() 
	{
		parent::setUserFromDb();
	}

	/**
	 * @inheritdoc
	 */
	public function authenticate()
	{
		// allow all
		if((boolean)$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === true)
		{
			$this->admin = 1;
			$this->isAdmin = 1;
		}

		if( $this->admin )
		{
			return true;
		}

		parent::authenticate();
	}
}