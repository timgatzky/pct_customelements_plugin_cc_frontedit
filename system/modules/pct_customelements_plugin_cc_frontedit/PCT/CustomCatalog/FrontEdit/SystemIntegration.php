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
		$objFile = new \File('app/config/'.$strFile,true);
		
		$strContent = '';
		if($objFile->exists())
		{
			$strContent = $objFile->getContent();
		}
		
		// content parts
		$arrParts = array();
		$arrParts['contao.picker.builder::customcatalog_frontedit'] = "
# contao.picker.builder::customcatalog_frontedit
services:
   contao.picker.builder:
      class: PCT\Contao\Picker\PickerBuilder
      arguments:
            - '@knp_menu.factory'
            - '@router'
            - '@request_stack'
";

$arrParts['contao.picker.page_provider::customcatalog_frontedit'] = "
# contao.picker.page_provider::customcatalog_frontedit
services:
   contao.picker.page_provider:
      class: PCT\Contao\Picker\PagePickerProvider
";

$arrParts['contao.picker.file_provider::customcatalog_frontedit'] = "
# contao.picker.file_provider::customcatalog_frontedit
services:
   contao.picker.file_provider:
      class: PCT\Contao\Picker\FilePickerProvider
";

		$blnWrite = false;
		foreach($arrParts as $ident => $strPart)
		{
			// part already exists
			if(strlen(strpos($strContent,$ident)) > 0)
			{
				continue;
			}
			
			$strContent .= $strPart;
			$blnWrite = true;
		}
		
		// write the config_...yml file
		if($blnWrite)
		{
			$objFile->write($strContent);
			$objFile->close();
			
			// log
			\System::log('CC Frontedit: /app/config/'.$strFile.' created or updated successfully',__METHOD__,TL_CRON);
			
			// reload the page to make changes take effect
			\Controller::reload();
		}
	}
	
	
	public function modifyDca($strTable)
	{
		if($strTable == 'tl_files')
		{
			unset($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']);
		}
	}
}