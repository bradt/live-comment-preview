=== Live Comment Preview ===
Contributors: bradt
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5VPMGLLK94XJC
Tags: comment, comments, preview
Requires at least: 1.5
Tested up to: 3.2
Stable tag: 2.0.1

Displays a preview of the user's comment as they type it.

== Description ==

= New in Version 2.0 =

* Zero configuration required / intelligent parsing of theme comment HTML
* Disallowed HTML tags are stripped from the preview (thanks to Jamie Zawinski)

Live Comment Preview (LCP) allows your users to see how their comment will
appear on the site as they type it.

Uses client-side Javascript only (no Ajax requests to the server) which means a
responsive, smooth, live comment preview.

== Installation ==

Use WordPress' built-in plugin installer, or do a manual install:

1. Download live-comment-preview.zip
2. Unzip the archive
2. Upload the live-comment-preview folder to your wp-content/plugins directory
3. Activate the plugin through the WordPress admin interface

Enjoy!

== Screenshots ==

1. Comment preview in WordPress' default theme Twenty Ten

== Frequently Asked Questions ==

= Why isn't the comment preview showing up? =

First, check your theme's comments.php file. It must contain the code
<code>&lt;?php comment_form(); ?&gt;</code> or <code>&lt;?php
do_action('comment_form', $post->ID); ?&gt;</code>. If it doesn't, you will need
to add it in.

Second, if the comment form fields do not have the proper id values (same ones
as the default WordPress theme), the comment preview will not work.

= Can I make the preview display in another location? =

Yes, you can add the code &lt;?php live_preview(); ?&gt; in comments.php where
you want the preview to be show.

= Why doesn't the comment preview look like the rest of the comments? =

Most likely because your theme does not use the standard comment HTML used by
the WordPress default theme. Comments should be wrapped in
<code>&lt;ol class="commentlist"&gt;&lt;/ol&gt;</code>.

= Can I supply my own HTML for the comment preview? =

Yes, simply create a file called comment-preview.php in your theme folder and
insert the strings COMMENT_CONTENT, COMMENT_AUTHOR, and AVATAR_URL where you would
like the respective content to show up.

== Changelog ==

= 2.0.1 (2011-09-08) =
* Bug fix: [Doesn't appear the preview in 2.0](http://wordpress.org/support/topic/plugin-live-comment-preview-doesn«t-appear-the-preview-in-20)

= 2.0 (2011-09-04) =
* Bug fix: Avatar images not the correct size for Twenty Eleven theme
* Bug fix: [The plugin does not have a valid header](http://wordpress.org/support/topic/plugin-live-comment-preview-the-plugin-does-not-have-a-valid-header)
* Bug fix: [error rendering html ending in a digit](http://wordpress.org/support/topic/plugin-live-comment-preview-error-in-rendering-html-ending-in-a-digit)

= 2.0b1 (2011-01-16) =
* Zero configuration required / intelligent parsing of theme comment HTML
* Disallowed HTML tags are stripped from the preview (thanks to Jamie Zawinski)

= 1.9 (2008-04-19) =
* Added support for WordPress 2.5's gravatar settings.
* [Several Bug Fixes](http://dev.wp-plugins.org/log/live-comment-preview?action=stop_on_copy&rev=41675&stop_rev=28426&mode=stop_on_copy&verbose=on)

= 1.8.2 (2007-12-03) =
* Bug fix: Only works if blog url is the web site root.

= 1.8.1 (2007-12-02) =
* Bug fix: Javascript doesn't load for users who have WP in a subdirectory.

= 1.8 (2007-11-29) =
* First release by Brad Touesnard
* Added [Gravatar](http://www.gravatar.com/) support

= 1.7 (2005-06-05) =
* Last release by Jeff Minard

== Upgrade Notice ==

= 2.0 =
This is a stable release containing several small bug fixes.

== Thanks! ==

Thanks to [Jeff Minard](http://jrm.cc/) for developing this plugin originally and also to [Iacovos Constantinou](http://www.softius.net/) for his JS functions for parsing the comment text.
