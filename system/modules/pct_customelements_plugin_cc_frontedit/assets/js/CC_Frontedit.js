
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
	toggleVisibility: function(elem)
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
	}
};
