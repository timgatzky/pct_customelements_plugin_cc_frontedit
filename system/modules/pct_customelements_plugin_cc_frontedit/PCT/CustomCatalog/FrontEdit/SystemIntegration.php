<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
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
namespace PCT\CustomCatalog\FrontEdit;

/**
 * Imports
 */
use Contao\System;
use Contao\File;
use Contao\Controller;


/**
 * Class file
 * SystemIntegration
 */
class SystemIntegration extends System
{
	/**
	 * Create the yaml files (or append them) depending on the current Contao version
	 * @return void 
	 * 
	 * called from initializeSystem Hook
	 */
	public function createYaml()
	{
		if( \version_compare(\VERSION, '4.4','<=') )
		{
			$this->createConfigYml();
		}
		else if( \version_compare(\VERSION, '4.9','==')  )
		{
			$this->createServiceYml();
		}
		else 
		{
			static::log('CC Frontedit: This Contao Version is not supported',__METHOD__,\TL_CRON);
			return;
		}	
	}
	
	
	/**
	 * Create/append config.yaml (Contao 4.4)
	 */
	protected function createConfigYml()
	{
		$strEnvironment = '';
		
		// current symphony environment
		#$strEnvironment = \System::getContainer('kernel')->getParameter('kernel.environment');
		
		$strFile = 'config'.($strEnvironment ? '_'.$strEnvironment : '').'.yml';
		
		// fetch the file
		$objFile = new File('app/config/'.$strFile,true);

		$arrYaml = array();
		// parse current yaml to array
		if( $objFile->exists() === true )
		{
			$arrYaml = \Symfony\Component\Yaml\Yaml::parseFile('../app/config/'.$strFile);
		}

		if( \array_key_exists('services',$arrYaml) === false )
		{
			$arrYaml['services'] = array();
		}

		// yaml created
		if( empty($arrYaml['services']['contao.picker.builder']) === false )
		{
			return;
		}

		// append services
		$arrYaml['services']['contao.picker.builder'] = array
		(
			'class' => 'PCT\Contao\Picker\PickerBuilder',
			//'arguments' => array(['@knp_menu.factory'], ['@router'], ['@request_stack'])
			'arguments' => array('@knp_menu.factory', '@router', '@request_stack')
		);
		
		$arrYaml['services']['contao.picker.page_provider'] = array('class' => 'PCT\Contao\Picker\PagePickerProvider');
		$arrYaml['services']['contao.picker.file_provider'] = array('class' => 'PCT\Contao\Picker\FilePickerProvider');

		$objDumper = new \Symfony\Component\Yaml\Dumper();
		
		$objFile->write( $objDumper->dump($arrYaml) );
		$objFile->close();
			
		// log
		static::log('CC Frontedit: /app/config/'.$strFile.' created or updated successfully',__METHOD__,TL_CRON);
		
		// reload the page to make changes take effect
		Controller::reload();
	}


	/**
	 * Create/append service.yaml (Contao 4.9)
	 */
	protected function createServiceYml()
	{
		$strEnvironment = '';
		
		// current symphony environment
		#$strEnvironment = \System::getContainer('kernel')->getParameter('kernel.environment');
		
		$strFile = 'service'.($strEnvironment ? '_'.$strEnvironment : '').'.yaml';
		
		// fetch the file
		$objFile = new \Contao\File('app/config/'.$strFile,true);
		
		$arrYaml = array();
		
		// parse current yaml to array
		if( $objFile->exists() === true )
		{
			$arrYaml = \Symfony\Component\Yaml\Yaml::parseFile('../app/config/'.$strFile);
		}

		if( \array_key_exists('services',$arrYaml) === false )
		{
			$arrYaml['services'] = array();
		}

		// yaml created
		if( empty($arrYaml['services']['contao.picker.builder']) === false )
		{
			return;
		}

		$arrYaml['services']['_default']['autowire'] = true;
		$arrYaml['services']['_default']['autoconfigure'] = true;
		$arrYaml['services']['_default']['public'] = true;

		// append services
		$arrYaml['services']['contao.picker.builder'] = array
		(
			'class' => 'PCT\Contao\Picker\PickerBuilder',
			//'arguments' => array(['@knp_menu.factory'], ['@router'], ['@request_stack'])
			'arguments' => array('@knp_menu.factory', '@router'),
			'public' => true
		);
		
		$arrYaml['services']['contao.picker.page_provider'] = array('class' => 'PCT\Contao\Picker\PagePickerProvider','public'=>true);
		$arrYaml['services']['contao.picker.file_provider'] = array('class' => 'PCT\Contao\Picker\FilePickerProvider','public'=>true);

		$objDumper = new \Symfony\Component\Yaml\Dumper();
		
		$objFile->write( $objDumper->dump($arrYaml) );
		$objFile->close();
			
		// log
		static::log('CC Frontedit: /app/config/'.$strFile.' created or updated successfully',__METHOD__,\TL_CRON);
		
		// reload the page to make changes take effect
		Controller::reload();
	}
	
	
	public function modifyDca($strTable)
	{
		if($strTable == 'tl_files')
		{
			unset($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']);
		}
	}
}