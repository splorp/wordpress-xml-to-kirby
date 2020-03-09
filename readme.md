# WordPress XML to Kirby

This script converts an XML file that has been exported in [WordPress eXtended RSS (WXR)](https://wordpress.org/support/article/tools-export-screen/) format to a flat file YAML structure for use with [Kirby](https://getkirby.com/).

This version of the code is based on the original [WPXML to Kirby](https://github.com/greywillfade/wpxml-to-kirby) script by [Sally Lait](https://sallylait.com/) with further modifications made by [Stay Regular Media](https://github.com/stayregular/wpxml-to-kirby).


## Requirements

+ [Composer](https://getcomposer.org/)
+ [HTML To Markdown for PHP](https://github.com/thephpleague/html-to-markdown)


## Usage

+ Download this repository to a working directory
+ Install the [Composer](https://getcomposer.org/) dependency manager
+ Require the [HTML To Markdown for PHP](https://github.com/thephpleague/html-to-markdown) library

`composer require league/html-to-markdown`

+ Modify your Wordpress core files (see below)
+ Create a Wordpress export file of your posts (or events)
+ Add your export directory and XML file to the variables up top.
+ Create the export directory on your server and CHMOD it 777 to ensure writeability `localhost/wpxml-to-kirby/your-dir`
+ Upload scripts to same folder as export directory `localhost/wpxml-to-kirby`
+ Run the script!


## Modify Core Files

To include the featured image metadata in the XML file, the WordPress core `export.php` file must be modified.

+ Open up `wp-admin/includes/export.php`
+ Locate the following code around line 542-544:

```php
<wp:post_type><?php echo wxr_cdata( $post->post_type ); ?></wp:post_type>
<wp:post_password><?php echo wxr_cdata( $post->post_password ); ?></wp:post_password>
<wp:is_sticky><?php echo intval( $is_sticky ); ?></wp:is_sticky>
```

+ Add the following code directly below the previous section:

```php
<?php if ( has_post_thumbnail($post->ID) ) : ?>
<?php $image =  wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full') ?>
<wp:attachment_url><?php echo wxr_cdata( $image[0] ); ?></wp:attachment_url>
<?php endif; ?>
```

After this modification, the exported XML will include a link to the full-sized feature image.

You can also modify the `get_post_thumbnail_id` function to retrieve a link to another image size or include additional XML objects for multiple image sizes.


## Bugs & Things To Do

+ Subdirectory names are missing accented characters, eg: “Adrien Tétar” generates the subdirectory “20160704-Adrien-Ttar”
+ Possibly use `wp:post_name` for correct page/subdirectory slug
+ Is `Coverimage:` the correct field name for the associated image?
+ Add export fields for speaker metadata `wpcf-speaker-sort`, `wpcf-speaker-twitter`, `wpcf-speaker-instagram`
+ [HTML to Markdown](https://github.com/thephpleague/html-to-markdown) removes line breaks, need to change `%0D` (CR) to `<br>` and set `$converter->getConfig()->setOption('hard_break', true);`


## Release Notes

### 20200309 — The “Compositionally Challenged” Release

+ Updated [HTML to Markdown for PHP](https://github.com/thephpleague/html-to-markdown) to version [4.9.1](https://github.com/thephpleague/html-to-markdown/releases/tag/4.9.1)
+ Added checks for items that do not have associated `attachment_url` data
+ Fixed missing space preceding `Text:` content

### 20200308 — The “Word Up” Release

+ Initial release based on the [WPXML to Kirby](https://github.com/stayregular/wpxml-to-kirby) script
+ Removed `index-events.php` for Modern Tribe’s [The Event Calendar](https://theeventscalendar.com/product/wordpress-events-calendar/) exports
+ Updated read me to describe this version of the script
