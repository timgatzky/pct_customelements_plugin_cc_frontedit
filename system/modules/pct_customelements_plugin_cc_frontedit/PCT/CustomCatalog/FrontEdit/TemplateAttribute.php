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
		
		// return when edit mode is not active
		if($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['showWidgetsOnlyInEditMode'] && !in_array(\Input::get('act'), array('edit','editAll','overrideAll')))
		{
			return '';
		}
		
		$objAttribute = $this->attribute();
		if($objAttribute === null)
		{
			return '';
		}
		
		$strBuffer = '';
		
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper;
		$objDC->value = $objAttribute->getValue();
		$objDC->table = $objAttribute->get('objCustomCatalog')->getTable();
		$objDC->id = \Input::get('id');
		$objDC->field = $objAttribute->get('alias');
		$objDC->activeRecord = $objAttribute->getActiveRecord();
		
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
			// create the widget
			$strClass = $GLOBALS['BE_FFL'][$arrFieldDef['inputType']];
			
			$arrAttributes = $strClass::getAttributesFromDca($arrFieldDef,$objDC->field,$objDC->value,$objDC->field,$objDC->table,$objDC);
		
			$objWidget = new $strClass($arrAttributes);
			$objWidget->__set('activeRecord',$objActiveRecord);
			
			// trigger the attributes parseWidgetCallback
			if(method_exists($objAttribute,'parseWidgetCallback'))
			{
				$strBuffer = $this->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
			}
			else
			{	
				$strBuffer = $objWidget->generateLabel();
				$strBuffer .= $objWidget->generateWithError();
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