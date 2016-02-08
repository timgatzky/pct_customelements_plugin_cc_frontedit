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
 * ModuleList
 */
class ModuleList extends \PCT\CustomElements\Plugins\CustomCatalog\Frontend\ModuleList
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
		if(TL_MODE == 'BE' || !$this->customcatalog_edit_active)
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
		
		// user must be logged in
		if(!FE_USER_LOGGED_IN && !$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'])
		{
			$this->hasAccess = false;
		}
		
		// check groups
		else if(FE_USER_LOGGED_IN && !$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['allowAll'])
		{
			$objUser = new \PCT\Contao\FrontendUser( \FrontendUser::getInstance() , array('customcatalog_edit_active' => 1));
			if(!$objUser->hasGroupAccess(deserialize($this->reg_groups)))
			{
				$this->hasAccess = false;
			}
		}
		
		// include scripts and backend stuff
		if($this->hasAccess)
		{
			$GLOBALS['TL_JQUERY'][] = PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/js/CC_FrontEdit.js';
			
			global $objPage;
			if(!$objPage->hasJQuery)
			{
				$GLOBALS['TL_JAVASCRIPT'][] = '//code.jquery.com/jquery-' . $GLOBALS['TL_ASSETS']['JQUERY'] . '.min.js';
			}
			
			// add backend assets
			\PCT\CustomCatalog\FrontEdit\Controller::addBackendAssets();
		}
		
		return parent::generate();
	}


	/**
	 * Generate the module
	 * @return string
	 */
	protected function compile()
	{
		// check general permissions 
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
		$this->Template->referer = \Controller::getReferer();
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        $this->Template->goBack = \PCT\CustomCatalog\FrontEdit\FrontendTemplate::backButton(true);
        
        $this->Template->isEnabled = true;
		$this->Template->showHeaderButtons = true;
       
        $arrListOperations = deserialize($objCC->get('list_operations'));
        
        // check if clipboard is active
		$arrClipboard = $this->Session->get('CLIPBOARD');
		if(count($arrClipboard[$objCC->getTable()]) > 0 || \Input::get('act') == 'select' || in_array(\Input::get('act'), array('fe_editAll','fe_overrideAll')))
		{
			$this->Template->clipboard = true;
		}
		
		// check if select mode is active
		if(\Input::get('act') == 'select')
		{
			$this->Template->selectMode = true;
		}
		else if ( in_array(\Input::get('act'), array('fe_editAll','fe_overrideAll')) )
		{
			$this->Template->editMode = true;
			$this->Template->isMultiple = true;
			$this->Template->saveOnly = true;
		}
		else if( \Input::get('act') == 'edit' && \Input::get('id') > 0)
		{
			$this->Template->editMode = true;
			$this->Template->saveOnly = true;
			$this->Template->singleEditMode = true;
			$this->Template->showHeaderButtons = false;
		}
		\PC::debug($this->Template->singleEditMode);
		
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
		$this->Template->saveSubmit = $objSaveSubmit->parse();
		$this->Template->saveLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_save'] ?: 'Save';
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
		
		//-- delete button
		$this->Template->hasDelete = true;
		$arr = array
		(
			'id'	=> $formName.'_delete',
			'name'	=> 'delete',
			'strName'	=> 'delete',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_delete'] ?: 'delete',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_delete'] ?: 'Delete',
			'class' => 'submit',
			'tableless' => true,
			'onclick' => "return confirm('".$GLOBALS['TL_LANG']['MSC']['delAllConfirm']."');"
		);
		$objDeleteSubmit = new \FormSubmit($arr);
		$this->Template->deleteSubmit = $objDeleteSubmit->parse();
		$this->Template->deleteLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_delete'] ?: 'Delete';
		$this->deleteSubmitName = $objDeleteSubmit->__get('name');
		
		//-- copy button
		$this->Template->hasCopy = true;
		$arr = array
		(
			'id'	=> $formName.'_copy',
			'name'	=> 'copy',
			'strName'	=> 'copy',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_copy'] ?: 'copy',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_copy'] ?: 'Copy',
			'class' => 'submit',
			'tableless' => true,
		);
		$objCopySubmit = new \FormSubmit($arr);
		$this->Template->copySubmit = $objCopySubmit->parse();
		$this->Template->copyLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_copy'] ?: 'Copy';
		$this->copySubmitName = $objCopySubmit->__get('name');
		
		//-- edit/editall button
		$this->Template->hasEdit = true;
		$arr = array
		(
			'id'	=> $formName.'_edit',
			'name'	=> 'edit',
			'strName'	=> 'edit',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_edit'] ?: 'edit',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_edit'] ?: 'Edit',
			'class' => 'submit',
			'tableless' => true,
		);
		$objEditSubmit = new \FormSubmit($arr);
		$this->Template->editSubmit = $objEditSubmit->parse();
		$this->Template->editLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_edit'] ?: 'Edit';
		$this->editSubmitName = $objEditSubmit->__get('name');
		
		
		//-- override button
		$this->Template->hasOverride = true;
		$arr = array
		(
			'id'	=> $formName.'_override',
			'name'	=> 'override',
			'strName'	=> 'override',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_override'] ?: 'override',
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_override'] ?: 'Override',
			'class' => 'submit',
			'tableless' => true,
		);
		$objOverrideSubmit = new \FormSubmit($arr);
		$this->Template->overrideSubmit = $objOverrideSubmit->parse();
		$this->Template->overrideLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_override'] ?: 'Override';
		$this->overrideSubmitName = $objOverrideSubmit->__get('name');
		
		//-- cut button
		if(in_array($objCC->get('list_mode'),array(4,5,'5.1')) && in_array('cut', $arrListOperations))
		{
			$this->Template->hasCut = true;
			$arr = array
			(
				'id'	=> $formName.'_cut',
				'name'	=> 'cut',
				'strName'	=> 'cut',
				'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_cut'] ?: 'cut',
				'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_cut'] ?: 'Cut',
				'class' => 'submit',
				'tableless' => true,
			);
			$objCutSubmit = new \FormSubmit($arr);
			$this->Template->cutSubmit = $objCutSubmit->parse();
			$this->Template->cutLabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_cut'] ?: 'Cut';
			$this->cutSubmitName = $objCutSubmit->__get('name');
		}
		
		//-- select all checkbox
		$this->Template->selectAll = '<label for="select_trigger_'.$this->id.'" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label>';
		$this->Template->selectAll .= '<input data-module="'.$this->id.'" id="select_trigger_'.$this->id.'" class="tl_select_trigger checkbox" type="checkbox" onclick="CC_FrontEdit.toggleCheckboxes(this)">';
		
		//-- paste first button
		$this->Template->pasteFirst = '<a href="'.\PCT\CustomElements\Helper\Functions::addToUrl('act='.$arrClipboard[$objCC->getTable()]['mode'].'&mode=2&pid=0', \Environment::get('request')).'">'.\Image::getHtml('pasteinto.gif','').'</a>';
		// these modes suppot paste first
		if(!in_array(\Input::get('act'),array('paste')) && !in_array(\Input::get('mode'),array('copyAll','cutAll')))
		{
			$this->Template->pasteFirst = '';
		}
		
		// hide the save buttons in select mode
		if(\Input::get('act') == 'select')
		{
			$this->Template->hasSave = false;
		}
		
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
		$this->Template->formClass = 'cc_frontedit_form';
		$this->Template->hidden = $strHidden;
		
		//-- handle form actions
		if(\Input::post('FORM_SUBMIT') == $formName && \Input::post('table') == $objCC->getTable())
		{
			$objUser = new \StdClass;
			$objUser->id = 1;
			
			// Create a datacontainer
			$objDC = new \PCT\CustomElements\Helper\DataContainerHelper($objCC->getTable());
			$objDC->User = $objUser;
			
			$arrSession = $this->Session->getData();
			
			$arrIds = $arrSession['CURRENT']['IDS'];
			if($_POST['IDS'])
			{
				$arrIds = $_POST['IDS'];
				$arrSession['CURRENT']['IDS'] = $arrIds;
				$this->Session->setData($arrSession);
			}
			
			// !DELETE selected
			if(isset($_POST[$this->deleteSubmitName]))
			{
				foreach ($arrIds as $id)
				{
					$objDC->intId = $id;
					$objDC->delete(true);
				}
				\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
			}
			// !save
			else if(isset($_POST[$this->saveSubmitName]) || isset($_POST[$this->saveNcloseSubmitName]))
			{
				// get current database set list 
				$arrSet = \PCT\CustomCatalog\FrontEdit::getDatabaseSetlist($objCC->getTable());
				
				// hook here
				$arrSet = \PCT\CustomCatalog\FrontEdit\Hooks::getInstance()->storeDatabaseHook($arrSet,$objCC->getTable(),$this);
				
				$time = time();
				
				// update the records
				if(!empty($arrSet) && $arrSet !== null)
				{
					foreach($arrSet as $id => $set)
					{
						if(!in_array($id,$arrIds))
						{
							continue;
						}
						
						if(!isset($set['tstamp']))
						{
							$set['tstamp'] = $time;
						}
						
						$objUpdate = \Database::getInstance()->prepare("UPDATE ".$objCC->getTable()." %s WHERE id=?")->set( $set )->execute($id);
					}
					
					// empty set list
					\PCT\CustomCatalog\FrontEdit::clearDatabaseSetlist($objCC->getTable());
					
					// go back to regular list view
					if(isset($_POST[$this->saveNcloseSubmitName]))
					{
						\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
					}
					
					// reload the page so changes take effect immediately
					\Controller::reload();
				}
			}
			// !copyAll submitted 
			else if(isset($_POST[$this->copySubmitName]))
			{
				// redirect to paste
				\Controller::redirect( \PCT\CustomElements\Helper\Functions::addToUrl('act=paste&mode=copyAll', \Environment::get('request')) );
			}
			// !cutAll submitted, show paste button
			else if(isset($_POST[$this->cutSubmitName]))
			{
				// redirect to paste
				\Controller::redirect( \PCT\CustomElements\Helper\Functions::addToUrl('act=paste&mode=cutAll', \Environment::get('request')) );
			}
			// !editAll submitted
			else if( isset($_POST[$this->editSubmitName]))
			{
				// redirect to act=editAll
				\Controller::redirect( \PCT\CustomElements\Helper\Functions::addToUrl('act=fe_editAll', \Environment::get('request')) );
			}
			// !overrideAll submitted
			else if( isset($_POST[$this->overrideSubmitName]))
			{
				// redirect to act=editAll
				\Controller::redirect( \PCT\CustomElements\Helper\Functions::addToUrl('act=fe_overrideAll', \Environment::get('request')) );
			}
			else
			{
				\Controller::reload( \Controller::getReferer() );
			}
		}
		
		
	}

	
}