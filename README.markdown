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

Also needed for correct output is the priority of each page, which is added to the pages Page Type field. By default it will return `<priority>0.5</priority>`.

The options are:

- `1.00` - will return `<priority>1.00</priority>`
- `0.90` - will return `<priority>0.90</priority>` 
- `0.80` - will return `<priority>0.80</priority>` 
- `0.70` - will return `<priority>0.70</priority>` 
- `0.60` - will return `<priority>0.60</priority>` 
- `0.50` - will return `<priority>0.50</priority>` 
- `0.40` - will return `<priority>0.40</priority>` 
- `0.30` - will return `<priority>0.30</priority>` 
- `0.20` - will return `<priority>0.20</priority>` 
- `0.10` - will return `<priority>0.10</priority>` 

The index/home page will automatically be set to 1.00.

You can also specify the modification date and change frequency in the System > Preferences page. These are set as current date/time at installation and "monthly" respectively.

Once done, navigate to the Sitemap XML page, Blueprints > Sitemap XML, and copy and paste the generated code into an xml file.