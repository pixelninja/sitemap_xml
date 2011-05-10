<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	
	Class ContentExtensionGenerate_SitemapXml_output extends AdministrationPage{
		
		const SITEMAP_LEVELS = 3;
		public $_pages = array();
		
		private $type_index = null;
		private $type_primary = null;
		private $type_lastmod = null;
		private $type_changefreq = null;
		
		function view(&$wrapper) {
			// fetch all pages
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
					
			$html = new XMLElement('html');
			$head = new XMLElement('head');
			$body = new XMLElement('body');
			$sitemap = new XMLElement('div', null, array('class' => 'sitemap'));
			
			// add headings
			$sitemap->appendChild(new XMLElement('h1', 'Sitemap XML <span>' . Symphony::Configuration()->get('sitename', 'general') . '</span>'));
			$sitemap->appendChild(new XMLElement('h2', 'Site Map, ' . date('d F Y', time())));
			
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
			
			// build a vanilla HTML document
			$html->setDTD('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
			
			$head->appendChild(new XMLElement('meta', null, array(
				'http-equiv' => 'Content-Type', 
				'context' => 'text/html; charset=utf-8'
			)));
			$head->appendChild(new XMLElement('title', 'Site Map XML — ' . Symphony::Configuration()->get('sitename', 'general')));
			$head->appendChild(new XMLElement('link', null, array(
				'rel' => 'stylesheet',
				'type' => 'text/css',
				'media' => 'print, screen',
				'href' => URL . '/extensions/generate_sitemap/assets/publish.css'
			)));
			
			$html->appendChild($head);
			$html->appendChild($body);
			$body->appendChild($sitemap);
			
			
			$code = new XMLElement('pre', '
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;urlset
  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"&gt;
  
  
&lt;/urlset&gt;
			');
				
			$sitemap->appendChild($code);
		
			header('content-type: text/html');
			echo $html->generate(true);
			
			die;
		}
	
		
		/*private function source() {
			// build sitemap code
			$echo  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";		
			$echo .= '<urlset '."\n";
			$echo .= '  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
			$echo .= '  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'."\n";
			$echo .= '  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n";
			echo $echo;	
				
			// append the Home page first
			foreach($this->_pages as $page) {
				if ($page['is_home'] == true) {
					$echo  = "\n".'	<url>'."\n";
					$echo .= '		<loc>'.URL.$page['url'].'</loc>'."\n";
					$echo .= '		<lastmod>'.$this->type_lastmod[0].'</lastmod>'."\n";
					$echo .= '		<changefreq>'.$this->type_changefreq[0].'</changefreq>'."\n";
					$echo .= '		<priority>1.00</priority>'."\n";
					$echo .= '	</url>';
					echo $echo;
				}
			}
			
			// append top level pages
			$primary_pages = 0;
			foreach($this->_pages as $page) {
				if ($page['is_primary'] == true) {
					$echo  = "\n".'	<url>'."\n";
					$echo .= '		<loc>'.URL.$page['url'].'</loc>'."\n";
					$echo .= '		<lastmod>'.$this->type_lastmod[0].'</lastmod>'."\n";
					$echo .= '		<changefreq>'.$this->type_changefreq[0].'</changefreq>'."\n";
					
					if($page['priority'] == 'high') {
						$echo .= '		<priority>1.00</priority>'."\n";
					} elseif($page['priority'] == 'high-mid') {
						$echo .= '		<priority>0.84</priority>'."\n";
					} elseif($page['priority'] == 'mid') {
						$echo .= '		<priority>0.64</priority>'."\n";
					} elseif($page['priority'] == 'mid-low') {
						$echo .= '		<priority>0.44</priority>'."\n";
					} elseif($page['priority'] == 'low') {
						$echo .= '		<priority>0.24</priority>'."\n";
					} else {
						$echo .= '		<priority>0.84</priority>'."\n";
					}
					
					$echo .= '	</url>';
					echo $echo;
					
					//var_dump($page);
				}
			}
				
			$echo = "\n".'</urlset> '."\n";
			echo $echo;
		}*/
	}