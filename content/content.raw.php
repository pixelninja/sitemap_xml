<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.datasourcemanager.php');
	
	Class ContentExtensionSitemap_XmlRaw extends AdministrationPage{
		
		private $type_index = null;
		private $type_global = null;
		private $type_lastmod = null;
		private $type_changefreq = null;

		//protected static $dsm = null;

		public function __construct(Administration &$parent){
			parent::__construct($parent);
		}
		
		
		function view(){
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
			
			// build the document
			// I know this is butt ugly, but I needed some way of building the document to work in <pre> tags, as it strips out
			// any < or > unless they're entities... So i build this here and use ajax to load the page...
			// If someone has a better idea please let me know!!!
			$html  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$html .= '<urlset'."\n";
			$html .= '  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
			$html .= '  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
			$html .= '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n\n";
						
			// iterate over each page
			foreach($this->_pages as $page) {
				// Display the home/index page
				if ($page['is_home'] == true) {
					$html .= '	<url>'."\n";
					$html .= '	  <loc>'.URL.$page['url'].'/</loc>'."\n";
					$html .= '	  <lastmod>'.$this->type_lastmod[0].'</lastmod>'."\n";
					$html .= '	  <changefreq>'.$this->type_changefreq[0].'</changefreq>'."\n";
					$html .= '	  <priority>1.00</priority>'."\n";
					$html .= '	</url>';
				}
				// Display all other pages
				if ($page['is_global'] == true && $page['is_home'] == false) {
					$html .= "\n".'	<url>'."\n";
					$html .= '	  <loc>'.URL.$page['url'].'/</loc>'."\n";
					$html .= '	  <lastmod>'.$this->type_lastmod[0].'</lastmod>'."\n";
					$html .= '	  <changefreq>'.$this->type_changefreq[0].'</changefreq>'."\n";
					
					if($page['priority'] == '1.00') 	$html .= '	  <priority>1.00</priority>'."\n";
					elseif($page['priority'] == '0.50') $html .= '	  <priority>0.50</priority>'."\n";
					elseif($page['priority'] == '0.10') $html .= '	  <priority>0.10</priority>'."\n";
					elseif(is_numeric($page['priority'])) $html .= '	  <priority>'.$page['priority'].'</priority>'."\n";
					else $html .= '	  <priority>0.50</priority>'."\n";
					
					$html .= '	</url>';
				}
				
				// Display associated entries from selected datasources
				if (!empty($datasources)) {
					$dsm = new DatasourceManager(Administration::instance());
					$dsm = new DatasourceManager($this->_Parent);
					
					$params = array();
					foreach($datasources as $datasource) {
						try{
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
										
										$html .= "\n".'	<url>'."\n";
										$html .= '	  <loc>'.$url.'</loc>'."\n";
										$html .= '	  <lastmod>'.$this->type_lastmod[0].'</lastmod>'."\n";
										$html .= '	  <changefreq>'.$this->type_changefreq[0].'</changefreq>'."\n";
										
										$html .= '	  <priority>'.$priority.'</priority>'."\n";
										$html .= '	</url>';
									}
									
								}
							}
						} catch (Exception $e) {
							$html = 'Error: '.$e->getMessage();
							echo $html;
							die;
						}
					}
				}
			}
			
			$html .= "\n\n".'</urlset>';
			echo $html;
			
			// File path
			$custom_file = getcwd() . '/sitemap.xml';
			# Open the file and reset it, to recieve the new code
			$open_file = fopen($custom_file, 'w');			
			# Write xml to file, then close
			fwrite($open_file, $html);
			# Close the file
			fclose($open_file);
			
			//stop the loading of Symphony core
			die;
		}
	}