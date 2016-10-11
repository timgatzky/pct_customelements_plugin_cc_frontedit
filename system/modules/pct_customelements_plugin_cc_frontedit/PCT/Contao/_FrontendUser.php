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
namespace PCT\Contao;

/**
 * Class file
 * FrontendUser
 * Make the regular Contao FrontendUser class more flexible
 */
class _FrontendUser
{
	/**
	 * Initialize the object
	 */
	public function __construct($objUser, $arrConditions=array())
	{
	   if($objUser === null)
	   {
		   return null;
	   }
	   
	   foreach($objUser->getData() as $key => $val)
	   {
		  $this->{$key} = $val;
	   }
	   
	   $this->filemounts = $this->get('filemounts',$arrConditions);
	   $this->pagemounts = $this->get('pagemounts',$arrConditions);;
	   
	   return $this;
	}
	
	
	/**
	 * Return the filemounts
	 * @param array
	 * @return array
	 */
	public function filemounts($arrConditions=array())
	{
		return $this->get('filemounts',$arrConditions);
	}
	
	
	/**
	 * Return the pagemounts
	 * @param array
	 * @return array
	 */
	public function pagemounts($arrConditions=array())
	{
		return $this->get('pagemounts',$arrConditions);
	}
	
	
	/**
	 * Returns a value merged with similar value name from the member group table
	 * @param string
	 * @return mixed
	 */
	public function get($strKey,$arrConditions=array())
	{
		if(count($arrConditions) > 0)
		{
			foreach($arrConditions as $key => $condition)
			{
				if($this->{$key} != $condition)
				{
					$this->{$strKey} = null;
				}
			}
		}
		
		$arrOptions = array();
		if(count($arrConditions) > 0)
		{
			foreach($arrConditions as $key => $condition)
			{
				$arrOptions['column'][] = $key.'='.$condition;
			}
		}
		
		// merge with member groups
		$objMemberGroup = \MemberGroupModel::findMultipleByIds($this->groups,$arrOptions);
		if($objMemberGroup !== null)
		{
			$value =  deserialize($this->{$strKey});
			while($objMemberGroup->next())
			{
				if($objMemberGroup->{$strKey})
				{
					$var = deserialize($objMemberGroup->{$strKey});
					// merge arrays
					if( is_array($var) && is_array($value) )
					{
						$this->{$strKey} = array_unique(array_merge($var, $value));
					}
					else
					{
						$this->{$strKey} = $objMemberGroup->{$strKey};
					}
				}
			}
		}
		
		return $this->{$strKey};
	}
	
	
	/**
	 * Check if user has access to the groups
	 * @param array
	 */
	public function hasGroupAccess($arrGroups)
	{
		if(count($arrGroups) < 1 || empty($arrGroups) || !$this->groups)
		{
			return false;
		}
	
		if(!is_array($arrGroups))
		{
			$arrGroups = explode(',', $arrGroups);
		}
				
		if( empty( array_intersect( $arrGroups, deserialize($this->groups) )))
		{
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * @inherit doc
	 */
	public function hasAccess($strField,$arr)
	{
		$objTester = \BackendUser::getInstance();
		$objTester->isAdmin = 0;
		
		// pass variables
		foreach($this as $key => $val)
		{
			$objTester->{$key} = $val;
		}
		
		return $objTester->hasAccess($strField,$arr);
		
	}
}