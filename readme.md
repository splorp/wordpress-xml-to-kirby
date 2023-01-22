# WordPress XML to Kirby

This script converts an XML file that has been exported in [WordPress eXtended RSS (WXR)](https://wordpress.org/support/article/tools-export-screen/) format to a flat file YAML structure for use with [Kirby](https://getkirby.com/).

This version of the code is based on the original [WPXML to Kirby](https://github.com/greywillfade/wpxml-to-kirby) script by [Sally Lait](https://sallylait.com/) with further modifications made by [Stay Regular Media](https://github.com/stayregular/wpxml-to-kirby).


## Requirements

+ [PHP](https://www.php.net/) 7.2 or later
+ [Composer](https://getcomposer.org/)
+ [HTML To Markdown for PHP](https://github.com/thephpleague/html-to-markdown)


## Usage

+ Download this repository and extract the contents to a working directory

```
/wordpress-xml-to-kirby
```

+ Install the [Composer](https://getcomposer.org/) dependency manager
+ Require the [HTML To Markdown for PHP](https://github.com/thephpleague/html-to-markdown) library

```
composer require league/html-to-markdown
```

+ [Export the content of your WordPress site](https://wordpress.org/documentation/article/tools-export-screen/) to an XML file
+ To include featured image metadata in the XML file, [see below](#include-featured-image-metadata)
+ Move to XML file to the working directory
+ Create an export directory in the working directory with full permissions

```
mkdir /wpxml-to-kirby/export`
chmod 777 /wpxml-to-kirby/export
```

+ Edit `convert.php` to add the name of the XML file and the export directory

```php
$importfile = 'data.xml';
$exportdir = 'export/';
```

+ Convert all the things!

```
php convert.php
```


## Include Featured Image Metadata

To include the featured image metadata in the XML file, the WordPress core `export.php` file must be modified.

+ Open `wp-admin/includes/export.php` in your favourite text editor
+ Locate the following code block:

```php
<wp:post_type><?php echo wxr_cdata( $post->post_type ); ?></wp:post_type>
<wp:post_password><?php echo wxr_cdata( $post->post_password ); ?></wp:post_password>
<wp:is_sticky><?php echo intval( $is_sticky ); ?></wp:is_sticky>
```
+ Add the following code directly following the aforementioned code block:

```php
<?php if ( has_post_thumbnail($post->ID) ) : ?>
<?php $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full') ?>
<wp:attachment_url><?php echo wxr_cdata( $image[0] ); ?></wp:attachment_url>
<?php endif; ?>
```

After this modification, the exported XML data will include links to any full sized featured images attached to pages or posts.

You can also modify the `get_post_thumbnail_id` function to retrieve a link to another image size or include additional XML objects for multiple image sizes.


## Release Notes

### 20230122 — The “Clean Up In Aisle Five” Release

+ Better handling of posts with empty `<title>` fields
+ Better handling of posts with `<title>` fields containing accented characters
+ Paragraph breaks are now maintained when processing `<content:encoded>`
+ Renamed several named array keys for clarity and consistency
+ Tweaked formatting of exported files because pretty
+ Renamed script from `index.php` to `convert.php` because that’s what it does

### 20200630 — The “Little Bump” Release

+ Updated [HTML to Markdown for PHP](https://github.com/thephpleague/html-to-markdown) to version [4.10.0](https://github.com/thephpleague/html-to-markdown/releases/tag/4.10.0)

### 20200309 — The “Compositionally Challenged” Release

+ Updated [HTML to Markdown for PHP](https://github.com/thephpleague/html-to-markdown) to version [4.9.1](https://github.com/thephpleague/html-to-markdown/releases/tag/4.9.1)
+ Added checks for items that do not have associated `attachment_url` data
+ Fixed missing space preceding `Text:` content

### 20200308 — The “Word Up” Release

+ Initial release based on the [WPXML to Kirby](https://github.com/stayregular/wpxml-to-kirby) script
+ Removed `index-events.php` for Modern Tribe’s [The Event Calendar](https://theeventscalendar.com/product/wordpress-events-calendar/) exports
+ Updated read me to describe this version of the script
