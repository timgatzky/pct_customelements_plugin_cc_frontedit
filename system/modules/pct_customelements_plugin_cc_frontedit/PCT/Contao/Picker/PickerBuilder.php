<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @copyright Tim Gatzky 2017
 * @author  Tim Gatzky <info@tim-gatzky.de>
 * @package  pct_customelements
 * @subpackage pct_customelements_plugin_customcatalog
 * @subpackage pct_customelements_plugin_cc_frontedit
 * @link  http://contao.org
 */

/**
 * Namespace
 */
namespace PCT\Contao\Picker;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Contao\CoreBundle\Security\Authentication\ContaoToken;

/**
 * Class file
 * PickerBuilder
 */
class PickerBuilder extends \Contao\CoreBundle\Picker\PickerBuilder
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct(FactoryInterface $menuFactory, RouterInterface $router, RequestStack $requestStack)
	{
		if(TL_MODE == 'BE')
		{
			return parent::__construct($menuFactory,$router,$requestStack);
		}

		$this->menuFactory = $menuFactory;
		$this->router = $router;
		$this->requestStack = $requestStack;
		
		$user = new \PCT\Contao\BackendUser;
		$user->admin = 1;
		$user->isAdmin = 1;
		
		#\Debug::log($user);

		$this->tokenStorage = new ContaoToken($user);

		#$this->tokenStoage
		#\Debug::log($this->tokenStorage);

		parent::__construct($menuFactory,$router,$requestStack);
	}


	# public function setTokenStorage(TokenStorageInterface $tokenStorage)
	#    {
	#     $this->tokenStorage = $tokenStorage;
	#    }



	/**
	 * {@inheritdoc}
	 */
	public function supportsContext($context, array $allowed = null)
	{
		if(TL_MODE == 'BE')
		{
			return parent::supportsContext($context,$allowed);
		}

		if(!FE_USER_LOGGED_IN && $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'])
		{
			return true;
		}

		return in_array($context, ['file', 'link'], true) && $this->getUser()->hasAccess('files', 'modules');
	}


	/**
	 * {@inheritdoc}
	 */
	public function getUser()
	{
		if(TL_MODE == 'BE')
		{
			return parent::getUser();
		}

		#$objMember = \MemberModel::findByPk( \Controller::replaceInsertTags('{{user::id}}') );
		$objUser = new \PCT\Contao\_FrontendUser($objMember,array('customcatalog_edit_active' => 1));
		$objUser->isAdmin = 1;

		return $objUser;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getUrl($context, array $extras = [], $value = '')
	{
		if(TL_MODE == 'BE')
		{
			return parent::getUrl($context, $extras, $value);
		}

		$providers = (isset($extras['providers']) && is_array($extras['providers'])) ? $extras['providers'] : null;

		if (!$this->supportsContext($context, $providers))
		{
			return '';
		}
		
		$arrParams = array('context'=>$context,'picker_builder'=>'1','popup' => 1);
		if($context == 'file')
		{
			$arrParams['do'] = 'files';
		}
		
		if(count($extras) > 0)
		{
			$arrParams = array_merge($arrParams,$extras);
		}
	
		#$t = "http://dev.contao4:8888/_contao/picker?context=file&extras%5BfieldType%5D=radio&extras%5BfilesOnly%5D=1&extras%5Bpath%5D=files/uploads&extras%5Bextensions%5D=png&value=";
		$strUrl = PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/main.php?'.http_build_query($arrParams);
		$strUrl = str_replace('%2F', '/',$strUrl);
		
		return $strUrl;
	}


	public function createFromData($data)
	{
		if(TL_MODE == 'BE')
		{
			return parent::createFromData($data);
		}
		
		$objPicker = null;
		$strContext = \Input::get('context');
		$strCurrent = '';

		if($strContext == 'file')
		{
			// create new filepicker
			$objPicker = new \PCT\Contao\Picker\FilePickerProvider($this->menuFactory,$this->router,\Config::get('uploadPath') ?: 'files');
		}

		if($objPicker === null)
		{
			return null;
		}
		
		// add as new provider
		$this->addProvider($objPicker);
		
		$strCurrent = $objPicker->getName();
		
		$arrExtras = array
		(
			'extensions'	=> \Input::get('extensions'),
			'filesOnly'		=> \Input::get('filesOnly'),
			'fieldType'		=> \Input::get('fieldType'),
			'path'			=> \Input::get('path')
		);
		
		$varValue = \Input::get('value');

		// create new picker config
		$objConfig = new \Contao\CoreBundle\Picker\PickerConfig($strContext,array_filter($arrExtras),$varValue,$strCurrent);
		
		$objReturn = $this->create($objConfig);
		
		return $objReturn;
	}
}