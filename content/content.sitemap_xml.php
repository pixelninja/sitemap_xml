<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.datasourcemanager.php');
	
	Class ContentExtensionSitemap_XmlSitemap_xml extends AdministrationPage{
		
		private $type_index = null;
		private $type_global = null;
		private $type_lastmod = null;
		private $type_changefreq = null;

		protected static $dsm = null;

		public function __construct(Administration &$parent){
			parent::__construct($parent);
		}
		
		public function view() {	
			$this->setPageType('index');
			$this->setTitle(__('Sitemap XML Generator'));
			$this->appendSubheading(__('Sitemap XML Generator'), '<a class="raw" href="'.URL.'/symphony/extension/sitemap_xml/raw/" rel="source">View raw</a>');

			$sitemap_xml = new XMLElement('div', null, array('class'=>'sitemap'));
			$pre = new XMLElement('pre');

			$sitemap_xml->appendChild($pre);
			$this->Contents->appendChild($sitemap_xml);
		}
	}
?>