<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	
	Class ContentExtensionSitemap_XmlRaw extends AdministrationPage{
		
		const SITEMAP_LEVELS = 3;
		public $_pages = array();
		
		private $type_index = null;
		private $type_global = null;
		private $type_utility = null;
		private $type_exclude = null;
		
		function view(){
			// fetch all pages
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
					
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
				
				if (count(array_intersect($page['types'], $this->type_exclude)) > 0) continue;
				
				$page['is_home'] = (count(array_intersect($page['types'], $this->type_index))) ? true : false;				
				$page['is_global'] = (count(array_intersect($page['types'], $this->type_global)) > 0) ? true : false;
				
				// Set priority level
				foreach($page['types'] as $type) {
					if ($type == '1.00') 	 $page['priority'] = '1.00';
					elseif ($type == '0.90') $page['priority'] = '0.90';
					elseif ($type == '0.80') $page['priority'] = '0.80';
					elseif ($type == '0.70') $page['priority'] = '0.70';
					elseif ($type == '0.60') $page['priority'] = '0.60';
					elseif ($type == '0.50') $page['priority'] = '0.50';
					elseif ($type == '0.40') $page['priority'] = '0.40';
					elseif ($type == '0.30') $page['priority'] = '0.30';
					elseif ($type == '0.20') $page['priority'] = '0.20';
					elseif ($type == '0.10') $page['priority'] = '0.10';
				}
				
				$this->_pages[] = $page;
			}
			
			
			// build the document
			$html  = '&lt;?xml version="1.0" encoding="UTF-8"?&gt;'."\n";
			$html .= '&lt;urlset'."\n";
			$html .= '  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
			$html .= '  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
			$html .= '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"&gt;'."\n\n";
			echo $html;
			
			foreach($this->_pages as $page) {
				if ($page['is_home'] == true) {
					$html  = '	&lt;url&gt;'."\n";
					$html .= '	  &lt;loc&gt;'.URL.$page['url'].'/&lt;/loc&gt;'."\n";
					$html .= '	  &lt;lastmod&gt;'.$this->type_lastmod[0].'&lt;/lastmod&gt;'."\n";
					$html .= '	  &lt;changefreq&gt;'.$this->type_changefreq[0].'&lt;/changefreq&gt;'."\n";
					$html .= '	  &lt;priority&gt;1.00&lt;/priority&gt;'."\n";
					$html .= '	&lt;/url&gt;';
					echo $html;
				}
			}
			
			// append top level pages
			$primary_pages = 0;
			foreach($this->_pages as $page) {
				if ($page['is_global'] == true) {
					$html  = "\n".'	&lt;url&gt;'."\n";
					$html .= '	  &lt;loc&gt;'.URL.$page['url'].'/&lt;/loc&gt;'."\n";
					$html .= '	  &lt;lastmod&gt;'.$this->type_lastmod[0].'&lt;/lastmod&gt;'."\n";
					$html .= '	  &lt;changefreq&gt;'.$this->type_changefreq[0].'&lt;/changefreq&gt;'."\n";
					
					if($page['priority'] == '1.00') 	$html .= '	  &lt;priority&gt;1.00&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.90') $html .= '	  &lt;priority&gt;0.90&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.80') $html .= '	  &lt;priority&gt;0.80&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.70') $html .= '	  &lt;priority&gt;0.70&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.60') $html .= '	  &lt;priority&gt;0.60&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.50') $html .= '	  &lt;priority&gt;0.50&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.40') $html .= '	  &lt;priority&gt;0.40&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.30') $html .= '	  &lt;priority&gt;0.30&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.20') $html .= '	  &lt;priority&gt;0.20&lt;/priority&gt;'."\n";
					elseif($page['priority'] == '0.10') $html .= '	  &lt;priority&gt;0.10&lt;/priority&gt;'."\n";
					else $html .= '	  &lt;priority&gt;0.50&lt;/priority&gt;'."\n";
					
					$html .= '	&lt;/url&gt;';
					echo $html;
				}
			}
			
			$html  = "\n\n".'&lt;/urlset&gt;';
			echo $html;
			die;
		}
	}