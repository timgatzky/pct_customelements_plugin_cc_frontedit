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
namespace PCT\CustomElements\Plugins\FrontEdit\Frontend;

/**
 * Class file
 * ModuleReader
 */
class ModuleReader extends \PCT\CustomElements\Plugins\CustomCatalog\Frontend\ModuleReader
{
	/**
	 * Display wildcard
	 */
	public function generate()
	{
		if (TL_MODE == 'BE' || !$this->customcatalog_edit_active)
		{
			return parent::generate();
		}
		
		$GLOBALS['TL_JAVASCRIPT'][] = PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/js/CC_FrontEdit.js';
		
		global $objPage;
		if(!$objPage->hasJQuery)
		{
			$GLOBALS['TL_JAVASCRIPT'][] = '//code.jquery.com/jquery-' . $GLOBALS['TL_ASSETS']['JQUERY'] . '.min.js';
		}
		
		// add backend assets
		\PCT\CustomCatalog\FrontEdit\Helper::addBackendAssets();
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 * @return string
	 */
	protected function compile()
	{
		if(!$this->customcatalog_edit_active)
		{
			$this->Template->isEnabled = false;
			return parent::compile();
		}
		
		parent::compile();
		
		global $objPage;
		$objCC = $this->CustomCatalog;
		
		$objOrigTemplate = $this->Template;
		$this->Template = new \PCT\CustomCatalog\FrontEdit\FrontendTemplate($this->strTemplate);
		$this->Template->setData($objOrigTemplate->getData());
		$this->Template->raw = $objCC;
		foreach($objOrigTemplate as $key => $val) 
		{
            $this->Template->{$key} = $val;
        }
        
        $this->Template->isEnabled = false;

        if( in_array(\Input::get('act'), array('edit','editAll','overrideAll')) )
		{
			$this->Template->editMode = true;
			$this->Template->clipboard = true;
		}
		
		// form vars
		$formName = $objCC->getTable();
		
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
			'mod'	=> $this->id,
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
				
				// get current database set list 
				$arrSet = \PCT\CustomCatalog\FrontEdit::getDatabaseSetlist($objCC->getTable());
				
				// hook here
				$arrSet = \PCT\CustomCatalog\FrontEdit\Hooks::getInstance()->storeDatabaseHook($arrSet,$objCC->getTable(),$this);
				
				// update the record
				if(!empty($arrSet) && $arrSet !== null)
				{
					foreach($arrSet as $id => $set)
					{
						$objUpdate = \Database::getInstance()->prepare("UPDATE ".$objCC->getTable()." %s WHERE id=?")->set($set)->execute($id);
					}
					// empty set list
					\PCT\CustomCatalog\FrontEdit::clearDatabaseSetlist($objCC->getTable());
					
					// reload the page so changes take effect immediately
					\Controller::reload();
				}
			}
		}
	}
	
	
	/**
	 * 
	 */
	protected function isEditable()
	{
		return true;
	}
	
}