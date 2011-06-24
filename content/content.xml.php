<?php
	
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.datasourcemanager.php');
	
	Class ContentExtensionSitemap_XmlXml extends AdministrationPage{
		
		public function view() {	
			$pages = Symphony::Database()->fetch("SELECT p.* FROM `tbl_pages` AS p ORDER BY p.sortorder ASC");
			$sitemap_entries = Symphony::Database()->fetch("SELECT * FROM `tbl_sitemap_xml`");

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

			/* Pin DS to Page */
			$fieldset = new XMLElement('fieldset', null, array('class'=>'secondary'));
			$fieldset->appendChild(new XMLElement('legend', __('Pin datasources to page')));

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
			
			$span = new XMLElement('span', NULL, array('class' => 'frame'));
			
			
			$group = new XMLElement('div');
			
			$label = Widget::Label(__('Relative URL'));
			$label->appendChild(Widget::Input('pin[relative_url]', '/'));
			$group->appendChild($label);
			
			$help = new XMLElement('p', 'For example: if the page was News, the relative url might be /{news-title/@handle}/{@id}/. This would output '.URL.'/news/random-article/32/', array('class' => 'help'));
			$group->appendChild($help);
			
			$span->appendChild(new XMLElement('button', __('Pin datasource to page'), array_merge(array('name' => 'action[pin]', 'type' => 'submit'))));
			$group->appendChild($span);
			
			$fieldset->appendChild($group);
			$this->Form->appendChild($fieldset);
			/* end */
			
			/* id entries exist, display linked DS/pages */
			if($sitemap_entries != null) {
				
				$fieldset = new XMLElement('fieldset', null, array('class'=>'secondary'));
				$fieldset->appendChild(new XMLElement('legend', __('Current pinned datasources')));
				$group = new XMLElement('div');
					
				$table = new XMLElement('table');
				$table->setAttribute('class', 'selectable');
				$tableBody = array();
				$tableHead = array(
					array(__('Datasource'), 'col'),
					array(__('Page'), 'col'),
					array(__('Relative URL'), 'col')
				);	
						
				if(!empty($sitemap_entries)) {
					foreach($sitemap_entries as $entry) {
						$related_page = Symphony::Database()->fetch("SELECT title FROM `tbl_pages` WHERE id=" . $entry['page_id']);
							
						$ds = Widget::TableData(ucfirst(str_replace('_', ' ', $entry['datasource_handle'])));
						$ds->appendChild(Widget::Input("row[".$entry['id']."]", $entry['id'], 'checkbox', array('id' => 'item')));
						$page = Widget::TableData($related_page[0]['title']);
						$url = Widget::TableData($entry['relative_url']);
							
						$tableBody[] = Widget::TableRow(
							array(
								$ds, 
								$page, 
								$url
							)
						);
						
					}
				}
				$table->appendChild(Widget::TableHead($tableHead));
				$table->appendChild(Widget::TableBody($tableBody));
				$group->appendChild($table);
				
				$fieldset->appendChild($group);
				
				$span = new XMLElement('span', NULL, array(
														'class' => 'frame',
														'style' => 'margin-top: 15px;'
													 ));
				$span->appendChild(new XMLElement('button', __('Delete pinned datasource'), array_merge(array('name' => 'action[removeRow]', 'type' => 'submit'))));
				$fieldset->appendChild($span);
				
				$this->Form->appendChild($fieldset);
			}
			/* end */
			
			
			
						
			/*@group mysql query on Pin submit*/
			if(isset($_REQUEST['action']['pin'])){
				$page = $_REQUEST['pin']['page'];
				$datasource = $_REQUEST['pin']['datasource'];
				$relative_url = $_REQUEST['pin']['relative_url'];
				
				Symphony::Database()->query('
					INSERT INTO tbl_sitemap_xml VALUES ("", "'.$page.'", "'.$datasource.'", "'.$relative_url.'")
				');
				
				header("Location: ".URL."/symphony/extension/sitemap_xml/xml/success/");
			}
			
			/*@group mysql query on Delete submit*/
			if(isset($_REQUEST['action']['removeRow'])){
				$item_id = $_REQUEST['row'];

				foreach($item_id as $id) {
					Symphony::Database()->query('DELETE FROM tbl_sitemap_xml WHERE id=' .$id );
				}
				
				header("Location: ".URL."/symphony/extension/sitemap_xml/xml/success/");
			}

			
			
			
			
			
		}
	}
