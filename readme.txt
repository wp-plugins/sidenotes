=== Sidenotes ===
Contributors: stephanlenhart
Tags: sidenotes, notes, posts, simple, links, rss
Requires at least: 2.3
Tested up to: 3.0.4
Stable tag: 1.0.1

Simply add short side notes to your site, showing a date, title, description and a link. An additional RSS feed provides easy syndication.


== Description ==
This plugin provides the possibility to simply add short side notes to your Wordpress blog, showing the date of publication, a title and a description. In addition the plugin provides a link to the site the side notes refers to. Simply activate it and add new side notes within the "Tools" admin panel. To show off your side notes just put <code>&lt;?php get_sidenotes(); ?&gt;</code> in your template. For easy syndication this plugin provides an additional RSS feed. The default URL of the feed is http://example.com/feed/sidenotes.

Related Links:
http://www.uidesign.at/journal/2009/09/14/sidenotes-wordpress-plugin-side-notes-for-your-blog/


== Installation ==
1. Copy the "sidenotes"-folder to your /wp-content/plugins/ directory.
2. Activate the plug-in through the "Plugins" menu in your WordPress admin panel and do some additional setup in the "Settings" menu if you want.
3. Write your first side note within the "Tools" menu
4. Place get_sidenotes() somewhere in your code


== Frequently Asked Questions ==
No frequently asked questions yet


== Screenshots ==
1. The "Settings" page for the Sidenotes plugin: There are many ways to customize side notes to best fit to your site.
2. The "Tools" page for the Sidenotes plugin: Here you can publish new side notes, edit existing ones or delete them.
3. The result: An example of horizontal side notes in the footer area of your site.


== Changelog ==
= 0.9 =
* Initial version
= 0.9.1 =
* Bug Fix: Cyrillic language support
= 0.9.2 =
* Bug Fix: Display date / time in local language
= 0.9.3 =
* New feature: Output all (or specific number of) Sidenotes via <code>&lt;?get_sidenotes_archives(); ?&gt;</code>
= 1.0 =
* Bug Fix: Update database structure from timestamp to datetime to correctly display date / time
* Bug Fix: Output of side notes entries in correct order
* Bug Fix: Output of side notes in sidenotes-rss.php - please update file in your root-directory!
= 1.0.1 =
* New Feature: Sidenotes RSS feed reworked to better support RSS readers (e.g. avoid HTTP 404 errors). Do **NOT** copy sidenotes-rss.php out of plugin-directory. This is not neccessary anymore.