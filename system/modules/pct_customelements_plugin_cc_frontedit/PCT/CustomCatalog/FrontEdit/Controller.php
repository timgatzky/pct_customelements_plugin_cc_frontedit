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
 * Import
 */

use Contao\Backend;
use Contao\Combiner;
use Contao\Controller as ContaoController;
use Contao\Database;
use Contao\Environment;
use Contao\Image;
use Contao\File;
use Contao\FilesModel;
use Contao\FormSubmit;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use \PCT\CustomElements\Helper\Functions as Functions;
use \PCT\CustomElements\Helper\ControllerHelper as ControllerHelper;

/**
 * Class file
 * Controller
 */
class Controller extends \PCT\CustomElements\Plugins\CustomCatalog\Core\Controller
{
	/**	
	 * Return the Contao session bag
	 * @return object
	 */
	public static function getSession()
	{
		return System::getContainer()->get('session');
	}


	/**
	 * Add back end assets to the front end
	 */
	public static function addAssets()
	{
		global $objPage;

		$strContao = 'var Contao={'
		. 'theme:"' . Backend::getTheme() . '",'
		. 'lang:{'
			. 'close:"' . $GLOBALS['TL_LANG']['MSC']['close'] . '",'
			. 'collapse:"' . $GLOBALS['TL_LANG']['MSC']['collapseNode'] . '",'
			. 'expand:"' . $GLOBALS['TL_LANG']['MSC']['expandNode'] . '",'
			. 'loading:"' . $GLOBALS['TL_LANG']['MSC']['loadingData'] . '",'
			. 'apply:"' . $GLOBALS['TL_LANG']['MSC']['apply'] . '",'
			. 'picker:"' . $GLOBALS['TL_LANG']['MSC']['pickerNoSelection'] . '"'
		. '},'
		. 'script_url:"' . TL_ASSETS_URL . '",'
		. 'path:"' . TL_PATH . '",'
		. 'request_token:"' . REQUEST_TOKEN . '",'
		. 'referer_id:"' . TL_REFERER_ID . '"'
		. '};';
		
		$strLocale = 'Locale.define("en-US","Date",{'
		. 'months:["' . implode('","', $GLOBALS['TL_LANG']['MONTHS']) . '"],'
		. 'days:["' . implode('","', $GLOBALS['TL_LANG']['DAYS']) . '"],'
		. 'months_abbr:["' . implode('","', $GLOBALS['TL_LANG']['MONTHS_SHORT']) . '"],'
		. 'days_abbr:["' . implode('","', $GLOBALS['TL_LANG']['DAYS_SHORT']) . '"]'
		. '});'
		. 'Locale.define("en-US","DatePicker",{'
		. 'select_a_time:"' . $GLOBALS['TL_LANG']['DP']['select_a_time'] . '",'
		. 'use_mouse_wheel:"' . $GLOBALS['TL_LANG']['DP']['use_mouse_wheel'] . '",'
		. 'time_confirm_button:"' . $GLOBALS['TL_LANG']['DP']['time_confirm_button'] . '",'
		. 'apply_range:"' . $GLOBALS['TL_LANG']['DP']['apply_range'] . '",'
		. 'cancel:"' . $GLOBALS['TL_LANG']['DP']['cancel'] . '",'
		. 'week:"' . $GLOBALS['TL_LANG']['DP']['week'] . '"'
		. '});';
		
		// contao 3
		if(version_compare(VERSION, '4','<'))
		{
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript">'.$strContao.'</script>';
			
			if(!$objPage->hasJQuery)
			{
				$GLOBALS['TL_JAVASCRIPT'][] = '//code.jquery.com/jquery-' . $GLOBALS['TL_ASSETS']['JQUERY'] . '.min.js';
			}
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript">jQuery.noConflict();</script>';			
			#$GLOBALS['TL_HEAD'][] = '<script src="assets/contao/js/core-uncompressed.js"></script>';
			
			// css
			$objCombiner = new Combiner();
		    $objCombiner->add('assets/mootools/colorpicker/'. $GLOBALS['TL_ASSETS']['COLORPICKER'] .'/css/mooRainbow.css', $GLOBALS['TL_ASSETS']['COLORPICKER']);
		    $objCombiner->add('assets/mootools/chosen/chosen.css');
		    $objCombiner->add('assets/mootools/stylect/css/stylect.css');
		    $objCombiner->add('assets/mootools/simplemodal/'. $GLOBALS['TL_ASSETS']['SIMPLEMODAL'] .'/css/simplemodal.css', $GLOBALS['TL_ASSETS']['SIMPLEMODAL']);
		    $objCombiner->add('assets/mootools/datepicker/'. $GLOBALS['TL_ASSETS']['DATEPICKER'] .'/datepicker.css', $GLOBALS['TL_ASSETS']['DATEPICKER']);
		    $objCombiner->add('system/themes/default/fonts.css');
		    $objCombiner->add(PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/css/contao/basic.css');
		    $objCombiner->add(PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/css/styles.css');
		    $GLOBALS['TL_CSS'][] = $objCombiner->getCombinedFile();
				 
			// javascripts
			$objCombiner = new Combiner();
		    $objCombiner->add('assets/mootools/core/' . $GLOBALS['TL_ASSETS']['MOOTOOLS'] . '/mootools.js', $GLOBALS['TL_ASSETS']['MOOTOOLS']);
		    $objCombiner->add('assets/mootools/colorpicker/'. $GLOBALS['TL_ASSETS']['COLORPICKER'] .'/js/mooRainbow.js', $GLOBALS['TL_ASSETS']['COLORPICKER']);
		    $objCombiner->add('assets/mootools/chosen/chosen.js');
		    $objCombiner->add('assets/mootools/stylect/js/stylect.js');
		    $objCombiner->add('assets/mootools/simplemodal/'. $GLOBALS['TL_ASSETS']['SIMPLEMODAL'] .'/js/simplemodal.js', $GLOBALS['TL_ASSETS']['SIMPLEMODAL']);
		    $objCombiner->add('assets/mootools/datepicker/'. $GLOBALS['TL_ASSETS']['DATEPICKER'] .'/datepicker.js', $GLOBALS['TL_ASSETS']['DATEPICKER']);
		    $objCombiner->add('assets/mootools/mootao/Mootao.js');
		    $objCombiner->add('assets/contao/js/core-uncompressed.js');
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="'.$objCombiner->getCombinedFile().'"></script>';
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript">'.$strLocale.'</script>';
		
		}
		// contao 4
		else
		{
			if(!$objPage->hasJQuery)
			{
				$GLOBALS['TL_JAVASCRIPT'][] = TL_ASSETS_URL.'assets/jquery/js/jquery.js';
			}
			
			$strTheme = 'flexible';
			
			// css
			$objCombiner = new Combiner();
		    $objCombiner->add(TL_ASSETS_URL.'system/themes/'.$strTheme.'/fonts.css');
		    $objCombiner->add(TL_ASSETS_URL.'assets/colorpicker/css/mooRainbow.min.css');
		    $objCombiner->add(TL_ASSETS_URL.'assets/chosen/css/chosen.min.css');
		    $objCombiner->add(TL_ASSETS_URL.'assets/simplemodal/css/simplemodal.min.css');
		    $objCombiner->add(TL_ASSETS_URL.'assets/datepicker/css/datepicker.min.css');
		    $objCombiner->add(TL_ASSETS_URL.'system/themes/'.$strTheme.'/basic.css');
		    #$objCombiner->add(PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/css/contao/basic.css');
		    $objCombiner->add(PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/css/contao/main.css');
		    #$objCombiner->add(TL_ASSETS_URL.'system/themes/'.$strTheme.'/main.css');
			$GLOBALS['TL_CSS'][] = $objCombiner->getCombinedFile();
			
			// javascript
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript">jQuery.noConflict();</script>';
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript">'.$strContao.'</script>';
			#$GLOBALS['TL_HEAD'][] = '
			#<script src="'.TL_ASSETS_URL.'assets/mootools/js/mootools.min.js"></script>
			#<script src="'.TL_ASSETS_URL.'assets/colorpicker/js/mooRainbow.min.js"></script>
			#<script src="'.TL_ASSETS_URL.'assets/chosen/js/chosen.min.js"></script>
			#<script src="'.TL_ASSETS_URL.'assets/datepicker/js/datepicker.min.js"></script>
			#<script src="'.TL_ASSETS_URL.'bundles/contaocore/mootao.min.js"></script>
			#<script>'.$strLocale.'</script>';
			
			$GLOBALS['TL_HEAD'][] = '<script src="'.TL_ASSETS_URL.'assets/mootools/js/mootools.min.js"></script>';
			$GLOBALS['TL_HEAD'][] = '<script src="'.TL_ASSETS_URL.'assets/colorpicker/js/mooRainbow.min.js"></script>';
			$GLOBALS['TL_HEAD'][] =	'<script src="'.TL_ASSETS_URL.'assets/chosen/js/chosen.min.js"></script>';
			$GLOBALS['TL_HEAD'][] = '<script src="'.TL_ASSETS_URL.'assets/datepicker/js/datepicker.min.js"></script>';
			$GLOBALS['TL_HEAD'][] =	'<script src="'.TL_ASSETS_URL.'bundles/contaocore/mootao.min.js"></script>';
			$GLOBALS['TL_HEAD'][] =	'<script>'.$strLocale.'</script>';
			
			// rewrite contaocore.js to make it work with jquery
			$strFile = 'assets/cc_frontedit/js/contao_core.js';
			$objContaoCoreJs = new File($strFile,true);
			if(!$objContaoCoreJs->exists() || $GLOBALS['PCT_CUSTOMCATALOG']['debug'] === true)
			{
				// grab original
				$strOrigFile = TL_ASSETS_URL.'bundles/contaocore/core.js';
				
				$objFile = new File($strOrigFile,true);
				$strContent = ''; #$objFile->getContent();
				
				if(file_exists($strOrigFile) && strlen($strContent) < 1) 
				{
					$strContent = file_get_contents($strOrigFile);
				}
				
				if(strlen($strContent) > 0)
				{
					$search = array("$('tl_tablewizard')","$('tl_select')","$('home')","$(id)","$(oid)","$('tl_ajaxBox')","$('tl_ajaxOverlay')","$(document.body)","overlay === null","box === null");
					$replace = array("$$('#tl_tablewizard')[0]","$$('#tl_select')[0]","$$('#home')[0]","$$('#'+id)[0]","$$('#'+oid)","$$('#tl_ajaxBox')[0]","$$('#tl_ajaxOverlay')[0]","$$(document.body)[0]","overlay === null || overlay == undefined","box === null || box == undefined");
					
					// in makeMultiSrcSortable
					$search[] = "$$('#'+oid)";
					$replace[] = "$$('#'+oid)[0]";
					
					$strContent = str_replace($search,$replace,$strContent);
					$objTempFile = new File($strFile);
					$objTempFile->write($strContent);
					$objTempFile->close();
				}
			}
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="'.$objContaoCoreJs->path.'"></script>'; 
			#$objCombiner->add($objContaoCoreJs->path);
			
			
			// rewrite simplemodal.js to make it work with jquery
			$strFile = 'assets/cc_frontedit/js/simplemodal.js';
			$objSimpleModalJs = new File($strFile,true);
			if(!$objSimpleModalJs->exists() || $GLOBALS['PCT_CUSTOMCATALOG']['debug'] === true)
			{
				// grab original
				$strOrigFile = TL_ASSETS_URL.'assets/simplemodal/js/simplemodal.js';
				#$objFile = new \File($strOrigFile,true);
				$strContent = ''; #$objFile->getContent();
				if(file_exists($strOrigFile) && strlen($strContent) < 1) 
				{
					$strContent = file_get_contents($strOrigFile);
				}
				if(strlen($strContent) > 0)
				{
					$search = array
					(
						'$("simple-modal")',"$('simple-modal')",
						'$("simple-modal-overlay")',"$('simple-modal-overlay')"
					);
					$replace = array
					(
						'$$("#simple-modal")[0]','$$("#simple-modal")[0]',
						'$$("#simple-modal-overlay")[0]','$$("#simple-modal-overlay")[0]'
					);
					
					$strContent = str_replace($search,$replace,$strContent);
					$objTempFile = new File($strFile);
					$objTempFile->write($strContent);
					$objTempFile->close();
				}
			}
			$GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="'.$objSimpleModalJs->path.'"></script>';
			#$objCombiner->add($objSimpleModalJs->path);
			#$objCombiner->add(TL_ASSETS_URL.'bundles/contaocore/mootao.js');
			
			#$GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="'.TL_ASSETS_URL.'assets/simplemodal/js/simplemodal.js'.'"></script>';
			#$GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="'.TL_ASSETS_URL.'bundles/contaocore/mootao.js'.'"></script>';
			#$GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="'.$objCombiner->getCombinedFile().'"></script>';
		}
		
	    $GLOBALS['TL_JAVASCRIPT'][] = PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/js/CC_FrontEdit.js';
	}
	
	
	/**
	 * Generates the operations buttons list by an active record data (database result) or an array
	 * @param object			FrontendTemplate
	 * @param object||array		DatabaseResult || Array
	 * @param object			CustomCatalog
	 * @return string			Html output
	 */
	public function addButtonsToTemplateByRow($objTemplate, $varRow, $objCustomCatalog): FrontendTemplate
	{
		global $objPage;
		
		if(empty($varRow))
		{
			return '';
		}
				
		$objRow = null;
		if(is_array($varRow))
		{
			$objRow = new \stdClass;
			foreach($varRow as $k => $v)
			{
				$objRow->{$k} = $v;
			}
		}
		else if(is_object($varRow))
		{
			$objRow = $varRow;
		}
		
		$objFunction = \PCT\CustomElements\Helper\Functions::getInstance();
		
		$objDcaHelper = \PCT\CustomElements\Plugins\CustomCatalog\Helper\DcaHelper::getInstance();
		$objCC = $objCustomCatalog;
		$objModule = $objCustomCatalog->getModule();
		$arrDefaultDCA = $objDcaHelper->getDefaultDataContainerArray();
		$objMultilanguage = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage;
		$arrChilds = StringUtil::deserialize( $objCC->get('cTables') );
		if( !is_array($arrChilds) )
		{
			$arrChilds = explode(',',$arrChilds);
		}
		
		if(count($arrChilds) > 0)
		{
			foreach($arrChilds as $i => $childTable)
			{
				$objChildCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($childTable);
				if(!$objChildCC)
				{
					unset($arrChilds[$i]);
				}
				else if(!$objChildCC->get('active'))
				{
					unset($arrChilds[$i]);
				}
			}
		}
		
		$hasChilds = count($arrChilds) > 0 ? true : false;
		
		$strAliasField = $objCC->getAliasField();
		$strAlias = $objCC->getCustomElement()->get('alias');
		$strTable = $objCC->getTable();
		$strLanguage = $objMultilanguage->getActiveFrontendLanguage();
		$strJumpTo = PageModel::findByPk($objPage->id)->getFrontendUrl();
		
		if(!is_array($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable][$objRow->id]['keys']))
		{
			$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable][$objRow->id]['keys'] = array();
		}
		
		System::loadLanguageFile('tl_pct_customcatalog');
		System::loadLanguageFile('tl_content');
		System::loadLanguageFile($strTable);
		
		// load the data container to the frontend
		if(!$GLOBALS['TL_DCA'][$strTable])
		{
			$objSystem = new \PCT\CustomElements\Plugins\CustomCatalog\Core\SystemIntegration();
			
			// fallback CC <= 1.4.14
			if(version_compare(PCT_CUSTOMCATALOG_VERSION, '1.4.14','<'))
			{
				$c = $GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'];
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = true;
				
				$objSystem->loadCustomCatalog($strTable,true);
				
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = $c;
			}
			else
			{
				$objSystem->loadDCA($strTable);
			}
		}
		
		// override the switchToEdit option
		if(!$objModule->customcatalog_edit_switchToEdit && $GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'])
		{
			$GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'] = false;
		}
		
				
		// Create a datacontainer
		$objDC = new \PCT\CustomElements\Plugins\FrontEdit\Helper\DataContainerHelper($strTable);
		
		$arrOperations = $arrDefaultDCA['list']['operations'] ?: array();
		$arrListOperations = StringUtil::deserialize($objCC->get('list_operations'));
		
		// include the toggle button
		if(strlen($objCC->getPublishedField()) > 0 && in_array('toggle', $arrListOperations))
		{
			array_insert($arrOperations,count($arrOperations),array('toggle' => $this->getToggleVisibilityButton($objRow->row(),$strTable) ));
		}
		
		// include the cut button
		if(in_array($objCC->get('list_mode'),array(4,5,'5.1')) && in_array('cut', $arrListOperations))
		{
			array_insert($arrOperations,count($arrOperations),array('cut' => $this->getCutButton($objRow->row(),$strTable) ));
		}
		
		// reorder
		if(count($arrListOperations) > 0)
		{
			$tmp = array();
			foreach($arrListOperations as $i => $key)
			{
				if(isset($arrOperations[$key]))
				{
					$tmp[$key] = $arrOperations[$key];
				}
			}
			$arrOperations = $tmp;
			unset($tmp);
		}
		
		// remove empty values from array
		$arrOperations = array_filter($arrOperations);
		
		$arrButtons = array();
		$i = 0;
		foreach($arrOperations as $key => $button)
		{
			if(in_array($key,$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['ignoreButtons']))
			{
				continue;
			}
			
			// restrict buttons on entry level
			if(!empty($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable][$objRow->id]['keys']) && !in_array($key, $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable][$objRow->id]['keys']))
			{
				continue;
			}
			
			$jumpTo = $strJumpTo; 
			
			// overwrite the jumpTo page when editing should be done on a different page
			if($key == 'edit' && $objModule->customcatalog_jumpTo > 0 && $objModule->customcatalog_jumpTo != $objPage->id)
			{
				$jumpTo = PageModel::findByPk($objModule->customcatalog_jumpTo)->getFrontendUrl();
			}
			
			$href = (isset($button['href']) ? $button['href'] : '');
			// copy,cut button in mode 4,5,5.1 should call the paste button
			if(in_array($objCC->get('list_mode'),array(4,5,'5.1')))
			{
				if($key == 'copy' && !$GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'] )
				{
					$href = 'act=paste&amp;mode=copy';
				}
				
				if($key == 'cut')
				{
					$href = 'act=paste&amp;mode=cut';
				}
			}
			
			$title = sprintf($button['label'][1],$objRow->id);
			$href = $objFunction->addToUrl($href.'&amp;do='.$strAlias.'&amp;table='.$strTable.'&amp;id='.$objRow->id.($objRow->pid > 0 ? '&amp;pid='.$objRow->pid : ''), $jumpTo);
			// add the items parameter to the url
			$href = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.(strlen($strAliasField) > 0 && strlen($objRow->{$strAliasField}) > 0 ? $objRow->{$strAliasField} : $objRow->id) ,$href);
			// add the request token
			if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
			{
				$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
			}
			// simulate a switchToEdit
			if($key == 'copy' && $objModule->customcatalog_jumpTo > 0 && $GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'] )
			{
				$href = $objFunction->addToUrl('switchToEdit=1&jumpto='.$objModule->customcatalog_jumpTo, $href);
			}
			// multilanguage, add the langpid
			if($objCC->get('multilanguage'))
			{
				$langpid = $objMultilanguage->getBaseRecordId($objRow->id,$strTable,$strLanguage);
				$href = $objFunction->addToUrl('langpid='.$langpid,$href);
			}
			
			// replace the edit button with the editheader button
			if($hasChilds && $key == 'edit')
			{
				$button['icon'] = 'header.gif';	
			}
						
			$linkImage = Image::getHtml('system/themes/default/images/'.$button['icon'],$title);
			
			// set Contao 4 svgs
			if(version_compare(VERSION, '4', '>=') && strlen($button['icon']) > 0)
			{
				$button['icon'] = str_replace('gif','svg',$button['icon']);
				$linkImage = Image::getHtml('system/themes/flexible/icons/'.$button['icon'],$title);
			}
			
			$linkText = (strlen($linkImage) > 0 ? $linkImage : $button['label'][0]);
			
			$arr = array('operation',$key);
			($i%2 == 0 ? $arr[] = 'even' : $arr[] = 'odd');
			if($i == 0) {$arr[] = 'first';}
			if($i >= count($arrDefaultDCA['list']['operations'])-1) {$arr[] = 'last';}
			
			$class = implode(' ', $arr);
			
			$button['class'] = $class;
			$button['href'] = $href;
			$button['linkImage'] = $linkImage;
			
			$attributes = $button['attributes'];
			if($key == 'delete')
			{
				$attributes = sprintf($attributes,$objRow->id);
			}
			$attributes .= ' data-module="'.$objModule->id.'" data-id="'.$objRow->id.'"';
								
			// html
			$button['html'] = sprintf('<a href="%s" title="%s" class="%s" %s>%s</a>',$href,$title,$class,$attributes,$linkText);
			
			// trigger the button callbacks
			if(is_array($button['button_callback']))
			{
				$button['html'] = System::importStatic($button['button_callback'][0])->{$button['button_callback'][1]}($objRow->row(),$href,$title,$button['icon'],$attributes,$strTable);
			}
			
			$arrButtons[$key] = $button;
			
			$i++;
		}
		
		// insert child table edit buttons
		if($hasChilds)
		{
			$pos = 0;
			if(array_key_exists('edit', $arrButtons))
			{
				$pos = array_search('edit', array_keys($arrButtons));
			}
			
			$insertEditHeader = false;
				
			foreach($arrChilds as $i => $childTable)
			{
				// check if child table is active
				if(!$GLOBALS['PCT_CUSTOMCATALOG']['childTableMustBeAConfiguration'])
				{
					continue;
				}
				
				$objChildCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($childTable);
				
				$pos += $i;
				$button = $objDcaHelper->getCustomButton('editchild');
				
				$title = sprintf($button['label'][1],$objRow->id);
				
				// overwrite the jumpTo page when editing should be done on a different page
				if($objModule->customcatalog_jumpTo > 0 && $objModule->customcatalog_jumpTo != $objPage->id)
				{
					$strJumpTo = PageModel::findByPk($objModule->customcatalog_jumpTo)->getFrontendUrl();
				}
				
				$href = $objFunction->addToUrl('&amp;do='.$strAlias.'&amp;table='.$childTable.'&amp;id='.$objRow->id.($objRow->pid > 0 ? '&amp;pid='.$objRow->id : ''), $strJumpTo);
				// add the items parameter to the url
				$href = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.(strlen($strAliasField) > 0 ? $objRow->{$strAliasField} : $objRow->id) ,$href);
			
				// add the request token
				if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
				{
					$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
				}
								
				if($objChildCC)
				{
					if($objChildCC->get('icon'))
					{
						$icon = FilesModel::findByPk($objChildCC->get('icon'))->path;
						if(strlen($icon) > 0)
						{
							$icon = ControllerHelper::getInstance()->call('getImage',array($icon,'16','16','center_center'));
							$button['icon'] = $icon;
						}
					}
				}
				
				$linkImage = Image::getHtml('system/themes/default/images/'.$button['icon'],$title);
				if(version_compare(VERSION, '4', '>=') && strlen($button['icon']) > 0)
				{
					$button['icon'] = str_replace('gif','svg',$button['icon']);
					$linkImage = Image::getHtml('system/themes/flexible/icons/'.$button['icon'],$title);
				}
				$linkText = (strlen($linkImage) > 0 ? $linkImage : $button['label'][0]);
				$attributes = ' data-module="'.$objModule->id.'" data-id="'.$objRow->id.'"';
				
				// html
				$button['html'] = sprintf('<a href="%s" title="%s" class="%s" %s>%s</a>',$href,$title,$class,$attributes,$linkImage);
			
				array_insert($arrButtons,$pos,array('edit_'.$childTable=>$button));
				
				$insertEditHeader = true;
			}
		
			// remove the regular edit button and insert the editheader button
			if($insertEditHeader)
			{
				array_insert($arrButtons,$pos+1,array('editheader'=>$arrButtons['edit']));
				unset($arrButtons['edit']);
			}
						
		}
				
		// append the clipboard buttons
		if(Input::get('act') == 'paste')
		{
			array_insert($arrButtons,count($arrButtons),array('paste_after' => $this->getPasteAfterButton($objRow->row(),$strTable) ));
			
			if($objCC->get('list_mode') == 5)
			{
				array_insert($arrButtons,count($arrButtons),array('paste_into'=> $this->getPasteIntoButton($objRow->row(),$strTable) ));
			}
		}
		
		// append the multiple select checkbox
		if(Input::get('act') == 'select')
		{
			$html = '<input data-module="'.$objModule->id.'" id="ids_'.$objRow->id.'" class="tl_tree_checkbox checkbox" type="checkbox" value="'.$objRow->id.'" name="IDS[]">';
			$select = array('html'=>$html,'class'=>'select');
			
			// restrict buttons on entry level
			if(empty($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable][$objRow->id]['keys']) || in_array('select', $GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['EXCLUDE'][$strTable][$objRow->id]['keys']))
			{
				array_insert($arrButtons,count($arrButtons),array('select'=>$select));
			}
		}
		
		// Hook: Modify buttons
		if (isset($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons']) && count($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons']) > 0)
		{
			foreach($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons'] as $callback)
			{
				$arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons,$this);
			}
		}
		
		$objTemplate->empty = (count($arrButtons) < 1 ? true : false);
		$objTemplate->module = $objModule;
		$objTemplate->customcatalog = $objCC;
		$objTemplate->activeRecord = $objRow;
		$objTemplate->buttons = $arrButtons;
		$objTemplate->hasChilds = $hasChilds;
		
		return $objTemplate;
	}
	
	
	/**
	 * Simulate the switchToEdit function
	 * Handle the DC switchToEdit functionality as known from the backend
	 * @called from generatePage HOOK
	 */
	public function simulateSwitchToEdit()
	{
		$strTable = Input::get('table');
		$objFunction = new \PCT\CustomElements\Helper\Functions;
		$objSession = static::getSession();

		$arrSession = $objSession->get('CLIPBOARD_HELPER');
		$arrOrigSession = $objSession->get('CLIPBOARD') ?: array();
		$new_records = $objSession->get('new_records') ?: array();
		
		if(!empty($new_records[$strTable]) && isset($arrOrigSession[$strTable]))
		{
			$arrSession[$strTable]['mode'] = (Input::get('act') == 'copy' ? 'copy' : 'create');
		}
			
		// !switchToEdit on CREATE
		if($arrSession[$strTable]['mode'] == 'create' && Input::get('jumpto') > 0 && Input::get('act') == 'edit')
		{
			// redirect to lister page
			if(Input::get('switchToEdit') < 1)
			{
				$redirect = PageModel::findByPk(Input::get('jumpto'))->getFrontendUrl();
			}
			// redirect to details page
			else
			{
				$objFunction = new \PCT\CustomElements\Helper\Functions;
				$parse = parse_url(Environment::get('request'));
				$redirect = PageModel::findByPk(Input::get('jumpto'))->getFrontendUrl().'?'.$parse['query'];
				
				$redirect = str_replace(array('switchToEdit=1','jumpto='.Input::get('jumpto')), '', $redirect);
				
				// add the items parameter to the url
				if(!Input::get($GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter']))
				{
					$redirect = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.Input::get('id'),$redirect);
				}
				
				// add the request token
				if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'] && Input::get('rt') == '')
				{
					$redirect = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$redirect);
				}
			}
				
			// remove CLIPBOARD_HELPER session
			$arrSession[$strTable]['mode'] = 'on'.$arrSession[$strTable]['mode'];
			$arrSession[$strTable]['ref'] = PageModel::findByPk(Input::get('jumpto'))->getFrontendUrl();
			$objSession->set('CLIPBOARD_HELPER',$arrSession);
			
			// remove VALUE sessions
			$arrSession[$strTable]['CURRENT']['VALUES'] = array();
			$objSession->set($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName'],$arrSession);
			
			// redirect to edit page
			ContaoController::redirect($redirect);
		}
		
		// !switchToEdit on COPY
		else if($arrSession[$strTable]['mode'] == 'copy' && Input::get('jumpto') > 0 && Input::get('act') == 'copy')
		{
			$intNew = $arrSession[$strTable]['id'];
			$objFunction = new \PCT\CustomElements\Helper\Functions;
			$parse = parse_url(Environment::get('request'));
			$redirect = $objFunction->addToUrl($parse['query'].'&id='.$intNew.'&act=edit&jumpto=&',PageModel::findByPk(Input::get('jumpto'))->getFrontendUrl() );
			// add/rewrite the items parameter to the url
			$redirect = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.$intNew,$redirect);
			
			// add the request token
			if(!$GLOBALS['TL_CONFIG']['disableRefererCheck']  && Input::get('rt') == '')
			{
				$redirect = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$redirect);
			}
			
			// remove CLIPBOARD_HELPER session
			$arrSession[$strTable]['mode'] = 'on'.$arrSession[$strTable]['mode'];
			$arrSession[$strTable]['ref'] = ContaoController::getReferer();
			$objSession->set('CLIPBOARD_HELPER',$arrSession);
			
			// remove VALUE sessions
			$arrSession[$strTable]['CURRENT']['VALUES'] = array();
			$objSession->set($GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['sessionName'],$arrSession);
			
			// redirect to edit page
			ContaoController::redirect($redirect);
		}
	}
	
	
	/**
	 * Simulare the revise table function
	 * Basically deletes all unsaved entries from a table (tstamp <= 0)
	 * @param string	The table name
	 * @param boolean	Write log
	 */
	public static function simulateReviseTable($strTable,$blnLog=true)
	{
		// load the data container to the frontend
		if(!$GLOBALS['TL_DCA'][$strTable])
		{
			$objSystem = new \PCT\CustomElements\Plugins\CustomCatalog\Core\SystemIntegration();
			
			// fallback CC <= 1.4.14
			if(version_compare(PCT_CUSTOMCATALOG_VERSION, '1.4.14','<'))
			{
				$c = $GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'];
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = true;
				
				$objSystem->loadCustomCatalog($strTable,true);
				
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = $c;
			}
			else
			{
				$objSystem->loadDCA($strTable);
			}
		}
		
		$objDC = new \PCT\CustomElements\Plugins\FrontEdit\Helper\DataContainerHelper($strTable);
		
		$objDC->reviseTable();
	}
	

	/**
	 * POST and GET action listener
	 * Apply operations
	 * called from generatePage Hook
	 */
	public static function applyOperationsOnGeneratePage($objPage)
	{
		$strTable = Input::get('table') ?: Input::get('do');
		
		// return reader page when editing is not active
		if(Input::get($GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter']) && !Input::get('act'))
		{
			return;
		}
		
		// check if the table is allowed to be edited
		if( strlen($strTable) < 1 || !\PCT\CustomCatalog\FrontEdit::isEditable($strTable) )
		{
			return;
		}
		
		// check request token 
		if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'] && Input::get('rt') != REQUEST_TOKEN)
		{
			header('HTTP/1.1 400 Bad Request');
			if(version_compare(VERSION, '4.4', '>='))
			{
				throw new \Contao\CoreBundle\Exception\InvalidRequestTokenException('Invalid request token. Please <a href="javascript:window.location.href=window.location.href">go back</a> and try again.');
			}
			else
			{
				die_nicely('be_referer', 'Invalid request token. Please <a href="javascript:window.location.href=window.location.href">go back</a> and try again.');
			}
		}

		$objSession = static::getSession();
		
		// load the data container to the frontend
		if(!$GLOBALS['TL_DCA'][$strTable])
		{
			$objSystem = new \PCT\CustomElements\Plugins\CustomCatalog\Core\SystemIntegration();
			
			// fallback CC <= 1.4.14
			if(version_compare(PCT_CUSTOMCATALOG_VERSION, '1.4.14','<'))
			{
				$c = $GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'];
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = true;
				
				$objSystem->loadCustomCatalog($strTable,true);
				
				$GLOBALS['PCT_CUSTOMCATALOG']['SETTINGS']['bypassCache'] = $c;
			}
			else
			{
				$objSystem->loadDCA($strTable);
			}
		}
		
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strTable);
		if($objCC === null)
		{
			return;
		}
			
		if(!defined('CURRENT_ID')) {define('CURRENT_ID', Input::get('id'));}
		
		// Set a user ID for versions
		$objUser = new \StdClass;
		$objUser->id = isset($GLOBALS['TL_CONFIG']['customcatalog_edit_admin']) ? $GLOBALS['TL_CONFIG']['customcatalog_edit_admin'] : 1;
		
		// Create a datacontainer
		$objDC = new \PCT\CustomElements\Plugins\FrontEdit\Helper\DataContainerHelper($objCC->getTable());
		$objDC->User = $objUser;
		$objDC->intId = $objDC->id = Input::get('id');
		// handle parent tables
		if(!empty($GLOBALS['TL_DCA'][$strTable]['config']['ptable']))
		{
			$objDC->ptable = $GLOBALS['TL_DCA'][$strTable]['config']['ptable'];
		}
		
		$blnDoNotSwitchToEdit = true;
		$strCleanUrl = PageModel::findByPk($objPage->id)->getFrontendUrl();
		
		// TODO: !CREATE
		if(Input::get('act') == 'create')
		{
			$GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'] = false;
			$objDC->create();
		}
		
		// TODO: !DELETE
		if(Input::get('act') == 'delete')
		{
			$objDC->delete();
		}
		// !CUT
		else if(Input::get('act') == 'cut')
		{
			$objDC->cut(true);
			
			ContaoController::redirect( $strCleanUrl );
		}
		// TODO: !CUT ALL
		else if(Input::get('act') == 'cutAll')
		{
			$arrClipboard = static::getSession()->get('CLIPBOARD');
			if (is_array($arrClipboard[$strTable]['id']))
			{
				foreach($arrClipboard[$strTable]['id'] as $id)
				{
					$objDC->intId = $id;
					$objDC->cut(true);
					Input::setGet('pid', $id);
					Input::setGet('mode', 1);
				}
			}
						
			ContaoController::redirect( $strCleanUrl );
		}
		// TODO: !COPY
		else if(Input::get('act') == 'copy')
		{
			$intNew = $objDC->copy(true);
			
			// set the tstamp column to 0 when switchToEdit is in use
			$objNew = Database::getInstance()->prepare("SELECT id,tstamp FROM ".$objDC->table." WHERE id=?")->limit(1)->execute($intNew);
			if($objNew->tstamp > 0 && Input::get('switchToEdit') != '')
			{
				Database::getInstance()->prepare("UPDATE ".$objDC->table." %s WHERE id=?")->set(array('tstamp'=>0))->execute($intNew);
			}
			
			if(Input::get('switchToEdit') || Input::get('jumpto') > 0)
			{
				$arrClipboard[$strTable] = array
				(
					'id' 		=> $intNew,
					'mode' 		=> 'copy',
				);

				// set the clipboard helper to avoid that the DCA deletes the regular clipboard session
				$objSession->set('CLIPBOARD_HELPER',$arrClipboard);
				
				// reload the page to make the session take effect
				if($blnDoNotSwitchToEdit)
				{
					ContaoController::reload();
				}
			}
			
			ContaoController::redirect( $strCleanUrl );
		}
		// TODO: !COPY ALL
		else if(Input::get('act') == 'copyAll')
		{
			#$objDC->copyAll();
			$arrClipboard = static::getSession()->get('CLIPBOARD');

			if (is_array($arrClipboard[$strTable]['id']))
			{
				foreach($arrClipboard[$strTable]['id'] as $id)
				{
					$objDC->intId = $id;
					$id = $objDC->copy(true);
					Input::setGet('pid', $id);
					Input::setGet('mode', 1);
				}
			}
						
			ContaoController::redirect( $strCleanUrl );
		}
		// TODO: !EDIT ALL, OVERRIDE ALL
		else if(Input::get('act') == 'editAll')
		{
			
		}	
	
		// TODO: !PASTE set the clipboard session
		else if(Input::get('act') == 'paste')
		{
			$reload = false;
			$arrClipboard = $objSession->get('CLIPBOARD');
			
			if(count($arrClipboard[$strTable]) < 1)
			{
				$reload = true;
			}
			
			$ids = Input::get('id');
			
			$arrCurrent = $objSession->get('CURRENT');
			if(count($arrCurrent['IDS']) > 0 && is_array($arrCurrent['IDS']))
			{
				$ids = $arrCurrent['IDS'];
			}
			
			$arrClipboard[$strTable] = array
			(
				'id' 		=> $ids,
				'mode' 		=> Input::get('mode'),
			);
			
			$objSession->set('CLIPBOARD',$arrClipboard);
			
			// set the clipboard helper to avoid that the DCA deletes the regular clipboard session
			$objSession->set('CLIPBOARD_HELPER',$arrClipboard);
			
			if($reload)
			{
				ContaoController::reload();
			}
		}
		
		// TODO: !CLIPBOARD clear
		if(strlen($strTable) > 0 && Input::get('clear_clipboard'))
		{
			$arrSession = $objSession->get('CLIPBOARD');
			
			$arrSession[$strTable] = array();
			
			$objSession->set('CLIPBOARD',$arrSession);
			$objSession->set('CLIPBOARD_HELPER',$arrSession);
			$objSession->set('CURRENT',array());
			
			
			ContaoController::redirect( $strCleanUrl );
		}
	}
	
	
	/**
	 * Create submit widget object and return it
	 * @param string
	 * @param string
	 * @return object
	 */
	public function getSubmit($strId,$strName)
	{
		$arr = array
		(
			'id'	=> $strId.'_'.$strName,
			'name'	=> 'save', 
			'strName' => 'save',
			'value' => $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_'.$strName] ?: $strName,
			'label'	=> $GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG_FRONTEDIT']['MSC']['submit_'.$strName] ?: ucfirst($strName),
			'class' => 'submit',
			'tableless' => true,
		);
		
		return new FormSubmit($arr);
	}
	
	
	/**
	 * Generate the paste into button array
	 * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getPasteAfterButton($arrRow,$strTable,$arrClipboard=array())
	{
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByModule( ModuleModel::findByPk(Input::get('mod')) );
		if(!$objCC)
		{
			return '';
		}
		
		$objModule = $objCC->getOrigin();
		$objMultilanguage = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage;
		$strLanguage = str_replace('-','_',$objMultilanguage->getActiveFrontendLanguage());
		$strAlias = $objCC->getCustomElement()->get('alias');
		$strAliasField = $objCC->getAliasField();
		
		if(count($arrClipboard) < 1)
		{
			$objSession = static::getSession();
			$arrSession = $objSession->get('CLIPBOARD_HELPER');
			$arrClipboard = $arrSession[$strTable];
		}
		
		$ext = version_compare(VERSION, '4','>=') ? 'svg' : 'gif';
		$icon = 'pasteafter.'.$ext;	
		
		$image = Image::getHtml($icon, sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $arrRow['id']));
		#$image = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteinto'][1], $objRow->id));
		
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$icon = 'pasteafter_.'.$ext;
			$html = $image = Image::getHtml($icon);
		}
		else
		{
			$href = Functions::addToUrl('&amp;do='.$strAlias.'&amp;table='.$strTable.'&act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : ''));
			
			// switchToEdit
			if($objModule->customcatalog_jumpTo > 0 && ($GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'] || $objModule->customcatalog_edit_switchToEdit) )
			{
				$href = Functions::addToUrl('switchToEdit=1&jumpto='.$objModule->customcatalog_jumpTo, $href);
			}
			
			// add the request token
			if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'] && Input::get('rt') == '')
			{
				$href = Functions::addToUrl('rt='.REQUEST_TOKEN ,$href);
			}
			
			// multilanguage, add the langpid
			if($objCC->get('multilanguage'))
			{
				$langpid = $objMultilanguage->getBaseRecordId($arrRow['id'],$strTable,$strLanguage);
				$href = Functions::addToUrl('langpid='.$langpid,$href);
			}

			$html = '<a href="'.$href.'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $arrRow['id'])).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> $icon,
			'icon_html' => $image,
		);
		
		return $arrReturn;
	}
	
	
	/**
	 * Generate the paste into button array
	 * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getPasteIntoButton($arrRow,$strTable,$arrClipboard=array())
	{
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByModule( ModuleModel::findByPk(Input::get('mod')) );
		if(!$objCC)
		{
			return '';
		}
		
		$objModule = $objCC->getOrigin();
		$objMultilanguage = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage;
		$strLanguage = str_replace('-','_',$objMultilanguage->getActiveFrontendLanguage());
		$strAlias = $objCC->getCustomElement()->get('alias');
		
		if(count($arrClipboard) < 1)
		{
			$objSession = static::getSession();
			$arrSession = $objSession->get('CLIPBOARD_HELPER');
			$arrClipboard = $arrSession[$strTable];
		}
		
		$ext = version_compare(VERSION, '4','>=') ? 'svg' : 'gif';
		$icon = 'pasteafter.'.$ext;	
		
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image = Image::getHtml($icon, sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteinto'][1], $arrRow['id']));
		
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$icon = 'pasteinto_.'.$ext;
			$html = $image = Image::getHtml($icon);
		}
		else
		{
			$href = Functions::addToUrl('&amp;do='.$strAlias.'&amp;table='.$strTable.'act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : ''));
			
			// switchToEdit
			if($objModule->customcatalog_jumpTo > 0 && ($GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'] || $objModule->customcatalog_edit_switchToEdit) )
			{
				$href = Functions::addToUrl('switchToEdit=1&jumpto='.$objModule->customcatalog_jumpTo, $href);
			}
			
			// add the request token
			if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'] && Input::get('rt') == '')
			{
				$href = Functions::addToUrl('rt='.REQUEST_TOKEN ,$href);
			}
			
			// multilanguage, add the langpid
			if($objCC->get('multilanguage'))
			{
				$langpid = $objMultilanguage->getBaseRecordId($arrRow['id'],$strTable,$strLanguage);
				$href = Functions::addToUrl('langpid='.$langpid,$href);
			}
			
			$html = '<a href="'.$href.'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $arrRow['id'])).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> $icon,
			'icon_html' => $image,
		);
		
		return $arrReturn;	
	}
	
	
	/**
	 * Generate the cut button
	  * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getCutButton($arrRow,$strTable,$arrClipboard=array())
	{
		if(count($arrClipboard) < 1)
		{
			$objSession = static::getSession();
			$arrSession = $objSession->get('CLIPBOARD');
			$arrClipboard = $arrSession[$strTable];
		}
		
		$ext = version_compare(VERSION, '4','>=') ? 'svg' : 'gif';
		$icon = 'cut.'.$ext;	
		
		$image = '';
		if(version_compare(VERSION, '4','>='))
		{
			$image = Image::getHtml('cut.svg', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['cut'][1], $arrRow['id']));
		}
		else
		{
			$image = Image::getHtml('cut.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['cut'][1], $arrRow['id']));
		}	
		
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$html = '';
			$icon = 'cut_.'.$ext;
			$html = Image::getHtml($icon);
			
		}
		else
		{
			$href = 'act=paste&amp;mode=cut';
			$html = '<a href="'.$href.'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['cut'][1], $arrRow['id'])).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> $icon, #( version_compare(VERSION, '4','>=') ? 'cut.svg' : 'cut.gif' ),
			'icon_html' => $image,
		);
		
		return $arrReturn;	
	}

	
	/**
	 * Generate toggle visibility button
	 * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getToggleVisibilityButton($arrRow,$strTable,$arrClipboard=array())
	{
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByModule( ModuleModel::findByPk(Input::get('mod')) );
		if(!$objCC)
		{
			return '';
		}
		
		if (Input::get('tid'))
		{
			$this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
			ContaoController::redirect( ContaoController::getReferer() );
		}
		
		
		$strPublishedField = $objCC->getPublishedField();
		
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image_on = Image::getHtml('visible.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $arrRow['id']));
		$image_off =  Image::getHtml('invisible.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $arrRow['id']));
		$icon_on = 'system/themes/default/images/visible.gif';
		$icon_off = 'system/themes/default/images/invisible.gif';
		
		if(version_compare(VERSION, '4', '>='))
		{
			$image_on = Image::getHtml('visible.svg', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $arrRow['id']));
			$image_off =  Image::getHtml('invisible.svg', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $arrRow['id']));
			$icon_on = 'system/themes/flexible/icons/visible.svg';
			$icon_off = 'system/themes/flexible/icons/invisible.svg';
		}
		
		// Check permissions AFTER checking the tid, so hacking attempts are logged
		#if (!$this->User->isAdmin && !$this->User->hasAccess('create', 'pct_customcatalogsp'))
		#{
		#	return '';
		#}
		
		$image = $image_on;
		if (!$arrRow[$strPublishedField])
		{
			$image = $image_off;
		}
			
		$href = 'tid='.$arrRow['id'].'&amp;state='.($arrRow[$strPublishedField] ? '' : 1);
		
		$attributes = array
		(
			'onclick="CC_FrontEdit.toggleVisibility(this); return false;"',
			'data-state="'.($arrRow[$strPublishedField] ? '' : 1).'"',
			'data-icon="'.$icon_on.'"',
			'data-icon-disabled="'.$icon_off.'"',
			'data-table="'.$objCC->getTable().'"',
			'data-field="'.$strPublishedField.'"',
		);
		
		$arrReturn = array
		(
			'html' 	=> '',
			'href'	=> $href,
			'icon'	=> $arrRow[$strPublishedField] ? 'visible.gif' : 'invisible.gif',
			'icon_html' => $image,
			'attributes' => implode(' ', $attributes)
		);
		
		return $arrReturn;	
	}
	
	
	/**
	 * Toggle the published setting of an entry
	 * @param integer
	 * @param boolean
	 */
	protected function toggleVisibility($intId, $blnVisible)
	{
		$strTable = Input::get('table');
		
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByModule( ModuleModel::findByPk(Input::get('mod')) );
		if(!$objCC)
		{
			return;
		}
		
		$strField = $objCC->getPublishedField();
		
		// Check permissions to edit
		Input::setGet('id', $intId);
		Input::setGet('act', 'toggle');
		
		// Check permissions to publish
		#if (!$this->User->isAdmin && !$this->User->hasAccess($strTable.'::'.$strField, 'alexf'))
		#{
		#   $this->log('Not enough permissions to publish/unpublish item ID "'.$intId.'"', $strTable.' toggleVisibility', TL_ERROR);
		#   $this->redirect('contao/main.php?act=error');
		#}

		#$objVersions = new \Versions($strTable, $intId);
		#$objVersions->initialize();
		
		// Trigger the save_callback
		if (is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['save_callback']))
		{
		   foreach ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['save_callback'] as $callback)
		   {
		   		$objCaller = new $callback[0];
		   		$blnVisible = $objCaller->{$callback[1]}($blnVisible, $this);
		   }
		}
		
		// Update the database
		Database::getInstance()->prepare("UPDATE ".$strTable." %s WHERE id=?")->set(array('tstamp'=>time(),$strField=>$blnVisible ? '':1))->execute($intId);

		#$objVersions->create();
		
		System::log('A new version of record "'.$strTable.'.id='.$intId.'" has been created', $strTable.' toggleVisibility()', TL_GENERAL);
	}	
}