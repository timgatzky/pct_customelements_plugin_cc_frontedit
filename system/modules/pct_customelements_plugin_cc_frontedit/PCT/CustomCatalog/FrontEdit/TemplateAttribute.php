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
	 * Generates the formular widget
	 */
	public function widget()
	{
		$objAttribute = $this->attribute();
		if($objAttribute === null)
		{
			return $strBuffer;
		}
		
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper;
		$objDC->table = $objAttribute->get('objCustomCatalog')->getTable();
		$objDC->id = \Input::get('id');
		$objDC->field = $objAttribute->get('alias');
		$objDC->activeRecord = $objAttribute->getActiveRecord();
		
		if(!\PCT\CustomCatalog\FrontEdit::isEditable($objDC->table,$objDC->id))
		{
			return $strBuffer;
		}
		
		$objFrontEdit = new \PCT\CustomCatalog\FrontEdit();
		
		$objActiveRecord = $objAttribute->getActiveRecord();
		
		$objOrigin = \PCT\CustomElements\Core\Origin::getInstance();
		$objOrigin->set('intPid',$objActiveRecord->id);
		$objOrigin->set('strTable',$objDC->table);
		
		$objAttribute->setOrigin($objOrigin);
		$objAttribute->setValue($this->value());
		
		// generate the widget. Calls the CE parseWidget Hook
		$strWidget = $objAttribute->generateWidget($objDC);
		
		// append output with the editing widget
		$strBuffer .= '<div class="frontedit_widget widget">'.$strWidget.'</div>';
		
		return $strBuffer;

	}
}