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

use Contao\BackendUser;
use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class file
 * PagePickerProvider
 */
class PagePickerProvider extends \Contao\CoreBundle\Picker\PagePickerProvider
{
	/**
     * {@inheritdoc}
     */
	public function __construct(FactoryInterface $menuFactory, RouterInterface $router)
    {
        #$objFramework = \System::getContainer()->get('contao.framework');
        
        // set the framework for this class and for the parent class object
        #$this->setFramework( $objFramework );
		#parent::setFramework( $objFramework );
		
		parent::__construct($menuFactory, $router);
    }
    
	/**
     * {@inheritdoc}
     */
    public function supportsContext($context):bool
    {
       return in_array($context, ['page', 'link'], true) && $this->getUser()->hasAccess('page', array('page','modules'));
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getUser(): BackendUser
    {
	   	if(TL_MODE == 'BE')
		{
			return parent::getUser();
		}
		
		$objFrontendUser = FrontendUser::getInstance();
			
		// @var object \PCT\Contao\_FrontendUser
		$this->User = new \PCT\Contao\_FrontendUser($objFrontendUser,array('customcatalog_edit_active' => 1));
		
		// allow all
		if((boolean)$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === true)
		{
			$this->User->admin = 1;
			$objRoots = PageModel::findBy(array('type=?','published=1'), array('root'));
			if($objRoots !== null)
			{
				$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = $objRoots->fetchEach('id');
			}
		}
		
		// merge with member
		if(FE_USER_LOGGED_IN && (boolean)$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'] === false)
		{
			// set pagemounts
			$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = PageModel::findPublishedRootPages()->fetchEach('id');
			$GLOBALS['loadDataContainer']['tl_page'] = true;
			if($this->User->pagemounts)
			{
				$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['root'] = StringUtil::deserialize($this->User->pagemounts);
			}
		}	
		
		return $this->User;  
	}
}