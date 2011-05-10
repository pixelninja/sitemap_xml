<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	
	Class ContentExtensionGenerate_SitemapMap extends AdministrationPage{
		
		const SITEMAP_LEVELS = 3;
		public $_pages = array();
		
		private $type_index = null;
		private $type_primary = null;
		private $type_utility = null;
		private $type_exclude = null;
		
		function view(){
			// fetch all pages
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
					
			// get values from config: remove spaces, remove any trailing commas and split into an array
			$this->type_index = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('index_type', 'generate_sitemap')), ','));
			$this->type_primary = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('global', 'generate_sitemap')), ','));
			$this->type_lastmod = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('lastmod', 'generate_sitemap')), ','));
			$this->type_changefreq = explode(',', trim(preg_replace('/ /', '', Symphony::Configuration()->get('changefreq', 'generate_sitemap')), ','));			
			
			// supplement list of pages with additional meta data
			foreach($pages as $page) {
				$page_types = Symphony::Database()->fetchCol('type', "SELECT `type` FROM `tbl_pages_types` WHERE page_id = '".$page['id']."' ORDER BY `type` ASC");
				
				$page['url'] = '/' . Administration::instance()->resolvePagePath($page['id']);
				$page['edit-url'] = Administration::instance()->getCurrentPageURL() . 'edit/' . $page['id'] . '/';
				$page['types'] = $page_types;
				
				if (count(array_intersect($page['types'], $this->type_exclude)) > 0) continue;
				
				$page['is_home'] = (count(array_intersect($page['types'], $this->type_index))) ? true : false;				
				$page['is_primary'] = (count(array_intersect($page['types'], $this->type_primary)) > 0) ? true : false;
				
				// Set priority level
				foreach($page['types'] as $type) {
					if ($type == 'high') {
						$page['priority'] = 'high';
					} elseif ($type == 'high-mid') {
						$page['priority'] = 'high-mid';
					} elseif ($type == 'mid') {
						$page['priority'] = 'mid';
					} elseif ($type == 'mid-low') {
						$page['priority'] = 'mid-low';
					} elseif ($type == 'low') {
						$page['priority'] = 'low';
					}
				}
				
				$this->_pages[] = $page;
			}
			
			
			// build a vanilla XML document
			$html  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$html .= '<urlset'."\n";
			$html .= '  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
			$html .= '  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
			$html .= '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n\n";
			echo $html;
			
			foreach($this->_pages as $page) {
				if ($page['is_home'] == true) {
					$html  = '	<url>'."\n";
					$html .= '	  <loc>'.URL.$page['url'].'</loc>'."\n";
					$html .= '	  <lastmod>'.$this->type_lastmod[0].'</lastmod>'."\n";
					$html .= '	  <changefreq>'.$this->type_changefreq[0].'</changefreq>'."\n";
					$html .= '	  <priority>1.00</priority>'."\n";
					$html .= '	</url>';
					echo $html;
				}
			}
			
			// append top level pages
			$primary_pages = 0;
			foreach($this->_pages as $page) {
				if ($page['is_primary'] == true) {
					$html  = "\n".'	<url>'."\n";
					$html .= '	  <loc>'.URL.$page['url'].'</loc>'."\n";
					$html .= '	  <lastmod>'.$this->type_lastmod[0].'</lastmod>'."\n";
					$html .= '	  <changefreq>'.$this->type_changefreq[0].'</changefreq>'."\n";
					
					if($page['priority'] == 'high') {
						$html .= '	  <priority>1.00</priority>'."\n";
					} elseif($page['priority'] == 'high-mid') {
						$html .= '	  <priority>0.84</priority>'."\n";
					} elseif($page['priority'] == 'mid') {
						$html .= '	  <priority>0.64</priority>'."\n";
					} elseif($page['priority'] == 'mid-low') {
						$html .= '	  <priority>0.44</priority>'."\n";
					} elseif($page['priority'] == 'low') {
						$html .= '	  <priority>0.24</priority>'."\n";
					} else {
						$html .= '	  <priority>0.84</priority>'."\n";
					}
					
					$html .= '	</url>';
					echo $html;
				}
			}
			
			$html  = "\n".'</urlset>';
			echo $html;
			die;
		}
	}