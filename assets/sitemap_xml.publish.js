jQuery(function($){
   	$.get(Symphony.Context.get('root') + '/symphony/extension/sitemap_xml/raw/', function(data) {
		$('pre').text(data).html();
	});
   	
	$('.actions').on("click", 'a', function () {
	    window.open($(this).attr('href'));
	    return false;
	})
});