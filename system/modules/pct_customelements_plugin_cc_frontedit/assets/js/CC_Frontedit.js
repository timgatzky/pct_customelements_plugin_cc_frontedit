


/**
 * CustomCatalog FrontEdit class
 */
var CC_FrontEdit = 
{
	/**
	 * Trigger the select all option
	 * @param element
	 */
	toggleCheckboxes : function(elem)
	{
		var module = jQuery(elem).data('module');
		jQuery('.checkbox[data-module='+module+']').prop('checked',jQuery(elem).prop('checked'));
	},


	/**
	 * Toggle visibility
	 * @param element
	 */
	toggleVisibility : function(elem)
	{
		elem = jQuery(elem);
		var image = elem.find('img');
		
		if(elem.data('state') == 1)
		{
			elem.find('img').prop('src',elem.data('icon'));
			elem.data('state',0);
		}
		else
		{
			elem.find('img').prop('src',elem.data('icon-disabled'));
			elem.data('state',1);
		}
		
		jQuery.ajax(
		{
			url: location.href,
			data: {'tid':elem.data('id'), state:elem.data('state'), table:elem.data('table')}
		});
		
		return false;
	},
	
	
	/**
	 * Save the scrolloffset
	 */
	getScrollOffset : function()
	{
		jQuery.ajax(
		{
			method: 'POST',
			url: location.href,
			data: {ajax:1, scrollOffset:jQuery(window).scrollTop()}
		});
	},
	
	
	/**
	 * Contaos backend class has certain methods that should be only accessible when user is logged on to the back end
	 * @param string	The Method name called
	 * @param object	The parameters of the method
	 */
	backend : function(objData)
	{
		if(typeof(objData) === 'undefined' || typeof(objData) === 'function')
		{
			return objData;
		}
		
		var method = objData.method;
		var func = objData.func;
		var params = objData.params;
		var errors = objData.errors;
		
		if(this.hasOwnProperty(method))
		{
			if(params.length > 0)
			{
				jQuery(this).trigger(method,params);
			}
			else
			{
				jQuery(this).trigger(method);
			}
		}
		else
		{
			if(typeof(Contao) === 'undefined' || typeof(Backend) === 'undefined')
			{
				alert(errors.be_user_not_logged_in);
			}
		}
	}
};
