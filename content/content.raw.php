<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.datasourcemanager.php');
	
	Class ContentExtensionSitemap_XmlRaw extends AdministrationPage{
	
		public $_pages = array();
		
		private $type_index = null;
		private $type_global = null;
		private $type_lastmod = null;
		private $type_changefreq = null;

		protected static $dsm = null;

		public function __construct(Administration &$parent){
			parent::__construct($parent);
		}
		
		function view($context){
			// fetch all pages
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
			$sitemap_ds = Symphony::Database()->fetch("SELECT * FROM `tbl_sitemap_xml`");
						
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
			
			// build the document
			// I'm not sure if this is the best method but I needed some way of displaying the code in pre tags, hence the entities
			$html  = '&lt;?xml version="1.0" encoding="UTF-8"?&gt;'."\n";
			$html .= '&lt;urlset'."\n";
			$html .= '  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
			$html .= '  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
			$html .= '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"&gt;'."\n\n";
			
			// iterate over each page
			foreach($this->_pages as $page) {
				// Display the home/index page
				if ($page['is_home'] == true) {
					$html .= '	&lt;url&gt;'."\n";
					$html .= '	  &lt;loc&gt;'.URL.$page['url'].'/&lt;/loc&gt;'."\n";
					$html .= '	  &lt;lastmod&gt;'.$this->type_lastmod[0].'&lt;/lastmod&gt;'."\n";
					$html .= '	  &lt;changefreq&gt;'.$this->type_changefreq[0].'&lt;/changefreq&gt;'."\n";
					$html .= '	  &lt;priority&gt;1.00&lt;/priority&gt;'."\n";
					$html .= '	&lt;/url&gt;';
				}
				// Display all other pages
				if ($page['is_global'] == true) {
					$html .= "\n".'	&lt;url&gt;'."\n";
					$html .= '	  &lt;loc&gt;'.URL.$page['url'].'/&lt;/loc&gt;'."\n";
					$html .= '	  &lt;lastmod&gt;'.$this->type_lastmod[0].'&lt;/lastmod&gt;'."\n";
					$html .= '	  &lt;changefreq&gt;'.$this->type_changefreq[0].'&lt;/changefreq&gt;'."\n";
					
					if($page['priority'] == '1.00') 	$html .= '	  &lt;priority&gt;1.00&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.50') $html .= '	  &lt;priority&gt;0.50&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.10') $html .= '	  &lt;priority&gt;0.10&lt;/priority&gt;'."\n";
					elseif(is_numeric($page['priority'])) $html .= '	  &lt;priority&gt;'.$page['priority'].'&lt;/priority&gt;'."\n";
					else $html .= '	  &lt;priority&gt;0.50&lt;/priority&gt;'."\n";
					
					$html .= '	&lt;/url&gt;';
				}
				
				
				
				$dsm = new DatasourceManager(Administration::instance());
				$datasources = $dsm->listAll(); 
				
				foreach($sitemap_ds as $ds) {
				
					var_dump($datasources);
					
					while($ds['datasource_handle'] == $datasources['handle']) {
					
					}
					
				
				}
				
				
				exit;
				
				
				
				// Display associated entries from selected datasources
				/*if (!empty($ds_query)) {
				
				
					$dsm = new DatasourceManager(Administration::instance());
					$datasources = $dsm->listAll(); 
					
					
					
					$params = array();
					foreach($datasources as $datasource) {
						$ds = $dsm->create($datasource['handle'], $params);
						
						if($ds['dsParamROOTELEMENT'] == $ds_query['datasource_handle']) {
							$results = $ds->grab($params);
							var_dump($ds);
							if($results instanceof XMLElement) {
								$xml = $results->generate(true);
								$doc = DOMDocument::loadXML($xml);
								
								$xpath = new DOMXPath($doc);
								
								foreach($xpath->query('//entry') as $entry) {
									
									//$p = $xpath->evaluate('string(name)', $entry);
									//var_dump($p);
									
									
						
									$html .= "\n".'	&lt;url&gt;'."\n";
									$html .= '	  &lt;loc&gt;'.URL.$page['url'].'&lt;/loc&gt;'."\n";
									$html .= '	  &lt;lastmod&gt;'.$this->type_lastmod[0].'&lt;/lastmod&gt;'."\n";
									$html .= '	  &lt;changefreq&gt;'.$this->type_changefreq[0].'&lt;/changefreq&gt;'."\n";
									
									$html .= '	  &lt;priority&gt;0.50&lt;/priority&gt;'."\n";
									$html .= '	&lt;/url&gt;';
									
								
								}
								
							}
						}
					}
					
								exit;


				}
				*/
				
				//var_dump($page);
				//exit;
				
				
				
				
			}
			
			$html .= "\n\n".'&lt;/urlset&gt;';
			echo $html;
			
			// File path
			$custom_file = getcwd() . '/sitemap.xml';
			# Open the file and reset it, to recieve the new code
			$open_file = fopen($custom_file, 'w');
			// Replace html entities with ASCII 
			$valid_xml = str_replace('&lt;', '<', str_replace('&gt;', '>', $html));
			
			# Write xml to file, then close
			fwrite($open_file, $valid_xml);
			fclose($open_file);
			
			//stop the loading of Symphony core
			die;
		}
	}