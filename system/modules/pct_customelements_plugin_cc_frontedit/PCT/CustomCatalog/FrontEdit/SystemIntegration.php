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
 * Class file
 * SystemIntegration
 */
class SystemIntegration extends \Contao\System
{
	/**
	 * Create a config xml file for a Contao 4 environment to override system classes
	 */
	public function createConfigYml()
	{
		if(version_compare(VERSION, '4.4','<'))
		{
			return;
		}
				
		$strEnvironment = '';
		
		// current symphony environment
		#$strEnvironment = \System::getContainer('kernel')->getParameter('kernel.environment');
		
		$strFile = 'config'.($strEnvironment ? '_'.$strEnvironment : '').'.yml';
		
		// fetch the file
		$objFile = new \Contao\File('app/config/'.$strFile,true);

		// parse current config.yml to array
		$arrYaml = \Symfony\Component\Yaml\Yaml::parseFile('../app/config/'.$strFile);
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
		\System::log('CC Frontedit: /app/config/'.$strFile.' created or updated successfully',__METHOD__,TL_CRON);
		
		// reload the page to make changes take effect
		\Controller::reload();
	}
	
	
	public function modifyDca($strTable)
	{
		if($strTable == 'tl_files')
		{
			unset($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']);
		}
	}
}