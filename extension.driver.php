<?php

	Class extension_generate_sitemap extends Extension{
	
		public function about(){
			return array(
				'name' => 'Generate Sitemap',
				'version' => '1.0',
				'release-date' => '2011-05-10',
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
					'name'	=> 'Generate Sitemap',
					'link'	=> '/xml-output/',
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
			);
		}
		
		public function install() {
			if (!Symphony::Configuration()->get('primary_type', 'generate_sitemap')) {
				Symphony::Configuration()->set('index_type', 'index', 'generate_sitemap');
				Symphony::Configuration()->set('global', 'sitemap', 'generate_sitemap');
				Symphony::Configuration()->set('lastmod', date('c', time()), 'generate_sitemap');
				Symphony::Configuration()->set('changefreq', 'monthly', 'generate_sitemap');
			}
			return Administration::instance()->saveConfig();
		}
		
		public function uninstall() {
			Symphony::Configuration()->remove('generate_sitemap');
			return Administration::instance()->saveConfig();
		}
		
		public function __appendPreferences($context) {
			
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'Generate Sitemap'));

			$label = Widget::Label('Home page type');
			$label->appendChild(
				Widget::Input(
					'settings[generate_sitemap][index_type]',
					General::Sanitize(Symphony::Configuration()->get('index_type', 'generate_sitemap'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Global page type');
			$label->appendChild(
				Widget::Input(
					'settings[generate_sitemap][global]',
					General::Sanitize(Symphony::Configuration()->get('global', 'generate_sitemap'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Modification date of XML');
			$label->appendChild(
				Widget::Input(
					'settings[generate_sitemap][lastmod]',
					General::Sanitize(Symphony::Configuration()->get('lastmod', 'generate_sitemap'))
				)
			);
			$group->appendChild($label);
			
			$label = Widget::Label('Change frequency of XML');
			$label->appendChild(
				Widget::Input(
					'settings[generate_sitemap][changefreq]',
					General::Sanitize(Symphony::Configuration()->get('changefreq', 'generate_sitemap'))
				)
			);
			$group->appendChild($label);

			$context['wrapper']->appendChild($group);
		}
		
	}

?>