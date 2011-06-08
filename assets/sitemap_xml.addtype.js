Symphony.Language.add({
	'Request failed. Try again.': false,
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
		status.text('');
		var page_type = fieldset.find('input').val(),
			self = $(this),
			page = fieldset.find('select').val();
		
		if (page_type == false || page == null) {
			alert('Please fill out both fields');
			return;
		}
		
		self.attr('disabled', 'disabled');
		status.prepend(gif.attr('src', Symphony.WEBSITE + '/extensions/sitemap_xml/assets/ajax-loader.gif'));
    	
		var data = {addtype: {page_type: page_type, page: page}, 'action[add_pagetype]': 'run'};
		
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