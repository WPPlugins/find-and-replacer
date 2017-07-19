=== Find and Replacer ===
Contributors: wesg
Tags: multiple, mass, easy, find, search, replace, modify
Requires at least: 2.0
Tested up to: 2.7
Stable tag: 1.6

Find and replacer is a powerful search plugin for replacing tags and text throughout your blog installation.

== Description ==

Find and Replacer does exactly what the the name implies: it enables users to search through their entire blog and replace phrases, words, or *even tags* without modifying any other text. Sure there are similar plugins available, but none give the same power to modify post content, titles or even comments.

Currently tested successfully with the latest version of Wordpress, 2.6.2. If you have trouble with earlier versions, or even have it work properly on earlier versions, please let me know. Due to the complexity of blogs and the different conditions this plugin must adapt to, please contact me when the plugin does not give the desired output.

For a complete list of the changes from each version, please visit <a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#changelog">the plugin homepage</a> and for more detail visit <a href="http://www.wesg.ca/2008/10/find-and-replacer-dvelopment-detail/">the development details</a>.

For examples and tips on using the plugin, please check <a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#examples">the examples</a> on the plugin homepage.

Be sure to check out my other plugins at <a href="http://wordpress.org/extend/plugins/profile/wesg">my Wordpress profile</a>.

= TRANSLATIONS =

Currently the plugin includes the fr_FR French translation. If you are interested in translating Find and Replacer into your native language, please <a href="http://www.wesg.ca/contact/">contact me</a>. If you are using the French version, watch this page for the latest translation text.

= LATEST CHANGES =

**Version 1.5** brings a number of advanced changes to the plugin. In addition to improved search behavior, users can now search through comments and make replacements there. Revision interaction has also changed. Database records are no longer overwritten regardless of post status -- you can skip revisions and only make changes to displayed posts. Database efficiency has also been increased, as changes are only written to the database if they are actually different than the previous version. Also, changes that don't require REGEX are made without preg_match, to increase accuracy.

**It is highly recommended that you keep revisions enabled and use the default revision behavior. This way, should the plugin make a change you don't like, you can return to an earlier state.**

= USAGE =

1. After activating the plugin, navigate to the admin panel interface, where the options can be entered to edit the correct pages.
1. Be sure to enter the correct data to modify only the specific posts.

= LIMITATIONS =

Since it is impossible to know every use this plugin would work with, there are some conditions that can break functionality. If you find a condition that doesn't produce the correct result, please contact me on <a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#respond">the plugin homepage</a>. Additionally, some database types used with other languages may pose a problem. Try to search for the same characters in the database and everything should work properly.

== Installation ==

1. Copy the folder find-and-replacer into your WordPress plugins directory (wp-content/plugins).
1. Log in to WordPress Admin. Go to the Plugins page and click Activate.
1. Navigate to the Admin Panel for Find and Replacer and edit away (the plugin creates a page under Options).

== Frequently Asked Questions ==


= What is the purpose of this plugin? =

With blogs growing larger and larger all the time, Find and Replacer makes it super easy to edit pages throughout your blog installation. Change everything from words and phrases to complete tags.

= What options are available? =

In the interface panel, you have the ability to fine tune the editing process. You can:
	* enter the starting and ending page IDs of posts you want to modify
	* select the entire post database
	* choose between editing the post content, title, or both
	* view page IDs and other data for your posts
	* choose to include comments in the search
	* skip revisions to retain backups
	
= How does it all work? =

Find and Replacer uses the REGEX engine, or *regular expressions*. This is a powerful system built into PHP for finding and replacing text when only specific data is known. This is the only way to replace entire tags in one pass.

= Can I replace tags around other tags? =

Fixed in version 1.1, Find and Replacer can now replace tags even when surrounding other tags.  For more examples please visit <a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#examples">the plugin homepage</a>.

= What if something doesn't give me the result I want? =

Since this type of application can have so many possible scenarios, please make a comment on <a href="http://www.wesg.ca/2008/08/wordpress-plugin-find-and-replacer/#respond">the original blog post</a> when you get undesired results. Please be as specific as possible.

= Can it replace text over multiple lines? =

Yes. If you are replacing text with multiple lines and you want to preserve text inside it, you just need to put the placeholder (*) in the correct spot. For example, if you have 5 lines of whitespace and the text you want to preserve is on the 3rd line, that's where the (*) should be.

= How does Find and Replacer work with revisions? =

As of v1.5, Find and Replacer changes the way it interacts with revisions. Previously it edited each entry in the database as if it were the same. Now, you have the option to skip revisions. This means that if your search does not go the way you want, you can return to a previous revision with no issue.

= Can I modify comments? =

In v1.5, yes.

== Screenshots ==

1. The option panel interface of Find and Replacer.
2. The page list to determine which pages to edit.