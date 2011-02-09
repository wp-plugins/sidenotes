<?php
	header("Content-Type: application/xml; charset=UTF-8");  
	require_once("wp-blog-header.php");
	echo ("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
	
	// Database Connection
	global $wpdb, $prefix;
	$sidenotes_table = $wpdb->prefix . "sidenotes";

	// Last published
	$ResLastPublished = $wpdb->get_row( "SELECT MAX(id), time_updated FROM $sidenotes_table" );
	$lastPublished = $ResLastPublished->time_updated;
	$blog_siteurl = get_option('siteurl');
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action('rss2_ns'); ?>
>
	<channel>
		<title><?php bloginfo('name');?> - Sidenotes</title> 
		<link><?php echo $blog_siteurl; ?></link>
		<description><?php bloginfo('description'); ?></description> 
		<language><?php echo get_option('rss_language'); ?></language> 
		<pubDate><?php echo gmdate(DATE_RSS, strtotime($lastPublished)) ?></pubDate>
		<lastBuildDate><?php echo gmdate(DATE_RSS, strtotime($lastPublished)) ?></lastBuildDate>
		<docs><?php echo $blog_siteurl; ?>/sidenotes-rss.php</docs>
		<generator>Rss Feed Engine</generator>
		<webMaster><?php echo get_option('admin_email'); ?></webMaster>
		<atom:link href="<?php echo $blog_siteurl."/sidenotes-rss.php"; ?>" rel="self" type="application/rss+xml" />
<?php
// Get number of feed entries to show
$sidenotes_feed_number = get_option('sidenotes_feed_number');
if($sidenotes_feed_number != "" && is_numeric($sidenotes_feed_number)) {
	$tmp_sql = " LIMIT 0, ".$sidenotes_feed_number;
}
$ResSidenotes = $wpdb->get_results( "SELECT id,title,url,description,time_updated FROM $sidenotes_table ORDER BY id DESC".$tmp_sql );

foreach($ResSidenotes as $result) {
	echo "
	<item>
		<title>".$result->title."</title>
		<description>".$result->description."</description>
		<link>".$result->url."</link>
		<guid>".$blog_siteurl."/sidenotes-rss.php?item".$result->id."</guid>
		<pubDate>".gmdate(DATE_RSS, strtotime($result->time_updated))."</pubDate>
	</item>
	";
}
?>
	</channel>
</rss>