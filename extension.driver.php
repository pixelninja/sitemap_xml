<?php

	Class extension_sitemap_xml extends Extension{
	
		public function fetchNavigation() {
			return array(
				array(
					'location' => __('System'),
					'name'	=> __('Sitemap XML'),
					'link'	=> '/xml/',
				),
			);
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendPageHead'
				)
			);
		}
		
		public function install() {
			// Add defaults to config.php
			if (!Symphony::Configuration()->get('index_type', 'sitemap_xml')) {
				Symphony::Configuration()->set('index_type', 'index', 'sitemap_xml');
				Symphony::Configuration()->set('global', 'sitemap', 'sitemap_xml');
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
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
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
		
		public function appendPageHead($context) {
			$callback = Administration::instance()->getPageCallback();
			
			if($callback['driver'] == 'xml') {
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/sitemap_xml/assets/sitemap_xml.publish.js', 10001);
				Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/sitemap_xml/assets/sitemap_xml.publish.css', 'screen');
			}
		}
		
		public function appendPreferences($context) {
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
			$sitemap_entries = Symphony::Database()->fetch("SELECT * FROM `tbl_sitemap_xml`");
		
			/*@group Fieldset containing config settings*/
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', __('Sitemap XML')));
			$context['wrapper']->appendChild($fieldset);
			
			/* column 1*/
			$column = new XMLElement('div');
			$column->setAttribute('class', 'two columns');
			
			$label = Widget::Label(__('Home page type'));
			$label->setAttribute('class', 'column');
			$label->appendChild(Widget::Input('settings[sitemap_xml][index_type]', General::Sanitize(Symphony::Configuration()->get('index_type', 'sitemap_xml'))));
			$column->appendChild($label);
			
			$label = Widget::Label(__('Global page type'));
			$label->setAttribute('class', 'column');
			$label->appendChild(Widget::Input('settings[sitemap_xml][global]',General::Sanitize(Symphony::Configuration()->get('global', 'sitemap_xml'))));
			$column->appendChild($label);
			
			$fieldset->appendChild($column);
			
			/* column 2*/
			$column = new XMLElement('div');
			
			$label = Widget::Label(__('Change frequency of XML'));
			$label->appendChild(Widget::Input('settings[sitemap_xml][changefreq]',General::Sanitize(Symphony::Configuration()->get('changefreq', 'sitemap_xml'))));
			$column->appendChild($label);

			$fieldset->appendChild($column);
			/*@column end*/
		}
	}

?>