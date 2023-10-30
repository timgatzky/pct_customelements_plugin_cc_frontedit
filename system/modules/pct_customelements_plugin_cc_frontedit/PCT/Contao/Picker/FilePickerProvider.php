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
namespace PCT\Contao\Picker;

use Contao\CoreBundle\Picker\FilePickerProvider as CoreFilePickerProvider;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\FrontendUser;
use Contao\System;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Class file
 * FilePickerProvider
 */
class FilePickerProvider extends CoreFilePickerProvider
{
	/**
     * {@inheritdoc}
     */
	public function __construct(FactoryInterface $menuFactory, RouterInterface $router, TranslatorInterface $translator, Security $security, string $uploadPath)
    {
       	$objFramework = System::getContainer()->get('contao.framework');
        // set the framework for this class and for the parent class object
        $this->setFramework( $objFramework );
		parent::setFramework( $objFramework );
		parent::__construct($menuFactory, $router, $translator, $security, $uploadPath);
    }
    
	/**
     * {@inheritdoc}
     */
    public function supportsContext($context): bool
    {
		return in_array($context, ['file', 'link'], true) && $this->_getUser()->hasAccess('files', array('files','modules'));
    }
    
	public function _getUser()
	{
		$objFrontendUser = FrontendUser::getInstance();
		
		// @var object \PCT\Contao\_FrontendUser
		$this->User = new \PCT\Contao\_FrontendUser($objFrontendUser,array('customcatalog_edit_active' => 1));
		
		// allow all
		if((boolean)$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === true)
		{
			$this->User->admin = 1;
			return $this->User;
		}
		
		// merge with member
		if(FE_USER_LOGGED_IN)
		{
			// set filemounts
			$GLOBALS['TL_DCA']['tl_files']['list']['sorting']['root'] = array($GLOBALS['TL_CONFIG']['uploadPath']);
				
			$root = array();
			if($this->User->filemounts)
			{
				$objFiles = FilesModel::findMultipleByUuids(array_map('StringUtil::binToUuid', StringUtil::deserialize($this->User->filemounts)));
				$root = array_merge($root,$objFiles->fetchEach('path'));
			}
			
			if($this->User->assignDir && $this->User->homeDir)
			{
				$objFiles = FilesModel::findMultipleByUuids(array_map('StringUtil::binToUuid',array($this->User->homeDir)));
				$root = array_merge($root,$objFiles->fetchEach('path'));
			}
			
			$GLOBALS['TL_DCA']['tl_files']['list']['sorting']['root'] = array_unique($root);
		}
		
		return $this->User;	  	
	}
}
