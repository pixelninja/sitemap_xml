Symphony.Language.add({
	'Request failed. Try again.': false,
	'Complete!': false
});

jQuery(function($){

	var _ = Symphony.Language.get;
	var fieldset = $('.add_pagetype, .pin_to_page'),
		status = $('<span />').attr('class', 'status'),
		gif = $('<img />'),
		form = $('form');

	if (!fieldset.length) return;

	fieldset.append(status).find('button').click(function(e){
		var parent = $(this).closest('fieldset');
		var status = parent.find('span.status');
		status.text('');
		
		if(parent.attr('class') == 'settings add_pagetype') {
			var page_type = parent.find('input').val(),
				self = $(this),
				page = parent.find('select').val();
			
			if (page_type == false || page == null) {
				alert('Please fill out both fields');
				return false;
			}
			var data = {addtype: {page_type: page_type, page: page}, 'action[add_pagetype]': 'run'};
		}
		if(parent.attr('class') == 'settings pin_to_page') {
			var datasource = parent.find('select[name="pin[datasource]"]').val(),
				self = $(this),
				page = parent.find('select[name="pin[page]"]').val();
		
			var data = {pin: {datasource: datasource, page: page}, 'action[pin]': 'run'};
		}
				
		self.attr('disabled', 'disabled');
		status.prepend(gif.attr('src', Symphony.WEBSITE + '/extensions/sitemap_xml/assets/ajax-loader.gif'));
		
		$.ajax({
			url: window.location.href,
			data: data,
			success: function(){
				self.attr('disabled', null);
				return status.text(Symphony.Language.get('Complete!'));
		},
			error: function(){
				self.attr('disabled', null);
				status.text(Symphony.Language.get('Request failed. Try again.'));
			}
		});
		
		return false;	
	});
});