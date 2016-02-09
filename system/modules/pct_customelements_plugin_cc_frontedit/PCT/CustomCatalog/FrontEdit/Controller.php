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
use \PCT\CustomElements\Helper\Functions as Functions;
use \PCT\CustomElements\Helper\ControllerHelper as ControllerHelper;

/**
 * Class file
 * Controller
 */
class Controller extends \PCT\CustomElements\Models\Model
{
	/**
	 * Add back end assets to the front end
	 */
	public function addAssets()
	{
		global $objPage;
		if(!$objPage->hasJQuery)
		{
			$GLOBALS['TL_JAVASCRIPT'][] = '//code.jquery.com/jquery-' . $GLOBALS['TL_ASSETS']['JQUERY'] . '.min.js';
		}
		
		$strLocale = 'var Contao={'
				. 'theme:"' . \Backend::getTheme() . '",'
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
		$GLOBALS['TL_HEAD'][] = '<script type="text/javascript">'.$strLocale.'</script>';
		
		// css
		$objCombiner = new \Combiner();
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
		$objCombiner = new \Combiner();
	    $objCombiner->add('assets/mootools/core/' . $GLOBALS['TL_ASSETS']['MOOTOOLS'] . '/mootools.js', $GLOBALS['TL_ASSETS']['MOOTOOLS']);
	    $objCombiner->add('assets/mootools/colorpicker/'. $GLOBALS['TL_ASSETS']['COLORPICKER'] .'/js/mooRainbow.js', $GLOBALS['TL_ASSETS']['COLORPICKER']);
	    $objCombiner->add('assets/mootools/chosen/chosen.js');
	    $objCombiner->add('assets/mootools/stylect/js/stylect.js');
	    $objCombiner->add('assets/mootools/simplemodal/'. $GLOBALS['TL_ASSETS']['SIMPLEMODAL'] .'/js/simplemodal.js', $GLOBALS['TL_ASSETS']['SIMPLEMODAL']);
	    $objCombiner->add('assets/mootools/datepicker/'. $GLOBALS['TL_ASSETS']['DATEPICKER'] .'/datepicker.js', $GLOBALS['TL_ASSETS']['DATEPICKER']);
	    $objCombiner->add('assets/mootools/mootao/Mootao.js');
	    $objCombiner->add('assets/contao/js/core-uncompressed.js');
		$GLOBALS['TL_HEAD'][] = '<script type="text/javascript" src="'.$objCombiner->getCombinedFile().'"></script>';
	   
	    $GLOBALS['TL_JAVASCRIPT'][] = PCT_CUSTOMELEMENTS_PLUGIN_CC_FRONTEDIT_PATH.'/assets/js/CC_FrontEdit.js';
	}
	
	
	/**
	 * Generates the operations buttons list by an active record data (database result) or an array
	 * @param object||array		DatabaseResult || Array
	 * @return string			Html output
	 */
	public function addButtonsToTemplateByRow($objTemplate, $varRow, $objConfig=null)
	{
		global $objPage;
		
		if(empty($varRow))
		{
			return '';
		}
				
		$objRow = null;
		if(is_array($varRow))
		{
			foreach($varRow as $k => $v)
			{
				$objRow->{$k} = $v;
			}
		}
		else if(is_object($varRow))
		{
			$objRow = $varRow;
		}
		
		// apply a config object
		if($objConfig !== null)
		{
			$this->setConfig($objConfig);
		}
		
		$objConfig = $this->getConfig();
		
		$objFunction = \PCT\CustomElements\Helper\Functions::getInstance();
		
		$objCC = $objConfig->customcatalog;
		$objModule = $objConfig->module;
		$arrDefaultDCA = \PCT\CustomElements\Plugins\CustomCatalog\Helper\DcaHelper::getDefaultDataContainerArray();
		$objMultilanguage = new \PCT\CustomElements\Plugins\CustomCatalog\Core\Multilanguage;
		
		$strAliasField = $objCC->getAliasField();
		$strAlias = $objCC->getCustomElement()->get('alias');
		$strTable = $objCC->getTable();
		$strLanguage = $objMultilanguage->getActiveBackendLanguage($strTable);
		
		\System::loadLanguageFile('tl_pct_customcatalog');
		\System::loadLanguageFile('tl_content');
		\System::loadLanguageFile($strTable);
		
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
		
		// Create a datacontainer
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper($strTable);
		$objDC->User = $objUser;
		
		$arrOperations = $arrDefaultDCA['list']['operations'];
		$arrListOperations = deserialize($objCC->get('list_operations'));
		
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
		
		$arrButtons = array();
		$i = 0;
		foreach($arrOperations as $key => $button)
		{
			if(in_array($key,$GLOBALS['PCT_CUSTOMCATALOG_FRONTEDIT']['ignoreButtons']))
			{
				continue;
			}
			
			$strJumpTo = \Controller::generateFrontendUrl( $objPage->row() );
			
			// overwrite the jumpTo page when editing should be done on a different page
			if($key == 'edit' && $objModule->customcatalog_jumpTo > 0)
			{
				$strJumpTo = \Controller::generateFrontendUrl( \PageModel::findByPk($objModule->customcatalog_jumpTo)->row() );
			}
			
			$href = (isset($button['href']) ? $button['href'] : '');
			// copy,cut button in mode 4,5,5.1 should call the paste button
			if(in_array($objCC->get('list_mode'),array(4,5,'5.1')))
			{
				if($key == 'copy')
				{
					$href = 'act=paste&amp;mode=copy';
				}
				else if($key == 'cut')
				{
					$href = 'act=paste&amp;mode=cut';
				}
			}
			
			$title = sprintf($button['label'][1],$objRow->id);
			$href = $objFunction->addToUrl($href.'&amp;do='.$strAlias.'&amp;table='.$strTable.'&amp;id='.$objRow->id.($objRow->pid > 0 ? '&amp;pid='.$objRow->pid : ''), $strJumpTo);
			// add the items parameter to the url
			$href = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.(strlen($strAliasField) > 0 ? $objRow->{$strAliasField} : $objRow->id) ,$href);
			// add the request token
			if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'])
			{
				$href = $objFunction->addToUrl('rt='.REQUEST_TOKEN ,$href);
			}
			// simulate a switchToEdit
			if($key == 'copy' && $objModule->customcatalog_jumpTo > 0 && $GLOBALS['TL_DCA'][$strTable]['config']['switchToEdit'])
			{
				$href = $objFunction->addToUrl('switchToEdit=1&jumpto='.$objModule->customcatalog_jumpTo, $href);
			}
			// multilanguage, add the langpid
			if($objCC->get('multilanguage'))
			{
				$langpid = $objMultilanguage->getBaseRecordId($row['id'],$strTable,$strLanguage);
				$href = $objFunction->addToUrl('langpid='.$langpid,href);
			}
			
			$linkImage = \Image::getHtml('system/themes/default/images/'.$button['icon'],$title);
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
				$button['html'] = \System::importStatic($button['button_callback'][0])->{$button['button_callback'][1]}($objRow->row(),$href,$title,$icon,$attributes,$strTable);
			}
			
			$arrButtons[$key] = $button;
			
			$i++;
		}
		
		// append the clipboard buttons
		if(\Input::get('act') == 'paste')
		{
			array_insert($arrButtons,count($arrButtons),array('paste_after' => $this->getPasteAfterButton($objRow->row(),$strTable) ));
			
			if($objCC->get('list_mode') == 5)
			{
				array_insert($arrButtons,count($arrButtons),array('paste_into'=> $this->getPasteIntoButton($objRow->row(),$strTable) ));
			}
		}
		
		// append the multiple select checkbox
		if(\Input::get('act') == 'select')
		{
			$html = '<input data-module="'.$objModule->id.'" id="ids_'.$objRow->id.'" class="tl_tree_checkbox checkbox" type="checkbox" value="'.$objRow->id.'" name="IDS[]">';
			$select = array('html'=>$html,'class'=>'select');
			array_insert($arrButtons,count($arrButtons),array('select'=>$select));
		}
		
		// Hook: Modify buttons
		if (isset($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons']) && count($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons']) > 0)
		{
			foreach($GLOBALS['CUSTOMCATALOG_FRONTEDIT_HOOKS']['getButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons,$this);
			}
		}
		
		$objTemplate->empty = (count($arrButtons) < 1 ? true : false);
		$objTemplate->module = $objModule;
		$objTemplate->config = $objConfig;
		$objTemplate->customcatalog = $objCC;
		$objTemplate->activeRecord = $objRow;
		$objTemplate->buttons = $arrButtons;
		
		return $objTemplate;
	}
	
	
	/**
	 * Simulate the switchToEdit function
	 * Handle the DC switchToEdit functionality as known from the backend
	 * @called from generatePage HOOK
	 */
	public function simulateSwitchToEdit()
	{
		$arrSession = \Session::getInstance()->get('CLIPBOARD_HELPER');
		
		$strTable = \Input::get('table');
		
		// !switchToEdit on CREATE
		if($arrSession[$strTable]['mode'] == 'create' && \Input::get('jumpto') > 0 && \Input::get('act') == 'edit')
		{
			// redirect to lister page
			if(\Input::get('switchToEdit') < 1)
			{
				$redirect = \Controller::generateFrontendUrl( \PageModel::findByPk(\Input::get('jumpto'))->row() );
			}
			// redirect to details page
			else
			{
				$objFunction = new \PCT\CustomElements\Helper\Functions;
				$parse = parse_url(\Environment::get('request'));
				$redirect = \Controller::generateFrontendUrl( \PageModel::findByPk(\Input::get('jumpto'))->row()).'?'.$parse['query'];
				
				$redirect = str_replace(array('switchToEdit=1','jumpto='.\Input::get('jumpto')), '', $redirect);
				
				// add the items parameter to the url
				if(!\Input::get($GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter']))
				{
					$redirect = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.\Input::get('id'),$redirect);
				}	
			}
			
			// remove session
			$arrSession[$strTable]['mode'] = 'on'.$arrSession[$strTable]['mode'];
			$arrSession[$strTable]['ref'] = \Controller::generateFrontendUrl( \PageModel::findByPk(\Input::get('jumpto'))->row() );
			
			\Session::getInstance()->set('CLIPBOARD_HELPER',$arrSession);
			// redirect to edit page
			\Controller::redirect($redirect);
		}
		
		// !switchToEdit on COPY
		else if($arrSession[$strTable]['mode'] == 'copy' && \Input::get('jumpto') > 0 && \Input::get('act') == 'copy')
		{
			$intNew = $arrSession[$strTable]['id'];
			$objFunction = new \PCT\CustomElements\Helper\Functions;
			$parse = parse_url(\Environment::get('request'));
			$redirect = $objFunction->addToUrl($parse['query'].'&id='.$intNew.'&act=edit&jumpto=&',\Controller::generateFrontendUrl( \PageModel::findByPk(\Input::get('jumpto'))->row() ) );
			// add/rewrite the items parameter to the url
			$redirect = $objFunction->addToUrl( $GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter'].'='.$intNew,$redirect);
			
			// remove session
			$arrSession[$strTable]['mode'] = 'on'.$arrSession[$strTable]['mode'];
			$arrSession[$strTable]['ref'] = \Controller::getReferer();
			
			\Session::getInstance()->set('CLIPBOARD_HELPER',$arrSession);
			
			// redirect to edit page
			\Controller::redirect($redirect);
		}
	}


	/**
	 * POST and GET action listener
	 * Apply operations
	 * called from generatePage Hook
	 */
	public function applyOperationsOnGeneratePage($objPage)
	{
		$strTable = \Input::get('table') ?: \Input::get('do');
		
		// return reader page when editing is not active
		if(\Input::get($GLOBALS['PCT_CUSTOMCATALOG']['urlItemsParameter']) && !\Input::get('act'))
		{
			return;
		}
		
		// check if the table is allowed to be edited
		if( strlen($strTable) < 1 || !\PCT\CustomCatalog\FrontEdit::isEditable($strTable) )
		{
			return;
		}
		
		// check request token 
		if(!$GLOBALS['TL_CONFIG']['disableRefererCheck'] && \Input::get('rt') != REQUEST_TOKEN)
		{
			header('HTTP/1.1 400 Bad Request');
			die_nicely('be_referer', 'Invalid request token. Please <a href="javascript:window.location.href=window.location.href">go back</a> and try again.');
		}
		
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
		
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findCurrent();
		
		if($objCC === null)
		{
			return;
		}
			
		if(!defined(CURRENT_ID)) {define(CURRENT_ID, \Input::get('id'));}
		
		// Set a user ID for versions
		$objUser = new \StdClass;
		$objUser->id = isset($GLOBALS['TL_CONFIG']['customcatalog_edit_admin']) ? $GLOBALS['TL_CONFIG']['customcatalog_edit_admin'] : 1;
		
		// Create a datacontainer
		$objDC = new \PCT\CustomElements\Helper\DataContainerHelper($objCC->getTable());
		$objDC->User = $objUser;
		
		$blnDoNotSwitchToEdit = true;
			
		// !CREATE
		if(\Input::get('act') == 'create')
		{
			$objDC->create();
		}
		
		// !DELETE
		if(\Input::get('act') == 'delete')
		{
			$objDC->delete();
		}
		// !CUT
		else if(\Input::get('act') == 'cut')
		{
			$objDC->cut(true);
			if($blnDoNotSwitchToEdit)
			{
				\Controller::redirect( \Controller::getReferer() );
			}
		}
		// !CUT ALL
		else if(\Input::get('act') == 'cutAll')
		{
			$arrClipboard = \Session::getInstance()->get('CLIPBOARD');
			if (is_array($arrClipboard[$strTable]['id']))
			{
				foreach($arrClipboard[$strTable]['id'] as $id)
				{
					$objDC->intId = $id;
					$objDC->cut(true);
					\Input::setGet('pid', $id);
					\Input::setGet('mode', 1);
				}
			}
						
			\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
		}
		// !COPY
		else if(\Input::get('act') == 'copy')
		{
			$intNew = $objDC->copy(true);
			
			if(\Input::get('switchToEdit') || \Input::get('jumpto') > 0)
			{
				$arrClipboard[$strTable] = array
				(
					'id' 		=> $intNew,
					'mode' 		=> 'copy',
				);

				// set the clipboard helper to avoid that the DCA deletes the regular clipboard session
				\Session::getInstance()->set('CLIPBOARD_HELPER',$arrClipboard);
				
				// reload the page to make the session take effect
				if($blnDoNotSwitchToEdit)
				{
					\Controller::reload();
				}
			}
			
			if($blnDoNotSwitchToEdit)
			{
				\Controller::redirect( \Controller::getReferer() );
			}
		}
		// !COPY ALL
		else if(\Input::get('act') == 'copyAll')
		{
			#$objDC->copyAll();
			$arrClipboard = \Session::getInstance()->get('CLIPBOARD');

			if (is_array($arrClipboard[$strTable]['id']))
			{
				foreach($arrClipboard[$strTable]['id'] as $id)
				{
					$objDC->intId = $id;
					$id = $objDC->copy(true);
					\Input::setGet('pid', $id);
					\Input::setGet('mode', 1);
				}
			}
						
			if($blnDoNotSwitchToEdit)
			{
				\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
			}
		}
		// !EDIT ALL, OVERRIDE ALL
		else if(\Input::get('act') == 'editAll')
		{
			
		}	
	
		// !PASTE set the clipboard session
		else if(\Input::get('act') == 'paste')
		{
			$reload = false;
			$objSession = \Session::getInstance();
			$arrClipboard = $objSession->get('CLIPBOARD');
			
			if(count($arrClipboard[$strTable]) < 1)
			{
				$reload = true;
			}
			
			$ids = \Input::get('id');
			
			$arrCurrent = $objSession->get('CURRENT');
			if(count($arrCurrent['IDS']) > 0 && is_array($arrCurrent['IDS']))
			{
				$ids = $arrCurrent['IDS'];
			}
			
			$arrClipboard[$strTable] = array
			(
				'id' 		=> $ids,
				'mode' 		=> \Input::get('mode'),
			);
			
			$objSession->set('CLIPBOARD',$arrClipboard);
			
			// set the clipboard helper to avoid that the DCA deletes the regular clipboard session
			$objSession->set('CLIPBOARD_HELPER',$arrClipboard);
			
			if($reload)
			{
				\Controller::reload();
			}
		}
		
		// !CLIPBOARD clear
		if(strlen($strTable) > 0 && \Input::get('clear_clipboard'))
		{
			$objSession = \Session::getInstance();
			$arrSession = $objSession->get('CLIPBOARD');
			
			$arrSession[$strTable] = array();
			
			$objSession->set('CLIPBOARD',$arrSession);
			$objSession->set('CURRENT',array());
			
			\Controller::redirect( \Controller::generateFrontendUrl($objPage->row()) );
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
		
		return new \FormSubmit($arr);
	}
	
	
	/**
	 * Generate the paste into button array
	 * @param array		Database Result array
	 * @param string	Tablename
	 * @return array
	 */
	public function getPasteAfterButton($arrRow,$strTable,$arrClipboard=array())
	{
		if(count($arrClipboard) < 1)
		{
			$arrSession = \Session::getInstance()->get('CLIPBOARD');
			$arrClipboard = $arrSession[$strTable];
		}
		
		$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		#$image = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteinto'][1], $objRow->id));
		
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$html = \Image::getHtml('pasteafter_.gif');
		}
		else
		{
			$href = Functions::addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : ''));
			$html = '<a href="'.$href.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id)).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> 'pasteafter.gif',
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
		if(count($arrClipboard) < 1)
		{
			$arrSession = \Session::getInstance()->get('CLIPBOARD');
			$arrClipboard = $arrSession[$strTable];
		}
			
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image = \Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteinto'][1], $objRow->id));
		
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$html = \Image::getHtml('pasteafter_.gif');
		}
		else
		{
			$href = Functions::addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : ''));
			$html = '<a href="'.$href.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id)).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> 'pasteinto.gif',
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
			$arrSession = \Session::getInstance()->get('CLIPBOARD');
			$arrClipboard = $arrSession[$strTable];
		}
			
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image = \Image::getHtml('cut.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['cut'][1], $objRow->id));
			
		$href = '';
		if( ($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $arrRow['id'])  || ($arrClipboard['mode'] == 'cutAll' && in_array($arrRow['id'], $arrClipboard['id'])) )
		{
			$html = \Image::getHtml('cut_.gif');
		}
		else
		{
			$href = 'act=paste&amp;mode=cut';
			$html = '<a href="'.$href.'" title="'.specialchars(sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['cut'][1], $objRow->id)).'">'.$image.'</a>';
		}
		
		$arrReturn = array
		(
			'html' 	=> $html,
			'href'	=> $href,
			'icon'	=> 'cut.gif',
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
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strTable);
		if(!$objCC)
		{
			return '';
		}
		
		if (\Input::get('tid'))
		{
			$this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
			\Controller::redirect( \Controller::getReferer() );
		}
		
		
		$strPublishedField = $objCC->getPublishedField();
		
		#$image = \Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['pasteafter'][1], $objRow->id));
		$image_on = \Image::getHtml('visible.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $objRow->id));
		$image_off =  \Image::getHtml('invisible.gif', sprintf($GLOBALS['TL_LANG']['PCT_CUSTOMCATALOG']['MSC']['toggle'][1], $objRow->id));
		$icon_on = 'system/themes/default/images/visible.gif';
		$icon_off = 'system/themes/default/images/invisible.gif';
		
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
	 * @param 
	 */
	protected function toggleVisibility($intId, $blnVisible)
	{
		$strTable = \Input::get('table');
		
		$objCC = \PCT\CustomElements\Plugins\CustomCatalog\Core\CustomCatalogFactory::findByTableName($strTable);
		if(!$objCC)
		{
			return;
		}
		
		$strField = $objCC->getPublishedField();
		
		// Check permissions to edit
		$objInput = \Input::getInstance();
		$objInput->setGet('id', $intId);
		$objInput->setGet('act', 'toggle');
		
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
		   		$blnVisible = $objCaller->$callback[1]($blnVisible, $this);
		   }
		}
		
		// Update the database
		\Database::getInstance()->prepare("UPDATE ".$strTable." %s WHERE id=?")->set(array('tstamp'=>time(),$strField=>$blnVisible ? '':1))->execute($intId);

		#$objVersions->create();
		
		\System::log('A new version of record "'.$strTable.'.id='.$intId.'" has been created', $strTable.' toggleVisibility()', TL_GENERAL);
	}
	
	
}
 