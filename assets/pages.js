jQuery(document).ready(function () {
	var form = jQuery('form');
	var select = jQuery('select[name = "with-selected"]');
	
	var option = {
		high: jQuery('<option value="high">High</option>'), 
	 	mid: jQuery('<option value="mid">Mid</option>'),
	 	low: jQuery('<option value="low">Low</option>')
	 }
	
	if (select.find('optgroup:first').length) {
		jQuery.each(option, function(key, value) {
			value.insertBefore(select.find('optgroup:first'));
		});
		
	} else {
		jQuery.each(option, function(key, value) {
			value.appendTo(select);
		});
	}
	
	form.bind('submit', function() {
		if (select.val() == 'high' || select.val() == 'mid' || select.val() == 'low') {
			console.log(select.val());
			
			
			return false;
		}
	});
});