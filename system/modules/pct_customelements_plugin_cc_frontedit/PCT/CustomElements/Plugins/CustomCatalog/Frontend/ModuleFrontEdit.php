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
		
		global $objPage;
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
		
		$objOrigTemplate = $this->Template;
		$this->Template = new \PCT\CustomCatalog\FrontEdit\FrontendTemplate($this->strTemplate);
		$this->Template->setData($objOrigTemplate->getData());
		$this->Template->raw = $objCC;
		foreach($objOrigTemplate as $key => $val) 
		{
            $this->Template->{$key} = $val;
        }
        
        if( in_array(\Input::get('act'), array('edit','editAll','overrideAll')) )
		{
			$this->Template->editMode = true;
			$this->Template->clipboard = true;
		}
		
		// form vars
		$formName = 'cc_frontedit_'.$this->id;
		
		//-- save button
		$this->Template->hasSave = true;
		$arr = array(
			'id'	=> $formName.'_save',
			'name'	=> 'save', 
			'strName' => 'save',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'save',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'Save',
			'class' => 'submit'
		);
		$objSaveSubmit = new \FormSubmit($arr);
		$this->Template->saveSubmit = $objSaveSubmit->parse();
		$this->Template->submitLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'Save';
		
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
		
		// rewrite the module back link
		if(\Input::get('act') == 'edit')
		{
			// remove parameters from url
			$href = \Controller::getReferer();
			foreach(array('act','jumpto','mode') as $v)
			{
				$href = \PCT\CustomElements\Helper\Functions::removeFromUrl($v,$href);
			}
			// add the clear clipboard parameter
			$href = \PCT\CustomElements\Helper\Functions::addToUrl('clear_clipboard=1',$href);
			\Environment::set('httpReferer',$href);
			$this->Template->referer = \Environment::get('httpReferer');
		}
		
		//-- handle form actions
		if(\Input::post('FORM_SUBMIT') == $formName && \Input::post('table') == $objCC->getTable())
		{
			if($_POST[$objSaveSubmit->__get('name')])
			{
				// validate
				if(\Input::get('act') == 'edit' && \Input::get('id') != \Input::post('id'))
				{
					\Controller::reload();
				}
				
				$arrAttributes = $objCC->getCustomElement()->getAttributes();
				if(count($arrAttributes) < 1)
				{
					\Controller::reload();
				}
				
				$time = time();
				
				$arrSet = array('tstamp'=>$time);
				
				foreach($arrAttributes as $objAttribute)
				{
					$field = $objAttribute->get('alias');
					if(isset($_POST[$field]))
					{
						$value = \Input::post($field);
					}
					$arrSet[$field] = $value;
				}
				
				// update the record
				$objUpdate = \Database::getInstance()->prepare("UPDATE ".$objCC->getTable()." %s WHERE id=?")->set($arrSet)->execute(\Input::get('id'));
				
				// reload the page so changes take effect immediately
				\Controller::reload();
			}
		}
	}


	protected function isEditable()
	{
		// check if edit modes are active
		if(!in_array(\Input::get('act'),$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['allowedOperations']))
		{
			return false;
		}
		
		// check if element is allowed and FE User has rights
		
		return true;
	}
	
	
	
}