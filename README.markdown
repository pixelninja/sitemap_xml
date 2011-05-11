# Sitemap

* Version: 1.0
* Author: Phill Gray (phill@randb.com.au)
* Build Date: 2011-05-11
* Requirements: Symphony 2.2*

## Installation

1. Upload the 'sitemap_xml' folder in this archive to your Symphony 'extensions' folder.
2. Enable it by selecting the "Publish Filtering", choose Enable from the with-selected menu, then click Apply.
3. Select Blueprints > Sitemap XML to view the sitemap XML

## Configuration
To output correctly, Sitemap XML needs to know which page is your index/home page, and which are the global pages to be included. 

By default it assumes the index/home page will have the type "index" and global pages have the type "map". 

If you use a different convention in your Page Type field then you can change these in the System > Preferences page. 

Also needed for correct output is the priority of each page. By default it will return &lt;priority&gt;0.84&lt;/priority&gt;, but you can specify in the System > Preferences page.

The options are:
**high** - this will return <priority>1.00</priority>
**high-mid** - this will return &lt;priority&gt;0.84&lt;/priority&gt; 
**mid** - this will return &lt;priority&gt;0.64&lt;/priority&gt; 
**mid-low** - this will return &lt;priority&gt;0.44&lt;/priority&gt; 
**low** - this will return &lt;priority&gt;0.24&lt;/priority&gt; 

The index/home page will automatically be set to 1.00.

You can also specify the modification date and change frequency in the System > Preferences page. These are set to the current date/time at installation and "monthly" respectively.