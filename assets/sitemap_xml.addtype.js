Symphony.Language.add({
	'Request failed. Try again.': false,
	'Processing...': false, 
	'Complete!': false
});

jQuery(function($){

	var _ = Symphony.Language.get;
	var fieldset = $('.add_pagetype'),
		status = $('<span />'),
		gif = $('<img />'),
		form = $('form');

	if (!fieldset.length) return;

	fieldset.append(status).find('button').click(function(e){
		e.preventDefault();
		
		var page_type = fieldset.find('input').val(),
			self = $(this),
			page = fieldset.find('select').val();
		
		if (page_type == false || page == null) {
			alert('Please fill out both fields');
			return;
		}
		
		self.attr('disabled', 'disabled');
		status.append(gif.attr('src', Symphony.WEBSITE + '/extensions/sitemap_xml/assets/ajax-loader.gif'));
    	
		var data = {addtype: {page_type: page_type, page: page}, 'action[add_pagetype]': 'doIt!'};
		
		$.ajax({
			url: window.location.href,
			data: data,
			success: function(post){
				self.attr('disabled', null);
				return status.text(_('Complete!'));
		},
			error: function(){
				self.attr('disabled', null);
				status.text(_(
					'Request failed. Try again.'
				));
			}
		});
		
		return false;	
	});
});