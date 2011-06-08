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
			if($callback['driver'] == 'systempreferences') {
				Symphony::Engine()->Page->addScriptToHead(URL . '/extensions/sitemap_xml/assets/sitemap_xml.addtype.js', 10001);
			}
		}
		
		public function __appendPreferences($context) {
			/*@group config settings*/
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
			/*@group end*/
			
			/*@group add type to pages and save*/
			if(isset($_REQUEST['action']['add_pagetype'])){
				$id = $_REQUEST['addtype']['page'];
				$type = $_REQUEST['addtype']['page_type'];
				
				foreach($id as $page) {
					Symphony::Database()->query('
						INSERT INTO tbl_pages_types VALUES ("", "'.$page.'", "'.$type.'")
					');
				}
			}
			
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings add_pagetype');
			$group->appendChild(new XMLElement('legend', __('Add page type'))); 
			
			$span = new XMLElement('span', NULL, array('class' => 'frame'));
			
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
			
			$options = array();
			foreach($pages as $page) {
				$page_types = Symphony::Database()->fetchCol('type', "SELECT `type` FROM `tbl_pages_types` WHERE page_id = '".$page['id']."' ORDER BY `type` ASC");
				$page['types'] = $page_types;
				
				$options[] = array(
					$page['id'], false, $page['title']
				);
				
				$this->_pages[] = $page;
			}
			
			$label = Widget::Label(__('Pages'));
			$select = Widget::Select('addtype[page][]', $options, array('multiple'=>'multiple'));
			$label->appendChild($select);
			$group->appendChild($label);
			
			$label = Widget::Label(__('Type to add to selected pages:'));
			$label->appendChild(Widget::Input('addtype[page_type]', 'high'));
			$group->appendChild($label);
			
			$span->appendChild(new XMLElement('button', __('Add type to pages'), array_merge(array('name' => 'action[add_pagetype]', 'type' => 'submit'))));
	
			$group->appendChild($span);
			$context['wrapper']->appendChild($group);
		}
	}

?>