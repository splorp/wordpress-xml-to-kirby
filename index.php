<?php

// This script converts an XML file exported from WordPress in
// WXR format to a flat file YAML structure for use with Kirby.
// https://github.com/splorp/wordpress-xml-to-kirby

// Requires HTML To Markdown for PHP
// https://github.com/thephpleague/html-to-markdown

require 'vendor/autoload.php';
use League\HTMLToMarkdown\HtmlConverter;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the namespaces used in the XML document

$ns = array (
	'excerpt' => "http://wordpress.org/export/1.2/excerpt/",
	'content' => "http://purl.org/rss/1.0/modules/content/",
	'wfw' => "http://wellformedweb.org/CommentAPI/",
	'dc' => "http://purl.org/dc/elements/1.1/",
	'wp' => "http://wordpress.org/export/1.2/"
);

// Specify the directory where files will be exported, including a trailing slash

$exportdir = 'export/';

// Specify the source XML file

$importfile = 'data.xml';

// Get the contents of the XML file

$xml = file_get_contents($importfile);
$xml = new SimpleXmlElement($xml);

// Grab all the things!

foreach ($xml->channel->item as $item)
{
	$article = array();
	$article['title'] = $item->title;
	$article['link'] = $item->link;
	$article['pubDate'] = date('m/d/Y', strtotime($item->pubDate));
	$article['timestamp'] = strtotime($item->pubDate);
	$article['description'] = (string) trim($item->description);
	$article['image'] = (string) trim($item->children($ns['wp'])->attachment_url);
	if ($article['image']) {
		$article['image_data'] = file_get_contents($article['image']);
	}

// Grab categories and tags for each post

	$tags = array();
	$categories = array();
	foreach ($item->category as $cat) {
		$cattype = $cat['domain'];

		if($cattype == "post_tag") {
			array_push($tags,$cat);
		}
		elseif($cattype == "category") {
			array_push($categories,$cat);
		}
	}

// Grab data within specific namespaces

	$content = $item->children($ns['content']);
	$wfw = $item->children($ns['wfw']);

	$article['content'] = (string) trim($content->encoded);
	$article['content'] = mb_convert_encoding($article['content'], 'HTML-ENTITIES', "UTF-8");
	$article['commentRss'] = $wfw->commentRss;

// Convert HTML to Markdown, set optional parameters (ie: strip_tags)

	$converter = new HtmlConverter(array('strip_tags' => true));
	$markdown = $converter->convert($article['content']);
	
// Strip WordPress caption shortcodes, optional

	$markdown = preg_replace("/\[caption(.*?)\]/", "", $markdown);
	$markdown = preg_replace("/\[\/caption\]/", "", $markdown);

// Prepare various bits of content for the export

	$tmptitle = str_replace(' ', '-', $article['title']);
	$noslashes = preg_replace('/[^A-Za-z0-9\-]/', '', $tmptitle);
	$image_name = basename($article['image']);
	$tmpyear = date('Y', strtotime($article['pubDate']));
	$tmpdate = date('Y/Ymd', strtotime($article['pubDate']));
	$file = $exportdir . $tmpdate . '-' . $noslashes . '/article.txt';
	$file_image = $exportdir . $tmpdate . '-' . $noslashes . '/' . $image_name;
	$folder = $exportdir . $tmpdate . '-' . $noslashes;

// Create the directory for the export

	if (!mkdir($folder, 0777, true)) {
		die('Failed to create folders...'. $folder);
	}

// Compile the content for the export

	$strtowrite = "Title: " . $article['title']
		. PHP_EOL . "----" . PHP_EOL
		. "Date: " . $article['pubDate']
		. PHP_EOL . "----" . PHP_EOL
		. "Category: " . implode(', ', $categories)
		. PHP_EOL . "----" . PHP_EOL
		. "Summary: "
		. PHP_EOL . "----" . PHP_EOL
		. "Tags: " . implode(', ', $tags)
		. PHP_EOL . "----" . PHP_EOL
		. "Coverimage: " . $image_name
		. PHP_EOL . "----" . PHP_EOL
		. "Text: " . $markdown;

// Save the article.txt file

	file_put_contents($file, $strtowrite);

// Save the image file associated with the post, if there is one

	if ($article['image']) {
		file_put_contents($file_image, $article['image_data']);
	}

// Report what happened

	echo 'File written: ' . $file . ' at ' . date('Y-m-d H:i:s') . '<br />';

}

?>
