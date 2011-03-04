=== Good Reads ===
Contributors: iamgarrett
Donate link: Not at this time.
Tags: blogroll, links, rss, blogs, sidebar, widget, atom
Requires at least: 3.0
Tested up to: 3.1
Stable tag: trunk

An ordered blogroll widget for your sidebar that displays your favorite blogs, what they're writing, and when.

== Description ==

Google's Blogger tool has a great blogroll gadget, which I've always wanted in WordPress. I tried a few plugins that advertised similar behavior but was never quite satisfied - so I made my own.

This will grab all your links with a category of 'sidebar' and show them in a list on your sidebar. Specify each link's RSS/Atom feed address and it will grab the latest post, a link, and reorder the list based on when these posts were written. It only requires that you have jQuery and PHP and uses the Links and Widget section already included in WordPress.

== Installation ==

1. Upload 'good-reads.php' to the 'wp-content/plugins' directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to the 'Widgets' screen in 'Appearance' and add 'Good Reads' to your Sidebar.
1. Go to your WordPress' 'Links' and create a category called 'Sidebar'.
1. Add this category and the feed URL (in the RSS Address field) to each link you want to appear in 'Good Reads'.

== Frequently Asked Questions ==

= Hey! My links wont order themselves by the publish date of their latest article! What gives!? =

1. Make sure that the feed url is correct in WP.
1. Make sure you have the widget set to order them by publish date.
1. Make sure you have jQuery linked in your site's header.

== Screenshots ==

1. A 'Good Reads' module in my sidebar with four sites in the 'sidebar' category.

== Changelog ==

= 1.5 =
* Restructured the way the plugin and widget are built to meet with more recent WordPress standards.
* New widget option: Ordering - Choose to have your links listed by publish date or by site title.
* New widget option: Tabs - Choose if your links open in a new tab or in the same tab.

= 20110218 (1.0) =
* Public release!

== Upgrade Notice ==

= 1.5 = 
* You may need to go to your Widgets menu in WP, open the Good Reads menu and click 'Save' to enact new settings.
* Tested with WP 3.1
* More control