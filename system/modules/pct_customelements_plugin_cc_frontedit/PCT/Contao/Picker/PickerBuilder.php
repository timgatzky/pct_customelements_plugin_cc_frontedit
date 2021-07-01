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

use Contao\CoreBundle\Picker\Picker as CorePicker;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Contao\Input;
use Contao\Config;
use Contao\CoreBundle\Picker\FilePickerProvider;
use Contao\CoreBundle\Picker\PagePickerProvider;
use Contao\CoreBundle\Picker\PickerConfig;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;

/**
 * Class file
 * PickerBuilder
 */
class PickerBuilder extends \Contao\CoreBundle\Picker\PickerBuilder
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct(FactoryInterface $menuFactory, RouterInterface $router)
	{
		$this->menuFactory = $menuFactory;
		$this->router = $router;
		$this->translator = System::getContainer()->get('translator');
		$this->security = System::getContainer()->get('security.helper');
		$this->tokenStorage = $this->security->getToken();
		$this->framework = System::getContainer()->get('contao.framework');

		if(TL_MODE == 'BE')
		{
			return parent::__construct($menuFactory,$router);
		}
			
		parent::__construct($menuFactory,$router);
	}

	/**
	 * {@inheritdoc}
	 */
	public function supportsContext($context, array $allowed = null): bool
	{
		$objPicker = null;

		// use core picker providers in backend
		if( TL_MODE == 'BE')
		{
			// create new filepicker	
			if($context == 'file')
			{
				$objPicker = new FilePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security,Config::get('uploadPath') ?: 'files');
			}
			// create new pagepicker	
			else if($context == 'page' || $context == 'link')
			{
				$objPicker = new PagePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security);
			}
			
			if( $objPicker === null )
			{
				return false;
			}

			$this->addProvider($objPicker);
			return parent::supportsContext($context,$allowed);
		}
		else if( TL_MODE == 'FE' )
		{
			// create new filepicker	
			if($context == 'file')
			{
				$objPicker = new \PCT\Contao\Picker\FilePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security,Config::get('uploadPath') ?: 'files');
			}
			// create new pagepicker	
			else if($context == 'page')
			{
				// create new filepicker
				$objPicker = new \PCT\Contao\Picker\PagePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security);
			}
			
			$this->addProvider($objPicker);
			
			return $objPicker->supportsContext($context, $allowed);
		}

		return parent::supportsContext($context,$allowed);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getUrl($context, array $extras = [], $value = ''): string
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
		else if($context == 'page' || $context == 'link')
		{
			$arrParams['do'] = 'page';
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

	
	/**
	 * {@inheritdoc}
	 */
    public function createFromData($data): ?CorePicker
	{
		if(TL_MODE == 'BE')
		{
			return parent::createFromData($data);
		}
		
		$objPicker = null;
		$strContext = Input::get('context');
		$strCurrent = '';

		// create new filepicker	
		if($strContext == 'file')
		{
			$objPicker = new \PCT\Contao\Picker\FilePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security,Config::get('uploadPath') ?: 'files');
		}
		// create new pagepicker	
		else if($strContext == 'page')
		{
			// create new filepicker
			$objPicker = new \PCT\Contao\Picker\PagePickerProvider($this->menuFactory,$this->router);
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
			'extensions'	=> Input::get('extensions'),
			'filesOnly'		=> Input::get('filesOnly'),
			'fieldType'		=> Input::get('fieldType'),
			'path'			=> Input::get('path')
		);
		
		// get the value from the url GET parameter
		$varValue = Input::get('value');
		
		if( Validator::isBinaryUuid($varValue) )
		{
			$varValue = StringUtil::binToUuid( $varValue );
		}

		// contao expects value parameter to be a string
		if(is_array($varValue))
		{
			foreach($varValue as $i => $v)
			{
				if( Validator::isBinaryUuid($v) )
				{
					$varValue[$i] = StringUtil::binToUuid($v);
				}
			}
			$varValue = implode(',', array_filter($varValue));
		}
		
		// create new picker config
		$objConfig = new \Contao\CoreBundle\Picker\PickerConfig($strContext,array_filter($arrExtras),$varValue,$strCurrent);
		
		return $this->create($objConfig);;
	}


	/**
	 * {@inheritdoc}
	 */
	public function create(PickerConfig $config): ?CorePicker
	{
		$context = $config->getContext();

		$objPicker = null;

		if(TL_MODE == 'BE')
		{	
			// create new filepicker	
			if($context == 'file')
			{
				$objPicker = new FilePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security,Config::get('uploadPath') ?: 'files');
				$objPicker->setFramework( $this->framework );
			}
			// create new pagepicker	
			else if($context == 'page' || $context == 'link')
			{
				$objPicker = new PagePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security);
			}

			if( $objPicker === null )
			{
				return false;
			}
			
			$this->addProvider($objPicker);
		}
		#else
		#{
		#	// create new filepicker	
		#	if($context == 'file')
		#	{
		#		$objPicker = new \PCT\Contao\Picker\FilePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security,Config::get('uploadPath') ?: 'files');
		#	}
		#	// create new pagepicker	
		#	else if($context == 'page')
		#	{
		#		// create new filepicker
		#		$objPicker = new \PCT\Contao\Picker\PagePickerProvider($this->menuFactory,$this->router,$this->translator,$this->security);
		#	}
		#	
		#	$this->addProvider($objPicker);
		#}

		return parent::create($config);
	}
}