<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	
	Class ContentExtensionSitemap_XmlXml extends AdministrationPage{
		
		function view() {
			// fetch all pages
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
			
			// Add elements
			$html = new XMLElement('html');
			$head = new XMLElement('head');
			$body = new XMLElement('body');
			$sitemap = new XMLElement('div', null, array('class' => 'sitemap'));
			$pre = new XMLElement('pre');
			$raw = new XMLElement('a', 'View raw', array(
												   	  'class' => 'raw',
												   	  'rel' => 'external',
												   	  'href' => URL.'/sitemap.xml'
												   ));
						
			// add doctype
			$html->setDTD('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
			
			// add head
			$head->appendChild(new XMLElement('meta', null, array(
				'http-equiv' => 'Content-Type', 
				'context' => 'text/html; charset=utf-8'
			)));
			$head->appendChild(new XMLElement('title', 'Sitemap XML — ' . Symphony::Configuration()->get('sitename', 'general')));
			$head->appendChild(new XMLElement('link', null, array(
				'rel' => 'stylesheet',
				'type' => 'text/css',
				'media' => 'print, screen',
				'href' => URL . '/extensions/sitemap_xml/assets/sitemap_xml.publish.css'
			)));
			$head->appendChild(new XMLElement('script', '%nbsp;', array('src' => URL . '/symphony/assets/jquery.js')));
			$head->appendChild(new XMLElement('script', '%nbsp;', array('src' => URL . '/extensions/sitemap_xml/assets/sitemap_xml.publish.js')));
			
			// add headings
			$h1 = new XMLElement('h1', 'Sitemap XML <span>' . Symphony::Configuration()->get('sitename', 'general') . '</span>');
			$h2 = new XMLElement('h2', 'Sitemap XML, ' . date('d F Y', time()));
			// hidden heading to get the root url with jquery
			$h6 = new XMLElement('h6', '<a href="'.URL.'"></a>');
			$google = new XMLElement('a', 'Ping Google', array(
															'href'=>'http://www.google.com/webmasters/sitemaps/ping?sitemap='.URL.'/sitemap.xml',      
															'class'=>'google',  
															'target'=>'_blank'    
														));
			$bing = new XMLElement('a', 'Ping Bing', array(
															'href'=>'http://www.bing.com/webmaster/ping.aspx?siteMap='.URL.'/sitemap.xml',      
															'class'=>'bing',  
															'target'=>'_blank'    
														));
			$yahoo = new XMLElement('a', 'Ping Yahoo', array(
															'href'=>'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=YahooDemo&url='.URL.'/sitemap.xml',      
															'class'=>'yahoo',  
															'target'=>'_blank'    
														));
			
			// layer elements
			$html->appendChild($head);
			$html->appendChild($body);
			$body->appendChild($sitemap);
			$h1->appendChild($raw);
			$sitemap->appendChild($google);
			$sitemap->appendChild($bing);
			$sitemap->appendChild($yahoo);
			$sitemap->appendChild($h1);
			$sitemap->appendChild($h2);
			$sitemap->appendChild($h6);
			$sitemap->appendChild($pre);
			
			// echo content
			header('content-type: text/html');
			echo $html->generate(true);
						
			//stop the loading of Symphony core
			die;
		}
	}