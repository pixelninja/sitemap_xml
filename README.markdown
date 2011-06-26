# Sitemap XML

- Version: 2.0a
- Author: Phill Gray
- Build Date: 2011-06-27
- Requirements: Symphony 2.2.1

## Installation

- Upload the 'sitemap_xml' folder to your Symphony 'extensions' folder.
- Enable it by selecting "Sitemap XML", choose Enable from the with-selected menu, then click Apply.

## Configuration

To output correctly, Sitemap XML needs to know which page is your index/home page, and which are the global pages to be included. 

By default it assumes the index/home page will have the type "index" and global pages have the type "sitemap".

If you use a different convention in your Page Type field then you can change these in the System > Preferences page.

**It is recommended to use the [Page Type Tool extension](http://symphony-cms.com/download/extensions/view/72108/). This will make adding page types to multiple pages a lot easier.**

Also needed for correct output is the priority of each page, which is added to the pages Page Type field. By default it will return `<priority>0.5</priority>`.

The priority options are:

- `high` (1.00)
- `mid`  (0.50)
- `low`  (0.10)
- You can also specify the numerical value, ranging from `1.00` to `0.10`.

The index/home page will automatically be set to 1.00, and at this stage cannot be changed (unless done manually).

You can also specify the modification date and change frequency in the System > Preferences page. These are set as the current date/time at installation and "monthly" respectively.

## Usage

On installation, a `sitemap.xml` file is created in your root directory. This will be automatically populated later.

Go to System > Preferences if you wish to change any default settings.

Each page that is to be added to the sitemap file needs to have the global page type specified, as well as the desired priority level. Enter this in the pages Page Type field.

Once done, navigate to the Sitemap XML page, Blueprints > Sitemap XML. It will show you the generated code and the option to add a datasource to a page with a relative URL, which accepts xpath.

Every time the page is refreshed, it will write to the sitemap.xml file created during installation.

**NOTE: the creation of the sitemap.xml file during installation will overwrite any current files named the same. So please back up or rename any previous sitemap.xml files**