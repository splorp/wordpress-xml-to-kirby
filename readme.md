# WordPress XML to Kirby

This script converts an XML file that has been exported in [WordPress eXtended RSS (WXR)](https://wordpress.org/support/article/tools-export-screen/) format to a flat file YAML structure for use with [Kirby](https://getkirby.com/).
 
This version of the code is based on the original script by [Sally Lait](https://github.com/greywillfade/wpxml-to-kirby) with further modifications made by [Stay Regular Media](https://github.com/stayregular/wpxml-to-kirby).


## Bugs & Things To Do

+ Subdirectory names are missing high ASCII characters, eg: “Adrien Tétar” generates the subdirectory “20160704-Adrien-Ttar”
+ Possible use `wp:post_name` for correct page/subdirectory slug
+ The `Text:` field is missing a space preceding the content
+ Is `Coverimage:` the correct field name for the associated image?
+ Add export fields for speaker metadata `wpcf-speaker-sort`, `wpcf-speaker-twitter`, `wpcf-speaker-instagram`


## Release Notes

### 20200308 — The “Word Up” Release

+ Removed `index-events.php` for Tribes Event Calendar plugin exports
+ Updated read me to describe this version of the script


## How to use

**Note** You will need to modify your Wordpress core files to include the featured image meta data in the WPXML/RSS file.

+ Modify your Wordpress core files (see below)
+ Create a Wordpress export file of your posts (or events)
+ Add your export directory and XML file to the variables up top.
+ Create the export directory on your server and CHMOD it 777 to ensure writeability `localhost/wpxml-to-kirby/your-dir`
+ Upload scripts to same folder as export directory `localhost/wpxml-to-kirby`
+ Run the script!


## Modify Core Files

Open up `wp-admin/includes/export.php`

Find this section of code around line 542-544:

```php 
<wp:post_type><?php echo wxr_cdata( $post->post_type ); ?></wp:post_type>
<wp:post_password><?php echo wxr_cdata( $post->post_password ); ?></wp:post_password>
<wp:is_sticky><?php echo intval( $is_sticky ); ?></wp:is_sticky>
```

Add this code directly below the previous section:

```php 
<?php	if ( has_post_thumbnail($post->ID) ) : ?>
<?php $image =  wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full') ?>
<wp:attachment_url><?php echo wxr_cdata( $image[0] ); ?></wp:attachment_url>
<?php 	endif; ?>
```

After this modification, the exported files will include a link to the full-sized feature image.

You can also modify the `get_post_thumbnail_id` function to retrieve a link to another image size or include additional XML objects for multiple image sizes.
