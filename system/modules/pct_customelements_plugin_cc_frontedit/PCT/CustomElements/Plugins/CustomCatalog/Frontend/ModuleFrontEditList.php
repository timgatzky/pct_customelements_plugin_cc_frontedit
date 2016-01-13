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
 * ModuleFrontEditList
 */
class ModuleFrontEditList extends \PCT\CustomElements\Plugins\CustomCatalog\Frontend\ModuleList
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_customcatalogfrontedit';	
	
	/**
	 * Display wildcard
	 */
	public function generate()
	{
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
		
		$objOrigTemplate = $this->Template;
		$this->Template = new \PCT\CustomCatalog\FrontEdit\FrontendTemplate($this->strTemplate);
		$this->Template->setData($objOrigTemplate->getData());
		$this->Template->raw = $objCC;
		foreach($objOrigTemplate as $key => $val) 
		{
            $this->Template->{$key} = $val;
        }
        
        // check if clipboard is active
		$arrClipboard = \Session::getInstance()->get('CLIPBOARD');
		if(count($arrClipboard[$objCC->getTable()]) > 0 || \Input::get('act') == 'select')
		{
			$this->Template->clipboard = true;
		}
		
		// check if select mode is active
		if(\Input::get('act') == 'select')
		{
			$this->Template->selectMode = true;
		}
		
		// form vars
		$formName = 'cc_frontedit_'.$this->id;
		
		// save button
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
		
		// delete button
		$this->Template->hasDelete = true;
		$arr = array
		(
			'id'	=> $formName.'_delete',
			'name'	=> 'delete',
			'strName'	=> 'delete',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_delete'] ?: 'delete',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_delete'] ?: 'Delete',
			'class' => 'submit',
			'onclick' => "return confirm('".$GLOBALS['TL_LANG']['MSC']['delAllConfirm']."');"
		);
		$objDeleteSubmit = new \FormSubmit($arr);
		$this->Template->deleteSubmit = $objDeleteSubmit->parse();
		$this->Template->deleteLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_delete'] ?: 'Delete';
		
		// copy button
		$this->Template->hasCopy = true;
		$arr = array
		(
			'id'	=> $formName.'_copy',
			'name'	=> 'copy',
			'strName'	=> 'copy',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_copy'] ?: 'copy',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_copy'] ?: 'Copy',
			'class' => 'submit',
		);
		$objCopySubmit = new \FormSubmit($arr);
		$this->Template->copySubmit = $objCopySubmit->parse();
		$this->Template->copyLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_copy'] ?: 'Copy';
		
		// edit/editall button
		$this->Template->hasEdit = true;
		$arr = array
		(
			'id'	=> $formName.'_edit',
			'name'	=> 'edit',
			'strName'	=> 'edit',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_edit'] ?: 'edit',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_edit'] ?: 'Edit',
			'class' => 'submit',
		);
		$objEditSubmit = new \FormSubmit($arr);
		$this->Template->editSubmit = $objEditSubmit->parse();
		$this->Template->editLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_edit'] ?: 'Edit';
		
		// override button
		$this->Template->hasOverride = true;
		$arr = array
		(
			'id'	=> $formName.'_override',
			'name'	=> 'override',
			'strName'	=> 'override',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_override'] ?: 'override',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_override'] ?: 'Override',
			'class' => 'submit',
		);
		$objEditSubmit = new \FormSubmit($arr);
		$this->Template->overrideSubmit = $objEditSubmit->parse();
		$this->Template->overrideLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_override'] ?: 'Override';
		
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
		if(\Input::post('FORM_SUBMIT') == $formName && \Input::post('table') == $objCC->getTable())
		{
			$arrIds = \Input::post('IDS');
			if(empty($arrIds))
			{
				\Controller::reload( \Controller::getReferer() );
			}
			
			$objUser = new \StdClass;
			$objUser->id = 1;
			
			// Create a datacontainer
			$objDC = new \PCT\CustomElements\Helper\DataContainerHelper($objCC->getTable());
			$objDC->User = $objUser;
			
			$objSession = \Session::getInstance();
			$arrSession = $objSession->getData();
			$arrSession['CURRENT']['IDS'] = $arrIds;
			$objSession->setData($arrSession);
			
			// DELETE selected
			if(isset($_POST[$objDeleteSubmit->__get('name')]))
			{
				foreach ($arrIds as $id)
				{
					$objDC->intId = $id;
					$objDC->delete(true);
				}
				\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
			}
			else if(isset($_POST[$objCopySubmit->__get('name')]))
			{
				// shop paste button
				if(in_array($objCC->get('list_mode'), array(4,5,'5.1')))
				{
					$url = \Environment::get('request');
					$objFunction = new \PCT\CustomElements\Helper\Functions;
					
					// redirect to paste
					\Controller::redirect( $objFunction->addToUrl('act=paste&mode=copyAll', \Environment::get('request')) );
				}
				
				
				#$objDC->copyAll();
			}
		}
		
		
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