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
    public function supportsContext($context)
    {
       return in_array($context, ['page', 'link'], true) && $this->getUser()->hasAccess('page', 'modules');
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
		$objUser->admin = 1;
		$objUser->isAdmin = 1;
		
		return $objUser;	   
	}
}