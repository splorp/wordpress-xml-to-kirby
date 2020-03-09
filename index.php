<?php

/*
 * Functionality to convert Wordpress a export to something closer to the Kirby structure,
 * in order to help Paul Swain's specific migration needs.
 * Uses HTML to Markdown functionality by Nick Cernis - https://github.com/nickcernis/html-to-markdown
 */
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);

require_once('html_to_markdown.php');


//Define the namespaces used in the XML document
$ns = array (
    'excerpt' => "http://wordpress.org/export/1.2/excerpt/",
    'content' => "http://purl.org/rss/1.0/modules/content/",
    'wfw' => "http://wellformedweb.org/CommentAPI/",
    'dc' => "http://purl.org/dc/elements/1.1/",
    'wp' => "http://wordpress.org/export/1.2/"
);

//Get the contents of the import file
$exportdir = 'export/'; //Include training slash please
$importfile = 'typecon.wordpress.2020-03-08.speakers.xml';
$xml = file_get_contents($importfile);
$xml = new SimpleXmlElement($xml);

//Grab each item

foreach ($xml->channel->item as $item)
{
    $article = array();
    $article['title'] = $item->title;
    $article['link'] = $item->link;
    $article['pubDate'] = date('m/d/Y', strtotime($item->pubDate));
    $article['timestamp'] = strtotime($item->pubDate);
    $article['description'] = (string) trim($item->description);
    $article['image'] = (string) trim($item->children($ns['wp'])->attachment_url);

    $article['image_data'] = file_get_contents($article['image']);

    //Get the category and tags for each post
    $tags = array();
    $categories = array();
    foreach ($item->category as $cat) {
        $cattype = $cat['domain'];

        if($cattype == "post_tag") { //Tags
            array_push($tags,$cat);
        }
        elseif($cattype == "category") { //Category
            array_push($categories,$cat);
        }
    }

    //Get data held in namespaces
    $content = $item->children($ns['content']);
    $wfw     = $item->children($ns['wfw']);

    $article['content'] = (string) trim($content->encoded);
    $article['content'] = mb_convert_encoding($article['content'], 'HTML-ENTITIES', "UTF-8");
    $article['commentRss'] = $wfw->commentRss; //Not used by Paul at present, but may be in future

    //Convert to markdown - optional param to strip tags
    $markdown = new HTML_To_Markdown($article['content'], array('strip_tags' => true));

    //Addition for conversion - strip Wordpress shortcodes for captions
    $markdown = preg_replace("/\[caption(.*?)\]/", "", $markdown);
    $markdown = preg_replace("/\[\/caption\]/", "", $markdown);

    //Save to file
    $tmptitle = str_replace(' ', '-', $article['title']);
    $noslashes = preg_replace('/[^A-Za-z0-9\-]/', '', $tmptitle);
    $image_name = basename($article['image']);
    $tmpyear = date('Y', strtotime($article['pubDate']));
    $tmpdate = date('Y/Ymd', strtotime($article['pubDate'])); //You don't want slashes, or it'll look for directories
    $file = $exportdir . $tmpdate . '-' . $noslashes . '/article.txt';
    $file_image = $exportdir . $tmpdate . '-' . $noslashes . '/' . $image_name;
    $folder = $exportdir . $tmpdate . '-' . $noslashes;
    if (!mkdir($folder, 0777, true)) {
      die('Failed to create folders...'. $folder);
    }

    //Compile the content of the file to write
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

    // put article
    file_put_contents($file, $strtowrite);
    // put image
    file_put_contents($file_image, $article['image_data']);
    echo 'File written: ' . $file . ' at ' . date('Y-m-d H:i:s') . '<br />';

}

?>
