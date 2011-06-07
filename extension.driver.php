<?php

	Class extension_sitemap_xml extends Extension{
	
		public function about(){
			return array(
				'name' => 'Sitemap XML',
				'version' => '2.0.1alpha',
				'release-date' => '2011-06-07',
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
			
			// Autogenerate a blank sitemap.xml
			$fp = fopen(getcwd() . '/sitemap.xml', 'w+');
			fclose($fp);
			
			return Administration::instance()->saveConfig();
		}
		
		public function uninstall() {
			Symphony::Configuration()->remove('sitemap_xml');
			return Administration::instance()->saveConfig();
		}
		
		public function initaliseAdminPageHead($context) {
			$callback = Symphony::Engine()->getPageCallback();
			
			// Append assets
			if($callback['driver'] == 'blueprintspages') {
				Symphony::Engine()->Page->addScriptToHead(URL . '/extensions/sitemap_xml/assets/pages.js', 10001);
			}
		}
		
		public function __appendPreferences($context) {
			
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'Sitemap XML'));

			$label = Widget::Label('Home page type');
			$label->appendChild(
				Widget::Input(
					'settings[sitemap_xml][index_type]',
					General::Sanitize(Symphony::Configuration()->get('index_type', 'sitemap_xml'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Global page type');
			$label->appendChild(
				Widget::Input(
					'settings[sitemap_xml][global]',
					General::Sanitize(Symphony::Configuration()->get('global', 'sitemap_xml'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Modification date of XML');
			$label->appendChild(
				Widget::Input(
					'settings[sitemap_xml][lastmod]',
					General::Sanitize(Symphony::Configuration()->get('lastmod', 'sitemap_xml'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Change frequency of XML');
			$label->appendChild(
				Widget::Input(
					'settings[sitemap_xml][changefreq]',
					General::Sanitize(Symphony::Configuration()->get('changefreq', 'sitemap_xml'))
				)
			);
			$group->appendChild($label);

			$context['wrapper']->appendChild($group);
		}
		
	}

?>