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
namespace PCT\CustomCatalog\FrontEdit;


/**
 * Class file
 */
class TemplateAttribute extends \PCT\CustomElements\Core\TemplateAttribute
{
	/**
	 * Override the classes
	 * @param array
	 * @param object
	 * @return array
	 * @called from $GLOBALS['CUSTOMCATALOG_HOOKS'][getEntries]
	 */
	public function __override($arrEntries)
	{
		if(count($arrEntries) < 1 || !is_array($arrEntries))
		{
			return $arrEntries;
		}
		$arrReturn = array();
		foreach($arrEntries as $i => $objRowTemplate)
		{
			if(count($objRowTemplate->get('fields')) > 0)
	        {
		    	$arrFields = $objRowTemplate->get('fields');
		    	foreach($arrFields as $field => $objAttributeTemplate)
		        {
			       $_this = new self();
				   foreach($objAttributeTemplate as $key => $val)
				   {
					   $_this->{$key} = $val;
				   }
			       $arrFields[$field] = $_this;
		        }
			     $objRowTemplate->set('fields',$arrFields);
		    }
	        $arrReturn[$i] = $objRowTemplate;
		}
		return $arrReturn;
	}
	
		
	
	/**
	 * Generates the widget
	 * @return string
	 */
	public function widget()
	{
		if(strlen($this->widget) > 0)
		{
			return $this->widget;
		}
		
		$objAttribute = $this->attribute();
		if($objAttribute === null)
		{
			return '';
		}
		
		$objSession = \Session::getInstance();
		
		/* @var contao ModelModule */
		$objModel = $objAttribute->get('objCustomCatalog')->getModel();
		
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper;
		$objDC->value = $objAttribute->getValue();
		$objDC->table = $objAttribute->get('objCustomCatalog')->getTable();
		$objDC->field = $objAttribute->get('alias');
		$objDC->activeRecord = $objAttribute->getActiveRecord();
		if($objDC->activeRecord !== null)
		{
			$objDC->id = $objDC->activeRecord->id;
		}
		$objDC->objAttribute = $objAttribute;
		
		$arrSession = $objSession->getData();
		$arrIds = $arrSession['CURRENT']['IDS'];
		
		$arrFeSession = $objSession->get($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']) ?: array();
		
		// return when edit mode is not active
		if($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['showWidgetsOnlyInEditModes'] && ( !in_array(\Input::get('act'), array('edit','editAll','overrideAll','fe_editAll','fe_overrideAll')) || !$objModel->customcatalog_edit_active) )
		{
			return '';
		}
		
		// check if is has been selected
		if(in_array(\Input::get('act'), $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['multipleOperations']) && !in_array($objDC->id, $arrIds))
		{
			return '';
		}
		
		$strBuffer = '';
		
		// generate the attribute to access its own methods
		$tmp = $objAttribute->generate();
		$tmp->getData($objAttribute->getData());
		foreach($objAttribute as $key => $val)
		{
			$tmp->{$key} = $val;
		}
		$objAttribute = $tmp;
		unset($tmp);
		
		// get the attributes field definition
		$arrFieldDef = $objAttribute->getFieldDefinition();
		
		$strInputType = $arrFieldDef['inputType'] ?: $objAttribute->get('type');
		
		$strLabel = $objAttribute->get('title');
		if(is_array($objAttribute->getTranslatedLabel()) && count($objAttribute->getTranslatedLabel()))
		{
			$strLabel = $objAttribute->getTranslatedLabel()[0];
		}
		
		$blnSubmitted = false;
		$blnRewriteBackendJavascriptCalls = true;
		
		if($GLOBALS['BE_FFL'][$strInputType] && class_exists($GLOBALS['BE_FFL'][$strInputType]))
		{
			if(\Input::post('FORM_SUBMIT') == $objDC->table && isset($_POST[$objDC->field]))
			{
				$objDC->value = \Input::post($objDC->field);
				$blnSubmitted = true;
			}
			
			// multiple modes
			if(\Input::get('act') == 'fe_editAll' && isset($_POST[$objDC->field.'_'.$objDC->activeRecord->id]) )
			{
				$objDC->value = \Input::post($objDC->field.'_'.$objDC->activeRecord->id);
			}
			
			if(\Input::get('act') == 'fe_overrideAll' && !isset($_POST[$objDC->field]))
			{
				$objDC->value = null;
			}
			
			// create the widget
			$strClass = $GLOBALS['BE_FFL'][$arrFieldDef['inputType']];
			
			$arrAttributes = $strClass::getAttributesFromDca($arrFieldDef,$objDC->field,$objDC->value,$objDC->field,$objDC->table,$objDC);
		
			$objWidget = new $strClass($arrAttributes);
			$objWidget->__set('activeRecord',$objActiveRecord);
			
			// append record id to widget name in multiple modes
			if(\Input::get('act') == 'fe_editAll')
			{
				$objWidget->__set('name',$objWidget->__get('name').'_'.$objDC->activeRecord->id);
			}
			
			// trigger the attributes parseWidgetCallback
			if(method_exists($objAttribute,'parseWidgetCallback'))
			{
				// !TIMESTAMP attributes
				if($objAttribute->get('type') == 'timestamp' && in_array('datepicker', deserialize($objAttribute->get('options'))) )
				{
					$format = $objAttribute->get('date_format');
					if(!$format)
					{
						$format = $GLOBALS['TL_CONFIG'][$objAttribute->get('date_rgxp').'Format'];
					}
					
					$objDC->value = \System::parseDate($format,$objDC->value) ;
					$objWidget->__set('value', $varValue);
					
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
					
					if(strlen(strpos($strBuffer, 'value=""')) > 0 && $objDC->value != '')
					{
					   $strBuffer = str_replace('value=""', 'value="'.$objDC->value.'"',$strBuffer);
					}
				}
				
				// !IMAGE attributes
				else if($objAttribute->get('type') == 'image')
				{
					// if widget has been closed and new value has been set, use it
					if($arrFeSession[$objDC->table]['AJAX_REQUEST'][$objDC->field] === true)
					{
						$objDC->value = $arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field];
					}
					
					
					if(\Validator::isBinaryUuid($objDC->value))
					{
						$objDC->value = \StringUtil::binToUuid($objDC->value); #\FilesModel::findByUuid($objDC->value)->uuid;
						\Input::setPost($objDC->field,$objDC->value);
					}
					
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
					
					// value for database must be binary
					if($blnSubmitted && $objDC->value !== null && \Validator::isStringUuid($objDC->value))
					{
						$objDC->value = \StringUtil::uuidToBin($objDC->value);
					}
					
					// rewrite the preview images in file selections
					if($arrFeSession[$objDC->table]['AJAX_REQUEST'][$objDC->field] === true && isset($arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field]))
					{
						$newValue = \StringUtil::binToUuid($arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field]);
						$currValue = '';
						if($objDC->value)
						{
							$currValue = \StringUtil::binToUuid($objDC->value);
						}
						
						$objFile = \FilesModel::findByUuid($newValue)->path;
						
						if($objFile && $newValue != $currValue && strlen($currValue) > 0)
						{
							$newSRC = \Image::get(\FilesModel::findByUuid($newValue)->path,'80','60','crop');
							$data = json_encode(array('field'=>$objDC->field,'currValue'=>$currValue,'newValue'=>$newValue,'newSRC'=>$newSRC));
							$GLOBALS['TL_JQUERY'][] = '<script type="text/javascript">CC_FrontEdit.replaceSelectorImage('.$data.');</script>';
						}
					}
				}
				else
				{
					// !render
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
				}
			}
			else
			{	
				if(\Input::post('FORM_SUBMIT') == $objDC->table)
				{
					// validate the input
					$objWidget->validate();
					
					if($objWidget->hasErrors())
					{
						$objWidget->class = 'error';
						$this->submit = false;
					}
				}
				
				// default ajax field, like the pagetree
				if($arrFeSession[$objDC->table]['AJAX_REQUEST'][$objDC->field] === true && isset($arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field]))
				{
					$objWidget->__set('value',$arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field]);
				}
				
				$strBuffer = $objWidget->generateLabel();
				$strBuffer .= $objWidget->generateWithError();				
			}
		}
		// HOOK let attribute generate their own widgets
		else if(method_exists($objAttribute,'generateFrontendWidget'))
		{
			$strBuffer = $objAttribute->generateFrontendWidget($objDC);
		}
		
		// render child attributes
		if($objAttribute->hasChilds())
		{
			$arr = array();
			foreach($objAttribute->get('arrChildAttributes') as $k => $objChildWidget)
			{
				$field = $objChildWidget->__get('name');
				$value = $objDC->activeRecord->{$field};
				
				$dc = new \PCT\CustomElements\Helper\DataContainerHelper;
				$dc->id = $objDC->id;
				$dc->field = $field;
				$dc->value = $value;
				$dc->table = $objDC->table;
				
				$objChildWidget->__set('value',$value);
				
				if(\Input::post('FORM_SUBMIT') == $objDC->table && isset($_POST[$dc->field]) && $_POST[$dc->field] != $value)
				{
					$value = $_POST[$dc->field];
					
					\Input::setPost($dc->field,$value);
					
					$objChildWidget->validate();
					
					// add child to submit list
					if(!$objChildWidget->hasErrors())
					{
						$dc->value = $value;
						\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($value,$dc);
					}
				}
				
				$strChild = $objChildWidget->generateLabel() . $objChildWidget->generateWithError();
				
				// handle wizards in child attributes
				if(count($objChildWidget->fieldDef['wizard']) > 0)
				{
					foreach($objChildWidget->fieldDef['wizard'] as $callback)
					{
						$strChild .= \System::importStatic($callback[0])->{$callback[1]}($dc);
					}
				}
				
				// little javascript helper to place an inserttag value back in the input field
				if(strlen(strpos($dc->value, '{{')) > 0 && !\Input::post('FORM_SUBMIT'))
				{
					$data = json_encode(array('field'=>$field,'currValue'=>\Controller::replaceInsertTags($dc->value),'newValue'=>$dc->value));
					$GLOBALS['TL_JQUERY'][] = '<script>CC_FrontEdit.rereplaceInsertTags('.$data.');</script>';
				}
				
				$arr[] = $strChild;
			}
 			
 			$strBuffer .= implode('', $arr);
		}
		
		// trigger CEs parseWidget HOOk
		$strBufferFromHook = \PCT\CustomElements\Core\Hooks::callstatic('parseWidgetHook',array($objWidget,$objDC->field,$arrFieldDef,$objDC));
		if(strlen($strBufferFromHook))
		{
			$strBuffer = $strBufferFromHook;
		}	
					
		// rewrite the javascript calls to the Backend class
		if(strlen(strpos($strBuffer, 'Backend.')) > 0)
		{
			$errors = array
			(
				'be_user_not_logged_in' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['ERR']['be_user_not_logged_in'],
			);
			$preg = preg_match_all('/Backend.(.*?)\(([^\)]*)\)/',$strBuffer,$matches);
			if($preg)
			{
				$processed = array();
				foreach($matches[0] as $i => $func)
				{
					if(in_array($func, $processed))
					{
						continue;
					}
					
					$method = $matches[1][$i];
					$params = implode(',',array_map('trim',explode(',',$matches[2][$i])));
					$data = str_replace('"',"'",json_encode(array('method'=>$method,'func'=>$func,'params'=>$params,'errors'=>$errors)));
					
					// these methods require an active backend login
					if(in_array($method, $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['methodsRequireBackendLogin']))
					{
						if(FE_BE_USER_LOGGED_IN)
						{
							$data = $func;
						}
												
						$strBuffer = str_replace($func, "CC_FrontEdit.backend(".$data.")", $strBuffer);
					}
											
					$processed[] = $func;
				}
			}
		}
		
		// rewrite calls to the contao/file.php from file pickers
		if(strlen(strpos($strBuffer, 'contao/file.php')) > 0)
		{
			$strBuffer = str_replace('contao/file.php', PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/contao/file.php',$strBuffer);
		}
		
		// rewrite calls to the contao/page.php e.g. from page pickers
		if(strlen(strpos($strBuffer, 'contao/page.php')) > 0)
		{
			$strBuffer = str_replace('contao/page.php', PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/contao/page.php',$strBuffer);
		}
			
		// add to save list
		if(\Input::post('FORM_SUBMIT') == $objDC->table && (\Input::post('save') || \Input::post('saveNclose')) )
		{
			// set value from an ajax field and remove it from the session
			#if(isset($arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field]))
			#{
			#	$varValue = $arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field];
			#	unset($arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field]);
			#}
						
			if(\Input::get('act') == 'fe_overrideAll')
			{
				#$arrSession = \Session::getInstance()->getData();
				if(count($arrSession['CURRENT']['IDS']) > 0 && is_array($arrSession['CURRENT']['IDS']))
				{
					foreach($arrSession['CURRENT']['IDS'] as $id)
					{
						\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($objDC->value,$objDC);
					}
				}
			}
			else
			{
				\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($objDC->value,$objDC);
			}
			
			// update the session
			\Session::getInstance()->remove($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']);
		}
	
		// cache
		$this->widget = $strBuffer;
		
		return $strBuffer;

	}
}