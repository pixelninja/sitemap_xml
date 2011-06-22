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
			// fetch all pages
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
			$datasources = Symphony::Database()->fetch("SELECT * FROM `tbl_sitemap_xml`");
			
			// get values from config: remove spaces, remove any trailing commas and split into an array
			$this->type_index = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('index_type', 'sitemap_xml')), ','));
			$this->type_global = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('global', 'sitemap_xml')), ','));
			$this->type_lastmod = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('lastmod', 'sitemap_xml')), ','));
			$this->type_changefreq = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('changefreq', 'sitemap_xml')), ','));			
				
			// supplement list of pages with additional meta data
			foreach($pages as $page) {
				$page_types = Symphony::Database()->fetchCol('type', "SELECT `type` FROM `tbl_pages_types` WHERE page_id = '".$page['id']."' ORDER BY `type` ASC");
				
				$page['url'] = '/' . Administration::instance()->resolvePagePath($page['id']);
				$page['types'] = $page_types;
				
				$page['is_home'] = (count(array_intersect($page['types'], $this->type_index))) ? true : false;				
				$page['is_global'] = (count(array_intersect($page['types'], $this->type_global)) > 0) ? true : false;
				
				// Set priority level
				foreach($page['types'] as $type) {
					if ($type == 'high') 	$page['priority'] = '1.00';
					elseif ($type == 'mid')  $page['priority'] = '0.50';
					elseif ($type == 'low')  $page['priority'] = '0.10';
					elseif (is_numeric($type)) $page['priority'] = $type;
				}
				
				$this->_pages[] = $page;
			}






			$this->setPageType('index');
			$this->setTitle(__('Sitemap XML Generator'));
			$this->appendSubheading(__('Sitemap XML Generator'), '<a class="raw" href="'.URL.'" rel="source">View raw</a>');

			$sitemap_xml = new XMLElement('div', null, array('class'=>'sitemap'));
			$pre = new XMLElement('pre');
			$xml = new XMLElement('xml', null, array('version'=>'1.0','encoding'=>'UTF-8'));
			$urlset = new XMLElement('urlset', null, array(
															  'xmlns'=>'http://www.sitemaps.org/schemas/sitemap/0.9',
															  'xmlns:xsi'=>'http://www.w3.org/2001/XMLSchema-instance',
															  'xsi:schemaLocation'=>'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'
														   ));


			foreach($this->_pages as $page) {
				// Display the home/index page
				if ($page['is_home'] == true) {
					$url = new XMLElement('url');
					$loc = new XMLElement('loc', URL.$page['url'].'/');
					$lastmod = new XMLElement('lastmod', $this->type_lastmod[0]);
					$changefreq = new XMLElement('changefreq', $this->type_changefreq[0]);
					$priority = new XMLElement('priority', '1');

					$url->appendChild($loc);
					$url->appendChild($lastmod);
					$url->appendChild($changefreq);
					$url->appendChild($priority);
					$urlset->appendChild($url);
				}

				if ($page['is_global'] == true && $page['is_home'] == false) {
					$url = new XMLElement('url');
					$loc = new XMLElement('loc', URL.$page['url'].'/');
					$lastmod = new XMLElement('lastmod', $this->type_lastmod[0]);
					$changefreq = new XMLElement('changefreq', $this->type_changefreq[0]);
					
					if($page['priority'] == '1.00') 	$priority = new XMLElement('priority', '1');
					elseif($page['priority'] == '0.50') $priority = new XMLElement('priority', '0.50');
					elseif($page['priority'] == '0.10') $priority = new XMLElement('priority', '0.10');
					elseif(is_numeric($page['priority'])) $priority = new XMLElement('priority', $page['priority']);
					else $priority = new XMLElement('priority', '0.50');

					$url->appendChild($loc);
					$url->appendChild($lastmod);
					$url->appendChild($changefreq);
					$url->appendChild($priority);
					$urlset->appendChild($url);

				}

				// Display associated entries from selected datasources
				/*if (!empty($datasources)) {
					$dsm = new DatasourceManager(Administration::instance());
					
					$params = array();
					foreach($datasources as $datasource) {
						if($datasource['page_id'] == $page['id']) {
							$ds = $dsm->create($datasource['datasource_handle'], $params);
							$results = $ds->grab($params);
	
							if($results instanceof XMLElement) {
								$xml = $results->generate(true);
								$doc = DOMDocument::loadXML($xml);
								
								$xpath = new DOMXPath($doc);
								
								$expression = $datasource['relative_url'];
								$page_url = URL . $page['url'];
								$priority = number_format($page['priority'] - '0.20', 2, '.', ',');
								$replacements = array();
								
								foreach($xpath->query('//entry') as $entry) {
									preg_match_all('/\{[^\}]+\}/', $expression, $matches);
									
									foreach($matches[0] as $match) {
										$result = $xpath->evaluate('string(' . trim($match, '{}') . ')', $entry);
										
										if(!is_null($result)) {
											$replacements[$match] = trim($result);
										}else{
											$replacements[$match] = '';
										}
									}
									$value = str_replace(array_keys($replacements),array_values($replacements),$expression);
										
									if(substr($value, 0, 1) != '/') {
										$value = '/'.$value;
									}
									if(substr($value, -1) != '/') {
										$value = $value.'/';
									}
									
									$url = $page_url . $value;




									$url = new XMLElement('url');
									$loc = new XMLElement('loc', $url);
									$lastmod = new XMLElement('lastmod', $this->type_lastmod[0]);
									$changefreq = new XMLElement('changefreq', $this->type_changefreq[0]);
									$priority = new XMLElement('priority', $priority);

									$url->appendChild($loc);
									$url->appendChild($lastmod);
									$url->appendChild($changefreq);
									$url->appendChild($priority);
									$urlset->appendChild($url);
								}
								
							}
						}
					}
				}*/
			}
	 
			
			$xml->appendChild($urlset);
			$pre->appendChild($xml);
			$sitemap_xml->appendChild($pre);


			$this->Contents->appendChild($sitemap_xml);
		}
	}
?>