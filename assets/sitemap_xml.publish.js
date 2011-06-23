jQuery(function($){

	$('pre').load(Symphony.WEBSITE + '/symphony/extension/sitemap_xml/raw/');
   
   
	$('a[rel=source]').live("click", function () {
	    window.open("view-source:" + $(this).attr('href'));
	    return false
	})


});