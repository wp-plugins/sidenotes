=== Sidenotes ===
Contributors: stephanlenhart
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=8SUV3C5UHSCQG&lc=AT&item_name=Stephan%20Lenhart&item_number=sidenotes&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: sidenotes, notes, posts, links, rss
Requires at least: 2.3
Tested up to: 2.8.4
Stable tag: 0.9.3

Simply add short side notes to your site, showing a date, title, description and a link. An additional RSS feed provides easy syndication.


== Description ==
This plugin provides the possibility to simply add short side notes to your Wordpress blog, showing the date of publication, a title and a description. In addition the plugin provides a link to the site the side notes refers to. Simply activate it and add new side notes within the "Tools" admin panel. To show off your side notes just put <code>&lt;?php get_sidenotes(); ?&gt;</code> in your template. For easy syndication this plugin provides an additional RSS feed too. Enjoy!

Related Links:
http://www.uidesign.at/journal/2009/09/14/sidenotes-wordpress-plugin-side-notes-for-your-blog/


== Installation ==
1. Copy the "sidenotes"-folder to your /wp-content/plugins/ directory.
2. Copy sidenotes-rss.php from your /wp-content/plugins/ directory to your root directory (where you can also find files like wp-app.php, wp-atom.php,...)
3. Activate the plug-in through the "Plugins" menu in your WordPress admin panel and do some additional setup in the "Settings" menu if you want.
4. Write your first side note within the "Tools" menu
5. Place get_sidenotes() somewhere in your code


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
* New feature: Output all (or specific number of) Sidenotes via <code>&lt;?php get_sidenotes_archives(); ?&gt;</code>