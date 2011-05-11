<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	
	Class ContentExtensionSitemap_XmlXml extends AdministrationPage{
		
		const SITEMAP_LEVELS = 3;
		public $_pages = array();
		
		function view(&$wrapper) {
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
				'href' => URL . '/extensions/sitemap_xml/assets/publish.css'
			)));
			$head->appendChild(new XMLElement('script', '%nbsp;', array('src' => URL . '/symphony/assets/jquery.js')));
			$head->appendChild(new XMLElement('script', '%nbsp;', array('src' => URL . '/extensions/sitemap_xml/assets/publish.js')));
			
			// add headings
			$sitemap->appendChild(new XMLElement('h1', 'Sitemap XML <span>' . Symphony::Configuration()->get('sitename', 'general') . '</span>'));
			$sitemap->appendChild(new XMLElement('h2', 'Sitemap XML, ' . date('d F Y', time())));
			$sitemap->appendChild(new XMLElement('h6', '<a href="'.URL.'"></a>'));
			
			// layer elements
			$html->appendChild($head);
			$html->appendChild($body);
			$body->appendChild($sitemap);
			$sitemap->appendChild($raw);
			$sitemap->appendChild($pre);
			
			// echo content
			header('content-type: text/html');
			echo $html->generate(true);
						
			//stop the loading of Symphony core
			die;
		}
	}