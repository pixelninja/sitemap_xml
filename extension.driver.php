<?php

	Class extension_sitemap_xml extends Extension{
	
		public function about(){
			return array(
				'name' => 'Sitemap XML',
				'version' => '2.1',
				'release-date' => '2011-06-10',
				'author' => array(
				 		'name' => 'Phill Gray',
						'email' => 'phill@randb.com.au'
					)
		 		);
		}
		
		public function fetchNavigation() {
			return array(
				array(
					'location' => 'Blueprints',
					'name'	=> 'Sitemap XML',
					'link'	=> '/xml/',
				),
			);
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => '__appendPreferences'
				),
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'initaliseAdminPageHead'
				)
			);
		}
		
		public function install() {
			// Add defaults to config.php
			if (!Symphony::Configuration()->get('index_type', 'sitemap_xml')) {
				Symphony::Configuration()->set('index_type', 'index', 'sitemap_xml');
				Symphony::Configuration()->set('global', 'sitemap', 'sitemap_xml');
				Symphony::Configuration()->set('lastmod', date('c', time()), 'sitemap_xml');
				Symphony::Configuration()->set('changefreq', 'monthly', 'sitemap_xml');
			}
			
			// Add table to database 
			Symphony::Database()->query('
				CREATE TABLE IF NOT EXISTS tbl_sitemap_xml (
					`id` INT(4) UNSIGNED DEFAULT NULL AUTO_INCREMENT,
					`page_id` INT(4) UNSIGNED DEFAULT NULL,
					`datasource_handle` VARCHAR(255) DEFAULT NULL,
					`relative_url` VARCHAR(255) DEFAULT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY datasource_handle_page_id_relative_url (`datasource_handle`(75), `page_id`, `relative_url`(75))
				) ENGINE=MyISAM
			');
			
			// Autogenerate a blank sitemap.xml
			$fp = fopen(getcwd() . '/sitemap.xml', 'w+');
			fclose($fp);
			
			return Administration::instance()->saveConfig();
		}
		
		public function uninstall() {
			Symphony::Configuration()->remove('sitemap_xml');
			Symphony::Database()->query('DROP TABLE IF EXISTS tbl_sitemap_xml');
			return Administration::instance()->saveConfig();
		}
		
		public function initaliseAdminPageHead($context) {
			$callback = Symphony::Engine()->getPageCallback();
			
			if($callback['driver'] == 'xml') {
				Symphony::Engine()->Page->addScriptToHead(URL . '/extensions/sitemap_xml/assets/sitemap_xml.publish.js', 10001);
				Symphony::Engine()->Page->addStylesheetToHead(URL . '/extensions/sitemap_xml/assets/sitemap_xml.publish.css', 'screen');
			}
		}
		
		public function __appendPreferences($context) {
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
			$sitemap_entries = Symphony::Database()->fetch("SELECT * FROM `tbl_sitemap_xml`");
		
			/*@group Fieldset containing config settings*/
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', __('Sitemap XML')));
			$context['wrapper']->appendChild($fieldset);
			
			/* group 1*/
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			$label = Widget::Label(__('Home page type'));
			$label->appendChild(Widget::Input('settings[sitemap_xml][index_type]', General::Sanitize(Symphony::Configuration()->get('index_type', 'sitemap_xml'))));
			$group->appendChild($label);
			
			$label = Widget::Label(__('Global page type'));
			$label->appendChild(Widget::Input('settings[sitemap_xml][global]',General::Sanitize(Symphony::Configuration()->get('global', 'sitemap_xml'))));
			$group->appendChild($label);
			
			$fieldset->appendChild($group);
			
			/* group 2*/
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			$label = Widget::Label(__('Modification date of XML'));
			$label->appendChild(Widget::Input('settings[sitemap_xml][lastmod]',General::Sanitize(Symphony::Configuration()->get('lastmod', 'sitemap_xml'))));
			$group->appendChild($label);
			
			$label = Widget::Label(__('Change frequency of XML'));
			$label->appendChild(Widget::Input('settings[sitemap_xml][changefreq]',General::Sanitize(Symphony::Configuration()->get('changefreq', 'sitemap_xml'))));
			$group->appendChild($label);

			$fieldset->appendChild($group);
			/*@group end*/
		}
	}

?>