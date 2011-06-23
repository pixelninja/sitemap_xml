<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.datasourcemanager.php');
	
	Class ContentExtensionSitemap_XmlXml extends AdministrationPage{
		
		public function view() {	
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");

			$this->setPageType('index');
			$this->setTitle(__('Sitemap XML Generator'));
			//$this->appendSubheading(__('Sitemap XML Generator'), '<a class="raw" href="'.URL.'/symphony/extension/sitemap_xml/raw/" rel="source">View raw</a>');

			$h2 = new XMLElement('h2', __('Sitemap XML'));
			$h2->appendChild(new XMLElement('span', __('Generator')));

			/* sitemap output */
			$fieldset = new XMLElement('fieldset', null, array('class'=>'primary'));
			$pre = new XMLElement('pre');

			$h2->appendChild(new XMLElement('a', 'View raw', array(
															'href'=>URL.'/symphony/extension/sitemap_xml/raw/',      
															'class'=>'raw',   
															'rel'=>'source'  
														)));
			$h2->appendChild(new XMLElement('a', 'Ping Google', array(
															'href'=>'http://www.google.com/webmasters/sitemaps/ping?sitemap='.URL.'/sitemap.xml',      
															'class'=>'google',  
															'rel'=>'external'    
														)));
			$h2->appendChild(new XMLElement('a', 'Ping Bing', array(
															'href'=>'http://www.bing.com/webmaster/ping.aspx?siteMap='.URL.'/sitemap.xml',      
															'class'=>'bing',  
															'rel'=>'external'    
														)));
			$h2->appendChild(new XMLElement('a', 'Ping Yahoo', array(
															'href'=>'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=YahooDemo&url='.URL.'/sitemap.xml',      
															'class'=>'yahoo',  
															'rel'=>'external'    
														)));

			$this->Contents->appendChild($h2);

			$fieldset->appendChild($pre);
			$this->Form->appendChild($fieldset);
			/* end */

			/* ds linking */
			$fieldset = new XMLElement('fieldset', null, array('class'=>'secondary'));

			$dsm = new DatasourceManager(Administration::instance());
			$datasources = array('');
			foreach($dsm->listAll() as $ds) {
				$datasources[] = array(
									$ds['handle'], 
									null, 
									$ds['name']
								 );
			}
			
			$page_list = array('');
			foreach($pages as $page) {
				$page_list[] = array(
									$page['id'],
									null,
									$page['title']
								);
			}
			
			$group = new XMLElement('div', null, array('class'=>'group'));
			
			$label = Widget::Label(__('Datasource:'));
			$label->appendChild(Widget::Select('pin[datasource]', $datasources));
			$group->appendChild($label);
			
			$label = Widget::Label(__('Page'));
			$label->appendChild(Widget::Select('pin[page]', $page_list));
			$group->appendChild($label);
			$fieldset->appendChild($group);
			
			
			$group = new XMLElement('div');
			
			$label = Widget::Label(__('Relative URL'));
			$label->appendChild(Widget::Input('pin[relative_url]', '/'));
			$group->appendChild($label);
			
			$help = new XMLElement('p', 'For example: if the page was News, the relative url might be /{news-title/@handle}/{@id}/. This would output '.URL.'/news/random-article/32/', array('class' => 'help'));
			$group->appendChild($help);
			
			if($sitemap_entries != null) {
				$label = Widget::Label(__('Show pinned datasources'));
				$label->setAttribute('class', 'view_pinned');
				$label->appendChild(Widget::Input('view[pinned]', 'yes', 'checkbox'));
				$group->appendChild($label);
			}
			$fieldset->appendChild($group);

			$this->Form->appendChild($fieldset);
			/* end */
		}
	}
