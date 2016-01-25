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
		
		$arrSession = \Session::getInstance()->getData();
		$arrIds = $arrSession['CURRENT']['IDS'];
		
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
		
		if($GLOBALS['BE_FFL'][$strInputType] && class_exists($GLOBALS['BE_FFL'][$strInputType]))
		{
			if($_POST[$objDC->field])
			{
				$objDC->value = \Input::post($objDC->field);
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
				$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
			}
			else
			{	
				$blnSubmit = true;
				if(\Input::post('FORM_SUBMIT') == $objDC->table)
				{
					// validate the input
					$objWidget->validate();
					
					if($objWidget->hasErrors())
					{
						$objWidget->class = 'error';
						$blnSubmit = false;
					}
				}
				
				$strBuffer = $objWidget->generateLabel();
				$strBuffer .= $objWidget->generateWithError();
				
				// add to save list
				if($blnSubmit)
				{
					if(\Input::get('act') == 'fe_overrideAll')
					{
						$arrSession = \Session::getInstance()->getData();
						
						if(count($arrSession['CURRENT']['IDS']) > 0 && is_array($arrSession['CURRENT']['IDS']))
						{
							foreach($arrSession['CURRENT']['IDS'] as $id)
							{
								\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($objWidget->__get('value'),$objDC->table,$id,$objDC->field);
							}
						}
					}
					else
					{
						\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($objWidget->__get('value'),$objDC->table,$objDC->id,$objDC->field);
					}
					
				}
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
		}
		// HOOK let attribute generate their own widgets
		else if(method_exists($objAttribute,'generateFrontendWidget'))
		{
			$strBuffer = $objAttribute->generateFrontendWidget($objDC);
		}
		
		// trigger CEs parseWidget HOOk
		$strBufferFromHook = \PCT\CustomElements\Core\Hooks::callstatic('parseWidgetHook',array($objWidget,$objDC->field,$arrFieldDef,$objDC));
		if(strlen($strBufferFromHook))
		{
			$strBuffer = $strBufferFromHook;
		}		
		
		// cache
		$this->widget = $strBuffer;
		
		return $strBuffer;

	}
}