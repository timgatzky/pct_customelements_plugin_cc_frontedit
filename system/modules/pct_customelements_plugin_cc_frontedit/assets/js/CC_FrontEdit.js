


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
	 * Scroll to a value
	 * @param integer||string
	 */
	scrollTo(value)
	{
		jQuery("html, body").animate({scrollTop: value}, 0);
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
	},
	
	
	/**
	 * Open an backend modal window
	 * @param string	Url to file
	 * Idea taken by contao core
	 */
	openModal : function(objData)
	{
		var type = 'file';
		if(objData.method == 'openModalBrowser')
		{
			var type = 'page';
		}
		var win = document.window;
		var url = objData.url;
		
		var M = new SimpleModal({
			'width': 768,
			'btn_ok': Contao.lang.close,
			'draggable': false,
			'overlayOpacity': .5,
			'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
			'onHide': function() { document.body.setStyle('overflow', 'auto'); }
		});
		M.addButton(Contao.lang.close, 'btn', function() {
			this.hide();
		});
		M.addButton(Contao.lang.apply, 'btn primary', function() {
			var frm = window.frames['simple-modal-iframe'],
				val, inp, i;
			if (frm === undefined) {
				alert('Could not find the SimpleModal frame');
				return;
			}

			// contao 4
			if( url.indexOf('context=file') > 0 )
			{
				inp = frm.document.getElementById('tl_listing').getElementsByTagName('input');
			}
			else
			{
				inp = frm.document.getElementById('tl_select').getElementsByTagName('input');
			}
			
			for (i=0; i<inp.length; i++) {
				if (inp[i].checked && !inp[i].id.match(/^reset_/)) {
					val = inp[i].get('value');
					break;
				}
			}
			
			if (!isNaN(val)) {
				val = '{{link_url::' + val + '}}';
			}
			
			win.document.getElementById(objData.field).value = val;
			this.hide();
		});
		M.show({
			'title': win.document.getElement('div.mce-title').get('text'),
			'contents': '<iframe src="' + url + '" name="simple-modal-iframe" width="100%" height="' + (window.getSize().y-180).toInt() + '" frameborder="0"></iframe>',
			'model': 'modal'
		});
	},
	// Little helper to open the modal window for textarea
	openModalInTextarea(field_name,objData)
	{
		objData.field = field_name;
		return this.openModal(objData);
	},

	
	/**
	 * Replace the preview image in a selector widget e.g. file selector widget
	 * @param object	
	 */
	replaceSelectorImage : function(objData)
	{
		var widget = jQuery('input[name="'+objData.field+'"]');
		if(widget.length < 1)
		{
			return false;
		}
		
		// input value
		jQuery('input[value="'+objData.currValue+'"]').attr('value',objData.newValue);
		
		var li = jQuery('#sort_'+objData.field+' li[data-id="'+objData.currValue+'"]');
		
		// <li>
		li.attr('data-id',objData.newValue);
		
		// img
		li.find('img').attr('src',objData.newSRC);
	},
	
	
	/**
	 * Insert a selector image
	 * @param object
	 */
	insertSelectorImage : function(objData)
	{
		var widget = jQuery('input[name="'+objData.field+'"]');
		if(widget.length < 1)
		{
			return false;
		}
		
		var li = jQuery('#sort_'+objData.field+' li[data-id="'+objData.currValue+'"]');
		if(li.length < 1)
		{
			jQuery('#sort_'+objData.field).append('<li data-id="'+objData.newValue+'"><img src="'+objData.newSRC+'" width="80" height="80"></li>');
		}
	},
	
	
	/**
	 * Re-replace an inserttag value. Since contao will replace all inserttags in the front end templates we must replace those with the original inserttag submitted
	 * @param object
	 */
	rereplaceInsertTags : function(objData)
	{
		jQuery('#ctrl_'+objData.field).attr('value',objData.newValue);
	}
};

