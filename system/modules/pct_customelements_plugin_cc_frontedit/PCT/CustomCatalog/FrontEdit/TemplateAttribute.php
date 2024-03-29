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

use Contao\Combiner;
use Contao\Config;
use Contao\Controller;
use Contao\Input;
use Contao\Session;
use Contao\Image;
use Contao\System;
use Contao\FilesModel;
use Contao\Date;
use Contao\Dbafs;
use Contao\StringUtil;
use Contao\Validator;
use Contao\Database;
use Contao\Environment;
use Contao\File;
use Contao\Folder;

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
		if( empty($arrEntries) || !is_array($arrEntries))
		{
			return $arrEntries;
		}
		$arrReturn = array();
		$arrProcessed = array();
		$arrFields = array();
		foreach($arrEntries as $i => $objRowTemplate)
		{
			if(!empty($objRowTemplate->get('fields')))
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
			    $arrProcessed[] = $field;
				
			}

			// set the field array
			if( !empty($arrFields) )
			{
				$objRowTemplate->set('field', $arrFields);
			}
			
			$arrReturn[$i] = $objRowTemplate;
		}
		return $arrReturn;
	}
	
		
	
	/**
	 * Generates the widget
	 * @param string	Custom template name
	 * @return string
	 */
	public function widget($strTemplate = '')
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
		
		// return info text when attribute is supposed to be not editable
		if($objAttribute->get('notEditable'))
		{
			return sprintf($GLOBALS['TL_LANG']['XPT']['cc_edit_attribute_not_editable'],'id:'.$objAttribute->get('id'));
		}
		
		// store potential child widgets
		$this->childWidgets = array();
		
		#$objSession = Session::getInstance();
		$objSession = System::getContainer()->get('session');


		/* @var contao ModelModule */
		$objModule = $objAttribute->get('objCustomCatalog')->getModule();
		
		$objDC = new \PCT\CustomElements\Plugins\FrontEdit\Helper\DataContainerHelper;
		$objDC->value = $objAttribute->getValue();
		$objDC->table = $objAttribute->get('objCustomCatalog')->getTable();
		$objDC->field = $objAttribute->get('alias');
		$objDC->activeRecord = $objAttribute->getActiveRecord();
		if($objDC->activeRecord !== null)
		{
			$objDC->id = $objDC->activeRecord->id;
			
			// use the current record value
			if($objDC->value != $objDC->value = $objDC->activeRecord->{$objDC->field})
			{
				$objDC->value = $objDC->activeRecord->{$objDC->field};
			}
		}
		$objDC->objAttribute = $objAttribute;
		$objDC->formSubmit = $objDC->table.'_'.Input::post('mod');
		$objDC->isAjax = false;
		
		$arrSession = $objSession->get('contao_frontend');
		$arrIds = $arrSession['CURRENT']['IDS'];
		
		$arrFeSession = $objSession->get($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']) ?: array();

		// return when edit mode is not active
		if($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['showWidgetsOnlyInEditModes'] && ( !in_array(Input::get('act'), array('edit','editAll','overrideAll','fe_editAll','fe_overrideAll')) || !$objModule->customcatalog_edit_active) )
		{
			return '';
		}

		// check if is has been selected
		if(in_array(Input::get('act'), $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['multipleOperations']) && !in_array($objDC->id, $arrIds))
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
		unset($key);
		unset($val);
		
		// get the attributes field definition
		$arrFieldDef = $objAttribute->getFieldDefinition();
		
		// mark attribute as sortable
		$this->sortable = $arrFieldDef['sortable'] ? true : false;
		$strOrderField = $arrFieldDef['eval']['orderSRC'] ? $arrFieldDef['eval']['orderSRC'] : 'orderSRC';
		
		// mark attribute as multiple
		$this->multiple = $arrFieldDef['eval']['multiple'] ? true : false;
		
		$strInputType = $arrFieldDef['inputType'] ?: $objAttribute->get('type');
		
		$strLabel = $objAttribute->get('title');
		if(is_array($objAttribute->getTranslatedLabel()) && count($objAttribute->getTranslatedLabel()))
		{
			$strLabel = $objAttribute->getTranslatedLabel()[0];
		}
		
		// mark attribute as ajax related field
		if( in_array($objAttribute->get('type'),array('pagetree','files','gallery','image','tags')) )
		{
			$this->isAjaxField = true;
		}

		$blnSubmitted = false;
		if(Input::post('FORM_SUBMIT') == $objDC->formSubmit && isset($_POST[$objDC->field]))
		{
			$objDC->value = Input::post($objDC->field);
			$blnSubmitted = true;
		}
		
		// multiple modes
		else if(Input::post('FORM_SUBMIT') == $objDC->formSubmit && Input::get('act') == 'fe_editAll' && isset($_POST[$objDC->field.'_'.$objDC->activeRecord->id]) )
		{
			$objDC->value = Input::post($objDC->field.'_'.$objDC->activeRecord->id);
			$blnSubmitted = true;
		}
		
		// reset current values in overrideAll mode to start from zero
		#if(Input::get('act') == 'fe_overrideAll' && !isset($_POST[$objDC->field]))
		#{
		#	$objDC->value = null;
		#}

		// ajax requests, store value in the session and reload page
		if(strlen(Input::post('action')) > 0 && (Input::post('name') == $objDC->field || Input::post('field') == $objDC->field) )
		{
			$objDC->value = Input::post('value');
			// !ajax IMAGE
			if($objAttribute->get('type') == 'image' && Input::post('value')) 
			{
				$objFile = Dbafs::addResource( \urldecode(Input::post('value')) );
				if ($objFile) 
				{
					$objDC->value = $objFile->uuid;
				}
			}
			// !ajax FILE(s), GALLERY
			else if (in_array($objAttribute->get('type'), array('files', 'gallery')) && Input::post('value')) {
				$objDC->value = Input::post('value');

				if ($this->multiple) {
					$objDC->value = StringUtil::trimsplit('\t', Input::post('value', true));

					foreach ($objDC->value as $v) 
					{
						$v = \urldecode($v);
						
						if ( Validator::isUuid($v) || Validator::isStringUuid($v) )
						{
							$v = FilesModel::findByUuid( $v )->path;
						}

						$objFile = Dbafs::addResource($v);
						if ($objFile) 
						{
							$values[] = StringUtil::binToUuid($objFile->uuid);
						}
					}
					$objDC->value = implode(',', $values);
				}
			}
			// !ajax TAGS, PAGETREE
			else if (in_array($objAttribute->get('type'),array('tags','pagetree')) && Input::post('value'))
		   	{
			   	if($this->multiple)
			   	{
				   	 $objDC->value = implode(',',StringUtil::trimsplit('\t',Input::post('value',true)));
			   	}
		   	}
		   
		   	$arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field] = $objDC->value;		
			$objSession->set($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName'],$arrFeSession);
			
			$objDC->isAjax = true;
			$objDC->ajaxValue = $objDC->value;
			$this->ajaxValue = $objDC->value;

			// reload the page to avoid wrong javascript
			if(!$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['simulateAjaxReloads'])
			{
				Controller::reload();
			}
		}
		
		// retrieve from session
		if(isset($arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field]))
		{
			$objDC->value = $arrFeSession[$objDC->table]['CURRENT']['VALUES'][$objDC->field];
			
			// convert paths to uuid when not done before
			if( in_array($objAttribute->get('type'), array('files','gallery')) && !$this->multiple && !empty($objDC->value))
			{
				$objDC->value = FilesModel::findByPath($objDC->value)->uuid;
			}
		}
		
		// trigger load callback
		if(!Input::post('FORM_SUBMIT'))
		{
			if(is_array($arrFieldDef['load_callback']))
			{
				$objDC->objAttribute = $objAttribute;
				foreach($arrFieldDef['load_callback'] as $callback)
				{
					if (is_array($callback))
					{
					   $objDC->value = System::importStatic($callback[0])->{$callback[1]}($objDC->value,$objDC,$this);	
					}
					else if(is_callable($callback))
					{
					   $objDC->value = $callback($objDC->value,$objDC,$this);
					}
				}
			}
		}
		
		if($GLOBALS['BE_FFL'][$strInputType] && class_exists($GLOBALS['BE_FFL'][$strInputType]))
		{
			// create the widget
			$strClass = $GLOBALS['BE_FFL'][$arrFieldDef['inputType']];
		
			$arrAttributes = $strClass::getAttributesFromDca($arrFieldDef,$objDC->field,$objDC->value,$objDC->field,$objDC->table,$objDC);
			
			$objWidget = new $strClass($arrAttributes);
			$objWidget->label = $strLabel;
			
			// set a custom template
			if(strlen($strTemplate) > 0)
			{
				$objWidget->__set('customTpl',$strTemplate);
			}
				
			// any validator need the current field value in the psydo post data
			$objDC->value = StringUtil::deserialize($objDC->value);
			
			// append record id to widget name in multiple modes
			if(Input::get('act') == 'fe_editAll')
			{
				Input::setPost($objDC->field.'_'.$objDC->activeRecord->id,$objDC->value);
				$objWidget->__set('name',$objWidget->__get('name').'_'.$objDC->activeRecord->id);
			}
			else if($objDC->isAjax === false)
			{
				Input::setPost($objDC->field,$objDC->value);
			}
			
			// trigger the attributes parseWidgetCallback
			if( \method_exists($objAttribute,'parseWidgetCallback') )
			{
				// !TIMESTAMP attributes
				if($objAttribute->get('type') == 'timestamp' && in_array('datepicker', StringUtil::deserialize($objAttribute->get('options'))) )
				{
					$rgxp = $arrFieldDef['eval']['rgxp'];
					$format = $GLOBALS['TL_CONFIG'][$rgxp.'Format'];
					
					if(!$blnSubmitted)
					{
						$objDC->value = Date::parse($format,$objDC->value);
						Input::setPost($objDC->field,$objDC->value);
					}
					$objWidget->__set('value', $objDC->value);
					
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
					
					if(strlen(strpos($strBuffer, 'value=""')) > 0 && $objDC->value != '')
					{
					   $strBuffer = str_replace('value=""', 'value="'.$objDC->value.'"',$strBuffer);
					}
					
					if(version_compare(VERSION, '4','>='))
					{
						$search = array('$("ctrl_'.$objDC->field.'")','$("toggle_'.$objDC->field.'")','//');
						$replace = array('$$("#ctrl_'.$objDC->field.'")[0]','$$("#toggle_'.$objDC->field.'")[0]','/');
						$strBuffer = str_replace($search,$replace, $strBuffer);
					}
				}
				// !IMAGE attributes
				else if($objAttribute->get('type') == 'image')
				{
					if( !$blnSubmitted && Validator::isBinaryUuid(Input::postRaw($objDC->field)) )
					{
						Input::setPost($objDC->field,null);
					}

					$objWidget->currentRecord = $objDC->id;
					
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
					
					// remove invalid spans from image title
					$strBuffer = str_replace(array('<span class="tl_gray">','</span>'),'',StringUtil::decodeEntities($strBuffer));
					
					// value for database must be binary
					if($blnSubmitted && Validator::isStringUuid($objDC->value))
					{
						$objDC->value = StringUtil::uuidToBin($objDC->value);
					}
					// value must be null
					else if($blnSubmitted && is_string($objDC->value) && strlen($objDC->value) < 1)
					{
						$objDC->value = null;
					}
					
					// rewrite the preview images in file selections
					#if( isset($objDC->ajaxValue) && $objDC->value =! $objDC->ajaxValue )
					#{
					#	$newValue = StringUtil::binToUuid($objDC->ajaxValue);
					#	$currValue = StringUtil::binToUuid($objDC->value);
					#	
					#	$strFile = FilesModel::findByUuid($newValue)->path;
#					#	if($strFile && $newValue != $currValue && strlen($currValue) > 0)
					#	if($strFile)
					#	{
					#		$newSRC = Image::get($strFile,'80','60','crop');
					#		$this->ajaxReplaceImage = Image::getHtml($strFile,'80','60','crop');
					#		$data = json_encode(array('field'=>$objDC->field,'currValue'=>$currValue,'newValue'=>$newValue,'newSRC'=>$newSRC));
					#	#	$GLOBALS['TL_JQUERY'][] = '<script type="text/javascript">CC_FrontEdit.replaceSelectorImage('.$data.');</script>';
					#	}
					#}
				}
				// !FILE(s), GALLERY attributes
				else if( in_array($objAttribute->get('type'),array('files','gallery')) )
				{
					if(!$this->multiple)
					{
						if(Validator::isBinaryUuid($objDC->value))
						{
							$objDC->value = StringUtil::binToUuid($objDC->value);
							Input::setPost($objDC->field,$objDC->value);
						}
						else if(FilesModel::findByPath($objDC->value) !== null)
						{
							$objDC->value = StringUtil::binToUuid( FilesModel::findByPath($objDC->value)->uuid );
							Input::setPost($objDC->field,$objDC->value);
						}
					}
					else
					{
						// coming from ajax, convert paths to binary
						if($objDC->ajaxValue)
						{
						   if(!is_array($objDC->value))
						   {
							   $objDC->value = array_filter(explode(',', $objDC->value));
						   }
						   $values = array();
						   foreach($objDC->value as $v)
						   {
							   if(Validator::isStringUuid($v))
							   {
								   $values[] = $v;
								   continue;
							   }
							   
							   $objFile = FilesModel::findByPath($v);
							   if($objFile)
							   {
								   $values[] = StringUtil::binToUuid($objFile->uuid);
								   continue;
							   }
							   
							   $objFile = new File(TL_ROOT.'/'.$v,true);
							   if($objFile !== null)
							   {
								   $values[] = StringUtil::binToUuid($objFile->uuid);
								   continue;
							   }
							}
							$objDC->value = implode(',',$values);
							Input::setPost($objDC->field,$objDC->value);
							unset($values);
						}
						else if(!$blnSubmitted)
						{
							// regular selected via backend, convert binary to uuid
							$arrValues = StringUtil::deserialize($objDC->value);
							if(!is_array($arrValues))
							{
								$arrValues = explode(',',$arrValues); 
							}
							$_value = array_map('StringUtil::binToUuid',array_filter($arrValues));
							Input::setPost($objDC->field,implode(',',$_value));
							unset($_value);
						}
					}
					
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
					
					// reorder
					if($blnSubmitted && $this->sortable && isset($_POST[$strOrderField.'_'.$objDC->field]) && $_POST[$strOrderField.'_'.$objDC->field] != $_POST[$objDC->field])
					{
						$newOrder = Input::post($strOrderField.'_'.$objDC->field);
						$objDC->value = Input::post($strOrderField.'_'.$objDC->field);
					}
						
					// values for database must be binary
					if($blnSubmitted && $this->multiple)
					{
						if(!is_array($objDC->value)) 
						{
							$objDC->value = explode(',', $objDC->value);
						}
						$objDC->value = array_map('StringUtil::uuidToBin',array_filter($objDC->value));
					}
					else if($blnSubmitted && Validator::isStringUuid($objDC->value))
					{
						$objDC->value = StringUtil::uuidToBin($objDC->value);
					}
					// value must be null
					else if($blnSubmitted && is_string($objDC->value) && strlen($objDC->value) < 1)
					{
						$objDC->value = null;
					}
				}
				// !HEADLINE attributes
				else if($objAttribute->get('type') == 'headline')
				{
					if($blnSubmitted)
					{
						$objDC->value = array('value'=>$_POST[$objDC->field]['value'],'unit'=>$_POST[$objDC->field]['unit']);
					}
					
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
				}
				// !TAGS attributes
				else if($objAttribute->get('type') == 'tags')
				{
					// load js
					$objCombiner = new Combiner();
					$objCombiner->add(PCT_TABLETREE_PATH.'/assets/js/tabletree.js');
					$GLOBALS['TL_HEAD'][] = '<script src="'.$objCombiner->getCombinedFile().'"></script>';
					
					$arrFieldDef['dataContainer'] = $objDC;
 					
 					// set value for validators
 					if(is_array($objDC->value) && $this->multiple)
					{
						$objDC->value = implode(',', $objDC->value);
						Input::setPost($objDC->field,$objDC->value);
					}
					
					if($this->sortable && !$blnSubmitted)
					{
						Input::setPost($strOrderField.'_'.$objDC->field,StringUtil::deserialize($objDC->activeRecord->{$strOrderField.'_'.$objDC->field}));
					}
					
					// merge dca attributes with field definition
					if(is_array($arrAttributes))
					{
						$arrAttributes = array_merge($arrAttributes,$arrFieldDef);
					}
					
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrAttributes,$objDC,$objDC->value);
					
					// database update
					if($blnSubmitted & isset($_POST[$objDC->field]))
					{
						$objDC->value = $_POST[$objDC->field];
						if($this->multiple && !is_array($objDC->value))
						{
							$objDC->value = array_filter(explode(',', $objDC->value));
						}
						
						\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($objDC->value,$objDC);
						
						// save the order field
						if(isset($_POST[$strOrderField.'_'.$objDC->field]))
						{
							$newOrder = is_array($_POST[$strOrderField.'_'.$objDC->field]) ? $_POST[$strOrderField.'_'.$objDC->field] : explode(',',$_POST[$strOrderField.'_'.$objDC->field]);
							
							$dc = clone($objDC);
							$dc->field = $strOrderField.'_'.$objDC->field;
							
							\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($newOrder,$dc);
						}
					}
					
					if( $blnSubmitted && (empty($objDC->value) || count($objDC->value) < 1) )
					{
						$objDC->value = null;
					}
					
					$this->sortable = false;
				}
				// !ALIAS attributes
				else if($objAttribute->get('type') == 'alias')
				{
					$strBuffer = $objWidget->generateLabel();
					$strBuffer .= $objWidget->generateWithError();	
				}
				// !CUSTOMELEMENTS WIDGET attributes
				else if($objAttribute->get('type') == 'customelement')
				{
					$attribute = \PCT\CustomElements\Core\AttributeFactory::findById($objAttribute->get('id'));
					$dc = clone($objDC);
					$dc->field = $attribute->uuid;
					
					// bypass the automatic field updater
					if(!$blnSubmitted && isset($_POST[$objDC->field]))
					{
						unset($_POST[$dc->field]);
						Input::setPost($dc->field,null);
						$dc->activeRecord->tstamp = 0;
					}
					
					// form submitted
					$formSubmitted = false;
					if(Input::post('FORM_SUBMIT') == $objDC->formSubmit)
					{
						Input::setPost('FORM_SUBMIT',$objDC->table);
						$formSubmitted = true;
					}
					
					$GLOBALS['TL_JQUERY'][] = '<script src="'.PCT_CUSTOMELEMENTS_PATH.'/assets/js/CustomElements.js'.'"></script>';
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$dc->field,$arrFieldDef,$dc);
					
					if($formSubmitted)
					{
						Input::setPost('FORM_SUBMIT',$objDC->formSubmit);
					}
				}
				// !render	
				else
				{
					$strBuffer = $objAttribute->parseWidgetCallback($objWidget,$objDC->field,$arrFieldDef,$objDC,$objDC->value);
				}
			}
			else
			{	
				if($blnSubmitted)
				{				
					// validate the input
					$objWidget->validate();
				}
				
				// recheck value
				if(!is_array($objWidget->value) && $objAttribute->get('eval_multiple'))
				{
					$objWidget->value = explode(',', $objWidget->value); 
				}
				
				// set custom template
				if(strlen($strTemplate) > 0)
				{
					$strBuffer = $objWidget->parse();
				}
				else
				{
					$strBuffer = $objWidget->generateLabel();
					$strBuffer .= $objWidget->generateWithError();
				}
			}
		}
		// HOOK let attribute generate their own widgets
		else if(method_exists($objAttribute,'generateFrontendWidget'))
		{
			$strBuffer = $objAttribute->generateFrontendWidget($objDC);
		}

		
		// make mootools jquery compatible
		if(version_compare(VERSION, '4.4','>='))
		{
			$search = array('$("ft_'.$objDC->field.'")','$("ctrl_'.$objDC->field.'")');
			$replace = array('$$("#ft_'.$objDC->field.'")[0]','$$("#ctrl_'.$objDC->field.'")[0]');
			$strBuffer = str_replace($search,$replace, $strBuffer);
		}
					
		// wizards
		if(!empty($arrFieldDef['wizard']) && is_array($arrFieldDef['wizard']))
		{
			foreach($arrFieldDef['wizard'] as $callback)
			{
				$strBuffer .= System::importStatic($callback[0])->{$callback[1]}($objDC);
			}
		}

// TODO: ! -- sortable

		// make it sortable
		if($this->sortable && strlen(strpos($strBuffer, 'ctrl_'.$strOrderField)) < 1)
		{
			$doc = new \DOMDocument();
			@$doc->loadHTML(preg_replace("/&(?!(?:apos|quot|[gl]t|amp);|#)/", "&amp;",$strBuffer));
			$elem = $doc->getElementById('sort_'.$objDC->field);
			
			$value = $objDC->value;
		
			#if(is_array($value))
			#{
			#	// convert binary values to uuid
			#	if(in_array($objAttribute->get('type'), array('files','gallery')))
			#	{
			#		$value = implode(',', array_map('StringUtil::binToUuid',array_filter($value)));
			#	}
			#}
			
			if($elem)
			{
				$class = $doc->createAttribute('class');
				$class->value = 'sortable' . ($arrFieldDef['eval']['isGallery'] ? ' sgallery':'');
				$elem->appendChild($class);
					
				$str = '<p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>';
				$str .= $elem->C14N();
				$str .= '<input type="hidden" id="ctrl_'.$strOrderField.'_'.$objDC->field.'" name="'.$strOrderField.'_'.$objDC->field.'" value="'.$value.'">';
				$str .= '<script>Backend.makeMultiSrcSortable("sort_'.$objDC->field.'", "ctrl_'.$strOrderField.'_'.$objDC->field.'"'.(version_compare(VERSION, '4.4','>=') ? ', "ctrl_'.$objDC->field.'"':'').');</script>';
				
				// replace sort container
				$preg = preg_match('/<ul(.*?)\/ul>/', $strBuffer,$result); 
				if($preg)
				{
					$strBuffer = str_replace($result[0], $str, $strBuffer);
				}
			}
			// fallback if DOMDocument fails
			else
			{
				$preg = preg_match('/<ul id="sort_'.$objDC->field.'"(.*?)\/ul>/', $strBuffer,$result); 
				if($preg)
				{
					$tmp = $result[0];
					// inject the class
					$elem = preg_replace('/class="/', 'class="sortable ', $tmp, 1);
					
					$str = '<p class="sort_hint">' . $GLOBALS['TL_LANG']['MSC']['dragItemsHint'] . '</p>';
					$str .= $elem;
					$str .= '<input type="hidden" id="ctrl_orderSRC_'.$objDC->field.'" name="orderSRC_'.$objDC->field.'" value="'.$value.'">';
					$str .= '<script>Backend.makeMultiSrcSortable("sort_'.$objDC->field.'", "ctrl_'.$strOrderField.'_'.$objDC->field.'"'.(version_compare(VERSION, '4.4','>=') ? ', "ctrl_'.$objDC->field.'"':'').');</script>';
	
					$strBuffer = str_replace($result[0], $str, $strBuffer);
				}
			}
			
			unset($value);
		}
		
		// render child attributes
		if($objAttribute->hasChilds())
		{
			$arr = array();
			foreach($objAttribute->get('arrChildAttributes') as $k => $objChildWidget)
			{
				$this->childWidgets[ $objChildWidget->__get('name') ] = $objChildWidget;
				
				$field = $objChildWidget->__get('name');
				$value = $objDC->activeRecord->{$field};
				
				$class = array($field,'is_child_attribute','block');
				
				$dc = new \PCT\CustomElements\Helper\DataContainerHelper;
				$dc->id = $objDC->id;
				$dc->field = $field;
				$dc->value = $value;
				$dc->table = $objDC->table;
				
				$objChildWidget->__set('value',$value);
				
				if(Input::post('FORM_SUBMIT') == $objDC->formSubmit && isset($_POST[$dc->field]) && $_POST[$dc->field] != $value)
				{
					$value = $_POST[$dc->field];
					
					if(!$blnSubmitted)
					{
						Input::setPost($dc->field,$value);
					}
					
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
				if( !empty($objChildWidget->fieldDef['wizard']) && \is_array($objChildWidget->fieldDef['wizard']) )
				{
					$class[] = 'wizard';
					foreach($objChildWidget->fieldDef['wizard'] as $callback)
					{
						$strChild .= System::importStatic($callback[0])->{$callback[1]}($dc);
					}
				}
				
				// little javascript helper to place an inserttag value back in the input field
				if(strlen(strpos($dc->value, '{{')) > 0 && !Input::post('FORM_SUBMIT'))
				{
					$data = json_encode(array('field'=>$field,'currValue'=>Controller::replaceInsertTags($dc->value),'newValue'=>$dc->value));
					$GLOBALS['TL_JQUERY'][] = '<script>CC_FrontEdit.rereplaceInsertTags('.$data.');</script>';
				}
				
				$arr[] = in_array('pct_autogrid', \Contao\Config::getInstance()->getActiveModules()) ? '<div class="'.implode(' ', $class).' autogrid one_half">'.$strChild.'</div>' : '<div class="'.implode(' ', $class).' w50">'.$strChild.'</div>';
			}
 			
 			$strBuffer .= implode('', $arr);
 			unset($arr);
		}
		
		// trigger CEs parseWidget HOOk
		$strBufferFromHook = \PCT\CustomElements\Core\Hooks::callstatic('parseWidgetHook',array($objWidget,$objDC->field,$arrFieldDef,$objDC));
		if(strlen($strBufferFromHook))
		{
			$strBuffer = $strBufferFromHook;
		}
		
		// decode entities
		if( $arrFieldDef['eval']['decodeEntities'] )
		{
			$strBuffer = StringUtil::decodeEntities($strBuffer);
		}

// TODO: ! -- rewrite the javascript calls to the Backend class
	
		if(strlen(strpos($strBuffer, 'Backend.')) > 0)
		{
			$objFunctions = \PCT\CustomElements\Helper\Functions::getInstance();	
			$arrUrl = parse_url(Environment::get('request'));
			
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
						$href = $objFunctions->addToUrl($arrUrl['query'], $arrUrl['base'] . PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH . '/assets/html/file.php');
						if($method == 'openModalBrowser')
						{
							$href = $objFunctions->addToUrl($arrUrl['query'], $arrUrl['base'] . PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH . '/assets/html/page.php');
						}

						// add values
						$href = Environment::get('base') . $href;
						
						$data = str_replace('"',"'",json_encode(array('method'=>$method,'func'=>$func,'field'=>'ctrl_'.$objDC->field,'url'=>$href,'errors'=>$errors)));

						if($objAttribute->get('type') == 'textarea' )
						{
						}
						else
						{
							$strBuffer = str_replace($func, "CC_FrontEdit.openModal(".$data .");", $strBuffer);
						}
					}
					else
					{
						if($method == 'getScrollOffset')
						{
							$strBuffer = str_replace($func, "CC_FrontEdit.getScrollOffset();", $strBuffer);
						}
					}
					
					// contao 4 pickers
					if(version_compare(VERSION, '4.4','>='))
					{
						if($method == 'openModalSelector')
						{
							$strBuffer = str_replace('$("pt_'.$objDC->field.'")','$$("#pt_'.$objDC->field.'")[0]', $strBuffer);
							$strBuffer = str_replace('this.href + document.getElementById("ctrl_'.$objDC->field.'")', 'this.href', $strBuffer);
							$strBuffer = str_replace('this.href.value', 'this.href', $strBuffer);
							
							// inject the AjaxRequest loading box
							if(strlen(strpos($strBuffer,'"callback":')) > 0)
							{
								$search = array('$("ft_'.$objDC->field.'")','$("ctrl_'.$objDC->field.'")');
								$replace = array('$$("#ft_'.$objDC->field.'")[0]','$$("#ctrl_'.$objDC->field.'")[0]');
								$strBuffer = str_replace('onSuccess',"onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),onSuccess", $strBuffer);
							}
							
							$strBuffer = str_replace('Browser.exec(json.javascript);',"Browser.exec(json.javascript);AjaxRequest.hideBox();window.fireEvent('ajax_change');",$strBuffer);
						}
					}
					
					
					$processed[] = $func;
				}
			}
		}
		
//! -- rewrite popup locations and pickers

		// rewrite calls to the contao/file.php from file pickers
		if(version_compare(VERSION,'4','<'))
		{
			if(strlen(strpos($strBuffer, 'contao/file.php')) > 0)
			{
				$strBuffer = str_replace('contao/file.php', PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/file.php',$strBuffer);
			}
			
			// rewrite calls to the contao/page.php e.g. from page pickers
			if(strlen(strpos($strBuffer, 'contao/page.php')) > 0)
			{
				$strBuffer = str_replace('contao/page.php', PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/page.php',$strBuffer);
			}
		}
		
		// Contao 4, pickers
		if(strlen(strpos($strBuffer, 'picker_builder=1')) > 0)
		{
			$value = $objDC->value;
			
			#// convert values
			if(!empty($value))
			{
				if($this->multiple && is_array($value))
				{
					// convert binary to uuid
					if( in_array($objAttribute->get('type'), array('files','gallery')) )
					{
						$value = array_map('StringUtil::binToUuid',array_filter($value));
					}
				}
				else if(Validator::isBinaryUuid($value))
				{
					$value = StringUtil::binToUuid($value);
				}
			}
			
			if(is_array($value))
			{
				$value = implode(',', $value);
			}
			
			$params = array
			(
				'_table' 	=> Input::get('table'),
				'_id'		=> Input::get('id'),
				'_field'	=> Input::get('field'),
				#'act'	=> 'show',
				'rt'		=> REQUEST_TOKEN,
				'picker' 	=> $objDC->field,
				'value' 	=> $value,
			);
			
			$strBuffer = str_replace('picker_builder=1', http_build_query($params), $strBuffer);
			unset($params);
			unset($value);
		}
		
		// rewrite calls to the pct table tree widget
		if(strlen(strpos($strBuffer, 'system/modules/pct_tabletree_widget/assets/html/PageTableTree.php')) > 0)
		{
			$strBuffer = str_replace('system/modules/pct_tabletree_widget/assets/html/PageTableTree.php', PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/html/tabletree.php',$strBuffer);
		}

		// !FORM_SUBMIT add to save list
		if(Input::post('FORM_SUBMIT') == $objDC->formSubmit && (Input::post('save') || Input::post('saveNclose')) && !$objWidget->hasErrors())
		{
			// trigger save callback
			if(is_array($arrFieldDef['save_callback']))
			{
				$objDC->objAttribute = $objAttribute;
				foreach($arrFieldDef['save_callback'] as $callback)
				{
					if (is_array($callback))
					{
					   $objDC->value = System::importStatic($callback[0])->{$callback[1]}($objDC->value,$objDC,$this);	
					}
					else if(is_callable($callback))
					{
					   $objDC->value = $callback($objDC->value,$objDC,$this);
					}
				}
			}
			
			// trigger the storeValue callback
			$saveDataAs = $objAttribute->get('saveDataAs') ?: 'data';
			
			// run the storeValue Hook here
			$set = \PCT\CustomElements\Core\Hooks::callstatic( 'storeValueHook',array($objAttribute->get('id'),array($saveDataAs=>$objDC->value)) );
			
			if($set[$saveDataAs] != $objDC->value)
			{
				$objDC->value = $set[$saveDataAs];
			}
			
			// decode entities
			if($arrFieldDef['eval']['decodeEntities'] || $arrFieldDef['inputType'] == 'textarea' || strlen($arrFieldDef['eval']['rte']) > 0)
			{
				$objDC->value = StringUtil::decodeEntities($objDC->value);
			}
					
			// custom order
			if($blnSubmitted && isset($_POST[$strOrderField.'_'.$objDC->field]) && !empty($_POST[$strOrderField.'_'.$objDC->field]))
			{
				$newOrder = is_array($_POST[$strOrderField.'_'.$objDC->field]) ? Input::post($strOrderField.'_'.$objDC->field) : explode(',',Input::post($strOrderField.'_'.$objDC->field));
				
				// save the order field
				$dc = clone($objDC);
				$dc->field = $strOrderField.'_'.$objDC->field;
				
				\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($newOrder,$dc);
			
				// convert new order to binary
				if( in_array($objAttribute->get('type'), array('files','gallery')) && $objAttribute->get('eval_multiple') )
				{
					$objDC->value = array_map('StringUtil::uuidToBin',$newOrder);
				}
			}
			
			// multiple values in blob fields
			if(!is_array($objDC->value) && $objAttribute->get('eval_multiple') && strlen(strpos(strtolower($arrFieldDef['sql']),'blob')) > 0)
			{
				if(!is_array($objDC->value))
				{
					$objDC->value = array_filter(explode(',', $objDC->value));
				}
				
				// convert to binary
				if( in_array($objAttribute->get('type'), array('files','gallery')) )
				{
					$objDC->value = array_map('StringUtil::uuidToBin',$objDC->value);
				}
			}
			
			// autosubmit member id to protection attribute when submitted empty
			if($objAttribute->get('type') == 'protection' && FE_USER_LOGGED_IN && $objAttribute->get('isDownload') && empty($objDC->value))
			{
				$objUser = \Contao\FrontendUser::getInstance();
				$objDC->value = $objUser->id;
				unset($objUser);
			}
				
			if(Input::get('act') == 'fe_overrideAll')
			{
				if(count($arrSession['CURRENT']['IDS']) > 0 && is_array($arrSession['CURRENT']['IDS']))
				{
					foreach($arrSession['CURRENT']['IDS'] as $id)
					{
						$objDC->id = $id;
						\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($objDC->value,$objDC);
					}
				}
			}
			else
			{
				\PCT\CustomCatalog\FrontEdit::addToDatabaseSetlist($objDC->value,$objDC);
			}
			
			// remove the session
			$objSession->remove($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']);
		}
		
		// observe ajax on the field
		$blnIsAjax = false;
		if(!$blnSubmitted && Environment::get('isAjaxRequest') && Input::post('name') == $objDC->field && $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['SETTINGS']['simulateAjaxReloads'])
		{
			$blnIsAjax = true;
		}
		
		$arrWidgetClasses = array();
		$arrWidgetClasses[] = $objAttribute->get('type');
		
		if($objAttribute->get('eval_tl_class_w50'))
		{
			$arrWidgetClasses[] = (in_array('pct_autogrid', Config::getInstance()->getActiveModules()) ? 'autogrid one_half' : 'w50');
		}
			
		$this->widget = $this;
		$this->widget->classes = $arrWidgetClasses;
		$this->widget->class = implode(' ', $arrWidgetClasses);
		$this->widget->id = $objWidget->__get('name');
		$this->widget->ajax = $blnIsAjax;
		
		$arrClasses = array('block');
		$arrClasses[] = $objAttribute->get('type');
		
		if($objAttribute->get('type') == 'textarea' && $arrFieldDef['eval']['rte'])
		{
			$arrClasses[] = 'hasTiny';
		}

		if( $objDC->isAjax )
		{
			$arrClasses[] = 'ajax';
		}

		// wrap the widget in a unique div
		$strWrapperStart = '<div id="'.$objWidget->__get('name').'_widget_container" class="widget_container '.implode(' ', $arrClasses).'">';
		$strWrapperStop = '</div>';
		$strBuffer = $strWrapperStart.$strBuffer.$strWrapperStop;
		
		// inject a little javascript ajax helper
		//! -- ajax
		if($this->isAjaxField)
		{
			// preserve scripts
			#$orig_allowedTags = Config::get('allowedTags');
			#\Config::set('allowedTags', \Config::get('allowedTags').'<script>');
			#$buffer = $strBuffer;
			#$buffer = str_replace(array('<script>','</script>'),array("###SCRIPT_START###","###SCRIPT_STOP###"),$buffer);
			#$buffer = StringUtil::decodeEntities(StringUtil::substrHtml($buffer,strlen($buffer)));
			#$buffer = str_replace("'","###PLACEHOLDER###",$buffer);
			#$strAjaxBuffer = $buffer;
			
			$moo_helper = new \Contao\FrontendTemplate('moo_cc_frontedit_picker_helper');
			$moo_helper->field = $objDC->field;
			$moo_helper->value = $objDC->value;
			$buffer = $moo_helper->parse();
			$buffer = str_replace(array('<script>','</script>'),array("###SCRIPT_START###","###SCRIPT_STOP###"),$buffer);
			$buffer = StringUtil::decodeEntities(StringUtil::substrHtml($buffer,strlen($buffer)));
			$buffer = str_replace("'","###PLACEHOLDER###",$buffer);
			$moo = $buffer;
			// reset to standard
			#Config::set('allowedTags', $orig_allowedTags);
			
			if( $objDC->isAjax )
			{
				$GLOBALS['TL_HEAD'][] = '<script>var isAjax=true;</script>';
			}

			$js_helper = new \Contao\FrontendTemplate('js_cc_frontedit_ajaxhelper');
			$js_helper->widget = $this->widget;
			$js_helper->dataContainer = $objDC;
			$js_helper->field = $objDC->field;
			$js_helper->value = $objDC->value;
			$js_helper->wrapperStart = $strWrapperStart;
			$js_helper->wrapperStop = $strWrapperStop;
			$js_helper->isAjax = $objDC->isAjax;
			$js_helper->script = $moo;
			
			$strBuffer .= $js_helper->parse();
		}
		
		// remove helper sessions
		if($blnSubmitted && ! Environment::get('isAjaxRequest'))
		{
			$objSession->remove($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName']);
		}
		
		// cache
		$this->widget = $strBuffer;
		
		return $strBuffer;

	}
	
	
	/**
	 * Generate an upload widget
	 * @param object	Additional settings
	 * @property string 	arrSettings['uploadFolder']
	 * @property boolean 	arrSettings['useHomeDir']
	 * @property boolean 	arrSettings['doNotOverwrite']
	 * @property array 		arrSettings['extensions']
	 * @property boolean 	arrSettings['createUploadFolder']
	 * @param string	Custom template name
	 * @return string
	 */
	public function uploadWidget($arrSettings=array(), $strTemplate='')
	{
		if(strlen($this->uploadWidget) > 0)
		{
			return $this->uploadWidget;
		}
		
		$objAttribute = $this->attribute();
		if($objAttribute === null)
		{
			return '';
		}
		
		// valid attribute types
		if( !in_array($objAttribute->get('type'), $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['uploadableAttributes']) )
		{
			return '';
		}
		
		$objModule = $objAttribute->get('objCustomCatalog')->getModule();
		$objDC = new \PCT\CustomElements\Plugins\FrontEdit\Helper\DataContainerHelper;
		$objDC->value = $objAttribute->getValue();
		$objDC->table = $objAttribute->get('objCustomCatalog')->getTable();
		$objDC->id = Input::get('id');
		$objDC->field = $objAttribute->get('alias');
		$strUploadFolder = $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['defaultUploadFolder'] ?: 'files/uploads';
		
		// custom data container object
		if(strlen($arrSettings['dataContainer']) > 0)
		{
			$objDC = $arrSettings['dataContainer'];
		}
		
		// custom upload folder
		if(strlen($arrSettings['uploadFolder']) > 0)
		{
			$strUploadFolder = $arrSettings['uploadFolder'];
		}
		
		// check if upload folder exists if it is not supposed to be created on the fly
		if(!is_dir(TL_ROOT.'/'.$strUploadFolder) && !$arrSettings['createUploadFolder'])
		{
			return sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['ERR']['invalid_upload_folder'],$strUploadFolder);
		}
		
		$objFolder = new Folder($strUploadFolder);
		$intUploadFolder = $objFolder->getModel()->uuid;
				
		$objWidget = new $GLOBALS['TL_FFL']['upload'];
		$objWidget->name = 'upload_'.$objDC->field;
		$objWidget->id = 'upload_'.$objAttribute->get('id');
		$objWidget->addSubmit = true;
		$objWidget->slabel = $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_upload'] ?: 'Upload';
		$objWidget->storeFile = true;
		$objWidget->uploadFolder = $intUploadFolder;
		$objWidget->extensions = Config::get('uploadTypes');
		
		// apply settings to widget
		if(count($arrSettings) > 0)
		{
			foreach($arrSettings as $k => $v)
			{
				$objWidget->{$k} = $v;
			}
		}
		
		if($objAttribute->get('type') == 'gallery')
		{
			$arrSettings['multiple'] = true;
		}
		
		if($arrSettings['extensions'] !== null)
		{
			$uploadTypes = $arrSettings['extensions'];
			if(is_array($uploadTypes))
			{
				$uploadTypes = implode(',', $uploadTypes);
			}
			$objWidget->extensions = $uploadTypes;
		}
		
		// validate on submit
		if(Input::post('FORM_SUBMIT') == $objDC->table.'_'.$objModule->id)
		{
			$arrFiles = array();
			
			// store the upload information
			if((boolean)$arrSettings['autoUpdate'] === true && isset($_FILES[$objWidget->name]) && count($_FILES[$objWidget->name]) > 0)
			{
				$arrFiles = $_FILES;
			}
			
			$objWidget->validate();
			
			// update the attribute
			if(count($arrFiles) > 0)
			{
				$setSetValue = array();
				foreach($arrFiles as $k => $v)
				{
					// skip empty or invalid uploads
					if($v['error'] != 0 || strlen($v['name']) < 1 || $v['size'] == 0)
					{
						continue;
					}
					
					// add the file to the file system
					if(file_exists(TL_ROOT.'/'.$strUploadFolder.'/'.$v['name']))
					{
						$setSetValue[$k][] = Dbafs::addResource($strUploadFolder.'/'.$v['name'])->uuid;
					}
				}
				
				$setSetValue = array_filter($setSetValue);
				
				$objDatabase = Database::getInstance();
				if(!empty($setSetValue[$objWidget->name]) && $objDatabase->tableExists($objDC->table) && $objDatabase->fieldExists($objDC->field,$objDC->table) && (int)$objDC->id > 0)
				{
					$setValue = $setSetValue[ $objWidget->name ];
					if(!$arrSettings['multiple'] && is_array($setValue))
					{
						$setValue = implode('', $setValue);
					}
					
					$objDatabase->prepare("UPDATE ".$objDC->table." %s WHERE id=?")->set( array($objDC->field => $setValue) )->execute($objDC->id);
					
					// flush post data and reload page to see changes
					if((boolean)$arrSettings['autoReload'] !== false || !isset($arrSettings['autoReload']))
					{
						Controller::reload();
					}
				}
			}
		}
		
		if(strlen($strTemplate) > 0)
		{
			$objWidget->__set('customTpl',$strTemplate);
		}
		
		$this->uploadWidget = $objWidget->generate();
		
		
		return $this->uploadWidget;
	}
}