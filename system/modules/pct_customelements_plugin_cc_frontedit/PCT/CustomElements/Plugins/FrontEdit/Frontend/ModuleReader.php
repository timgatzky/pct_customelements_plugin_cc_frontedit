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
	 * Flag if current user has group rights
	 * @param boolean
	 */
	protected $hasAccess = true;
	
	/**
	 * Display wildcard
	 */
	public function generate()
	{
		if (TL_MODE == 'BE' || !$this->customcatalog_edit_active)
		{
			return parent::generate();
		}
		
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($this->customcatalog);
		if(!$objCC)
		{
			return parent::generate();
		}
		
		// check plugin excludes
		if(in_array($objCC->get('pid'), $GLOBALS['PCT_CUSTOMELEMENTS']['PLUGINS']['cc_frontedit']['excludes']))
		{
			$this->hasAccess = false;
			return parent::generate();
		}
		
		// editing is not active
		if(!\Input::get('act') || !\Input::get('table'))
		{
			$this->hasAccess = false;
			return parent::generate();
		}

		// check permissions when entry is editable
		if( \Input::get('act') && \PCT\CustomCatalog\FrontEdit::isEditable() )
		{
			if( !\PCT\CustomCatalog\FrontEdit::checkPermissions( \Input::get('table'), \Input::get('id') ) )
			{
				$objTemplate = new \FrontendTemplate('cc_edit_nopermission');
				die_nicely('', $objTemplate->parse());
			}
		}
		
		// add assets
		\PCT\CustomCatalog\FrontEdit\Controller::addAssets();
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 * @return string
	 */
	protected function compile()
	{
		if(!$this->customcatalog_edit_active || !$this->hasAccess)
		{
			$this->Template->isEnabled = false;
			return parent::compile();
		}
		
		parent::compile();
		
		global $objPage;
		$objCC = $this->CustomCatalog;
		if(!$objCC->getOrigin())
		{
			$objCC->setOrigin($this);
		}
		
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
		$formName = $objCC->getTable().'_'.$this->id;
		
		//-- save button
		$this->Template->hasSave = true;
		$arr = array(
			'id'	=> $formName.'_save',
			'name'	=> 'save', 
			'strName' => 'save',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'save',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'Save',
			'class' => 'submit',
			'tableless' => true,
		);
		$objSaveSubmit = new \FormSubmit($arr);
		$objSaveSubmit->tableless = true;
		$this->Template->saveSubmit = $objSaveSubmit->parse();
		$this->Template->submitLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'Save';
		$this->saveSubmitName = $objSaveSubmit->__get('name');
		
		//-- save and close button
		$this->Template->hasSaveClose = true;
		$arr = array(
			'id'	=> $formName.'_saveNclose',
			'name'	=> 'saveNclose', 
			'strName' => 'saveNclose',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_saveNclose'] ?: 'save and go back',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_saveNclose'] ?: 'Save and go back',
			'class' => 'submit',
			'tableless' => true,
		);
		$objSaveNcloseSubmit = new \FormSubmit($arr);
		$this->Template->saveNcloseSubmit = $objSaveNcloseSubmit->parse();
		$this->Template->saveNcloseLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_saveNclose'] ?: 'Save and go back';
		$this->saveNcloseSubmitName = $objSaveNcloseSubmit->__get('name');
		
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
			$url = \Controller::getReferer();
			foreach(array('act','jumpto','mode') as $v)
			{
				$url = \PCT\CustomElements\Helper\Functions::removeFromUrl($v,$url);
			}
			// add the clear clipboard parameter
			$href = \PCT\CustomElements\Helper\Functions::addToUrl('clear_clipboard=1',$url);
			\Environment::set('httpReferer',$url);
			$this->Template->referer = \Environment::get('httpReferer');
		}
		
		//-- handle form actions
		if(\Input::post('FORM_SUBMIT') == $formName && \Input::post('table') == $objCC->getTable() && \Input::get('id') == \Input::post('id') )
		{
			if($_POST[$this->saveSubmitName] || isset($_POST[$this->saveNcloseSubmitName]) )
			{
				// get current database set list 
				$arrSet = \PCT\CustomCatalog\FrontEdit::getDatabaseSetlist($objCC->getTable());
				
				// hook here
				$arrSet = \PCT\CustomCatalog\FrontEdit\Hooks::getInstance()->storeDatabaseHook($arrSet,$objCC->getTable(),$this);
				
				$time = time();
				
				// update the record
				if(!empty($arrSet) && $arrSet !== null)
				{
					foreach($arrSet as $id => $set)
					{
						if(!isset($set['tstamp']))
						{
							$set['tstamp'] = $time;
						}
						
						$objUpdate = \Database::getInstance()->prepare("UPDATE ".$objCC->getTable()." %s WHERE id=?")->set($set)->execute($id);
					}
					
					// empty set list
					\PCT\CustomCatalog\FrontEdit::clearDatabaseSetlist($objCC->getTable());
					
					// go back to regular list view
					if(isset($_POST[$this->saveNcloseSubmitName]))
					{
						$url = \Controller::getReferer();
						foreach(array('act','jumpto','mode','table','do','rt','switchToEdit') as $v)
						{
							$url = \PCT\CustomElements\Helper\Functions::removeFromUrl($v,$url);
						}
						\Controller::redirect($url);
					}
					
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