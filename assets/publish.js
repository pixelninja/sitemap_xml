jQuery(document).ready(function() {
	$ = jQuery;
	
	root = $('h6 a').attr('href');
	$('pre').load(root + '/symphony/extension/sitemap_xml/raw/');
});