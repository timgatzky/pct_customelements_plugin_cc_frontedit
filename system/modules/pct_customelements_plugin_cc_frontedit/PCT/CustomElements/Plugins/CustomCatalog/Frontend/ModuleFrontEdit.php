<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2013 Leo Feyer
 * 
 * @copyright	Tim Gatzky 2015
 * @author		Tim Gatzky <info@tim-gatzky.de>
 * @package		pct_customelements
 * @subpackage	pct_customelements_plugin_customcatalog
 * @subpackage	pct_customelements_plugin_cc_frontedit
 * @link		http://contao.org
 */

/**
 * Namespace
 */
namespace PCT\CustomElements\Plugins\CustomCatalog\Frontend;

/**
 * Imports
 */
use PCT\CustomCatalog\FrontEdit\CustomCatalogFactory as CustomCatalogFactory;

/**
 * Class file
 * ModuleFrontEdit
 */
class ModuleFrontEdit extends \PCT\CustomElements\Plugins\CustomCatalog\Frontend\ModuleReader
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_customcatalogfrontedit';
	
	/**
	 * Flag if the reader should render content elements
	 */
	protected $blnRenderContentElements = false;
	
	
	/**
	 * Display wildcard
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['customcatalogfrontedit'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			
			return $objTemplate->parse();
		}
		
// !todo: custom error page here
		if(!$this->isEditable())
		{
			global $objPage;
			$objPage->noSearch = 1;
			$objPage->cache = 0;
			/** @var \PageError404 $objHandler */
			$objHandler = new $GLOBALS['TL_PTY']['error_404']();
			$objHandler->generate($objPage->id);

			return '';
		}
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 * @return string
	 */
	protected function compile()
	{
		parent::compile();
		
		$objCC = $this->CustomCatalog;
		if(!$objCC)
		{
			global $objPage;
			$objPage->noSearch = 1;
			$objPage->cache = 0;
			/** @var \PageError404 $objHandler */
			$objHandler = new $GLOBALS['TL_PTY']['error_404']();
			$objHandler->generate($objPage->id);

			return '';
		}
		
		// form vars
		$formName = 'cc_frontedit_'.$this->id;
		
		// save button
		$this->Template->hasSave = true;
			
		$objSubmit = new \FormSubmit();
		$objSubmit->__set('id', $formName.'_save');
		$objSubmit->__set('name', 'save');
		$objSubmit->__set('value', $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'save');
		$objSubmit->__set('label', $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'Save');
		$objSubmit->__set('class','submit');
		$this->Template->saveSubmit = $objSubmit->generateWithError();
		$this->Template->submitLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['label_save'] ?: 'Save';
		
		// hidden fields
		$arrHidden = array
		(
			'id'	=> \Input::get('id'),
			'pid'	=> \Input::get('pid') ?: 0,
			'table'	=> $objCC->getTable(),
		);
		
		$strHidden = '';
		foreach(array_filter($arrHidden) as $f => $v)
		{
			$strHidden .= '<input type="hidden" name="'.$f.'" value="'.$v.'">';
		}
		
		$this->Template->formSubmit = $formName;
		$this->Template->formId = $formName;
		$this->Template->formName = $formName;
		$this->Template->method = 'post';
		$this->Template->action = \Environment::get('request');
		$this->Template->tableless = true;
		$this->Template->formClass = 'filterform';
		$this->Template->hidden = $strHidden;
		
		//-- handle form actions
		
		
		
	}


	protected function isEditable()
	{
		// check if edit modes are active
		if(!in_array(\Input::get('act'),$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['defaultOperations']))
		{
			return false;
		}
		
		// check if element is allowed and FE User has rights
		
		return true;
	}
	
	
	
}