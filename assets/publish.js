jQuery(document).ready(function() {
	$ = jQuery;
	root = $('h6 a').attr('href');

	$('pre').load(root + '/symphony/extension/sitemap_xml/raw/');

	$('a[rel=external]').live("click", function() {
		window.open("view-source:" + $(this).attr('href'));
		return false;
	});
});