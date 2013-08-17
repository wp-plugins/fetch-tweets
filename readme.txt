=== Fetch Tweets ===
Contributors: Michael Uno, miunosoft
Donate link: http://en.michaeluno.jp/donate
Tags: twitter, tweets, tweet, widget, widgets, post, posts, page, pages, custom post type, API, Twitter API, REST, oAuth, shortcode, sidebar, plugin, template
Requires at least: 3.2
Tested up to: 3.5.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Fetches and displays tweets from twitter.com with the Twitter API.

== Description ==

<h4>Features</h4>
* **User Timeline** - by specifying the user name, the timeline can be fetched and displayed.
* **Search Results** - by specifying the search keyword, the results can be fetched and displayed.
* **Mashups** - you can display the combined results from multiple rule sets of your choosing.
* **Widget** - tweets can be displayed in the widgets that the plugin provides.
* **Shortcode** - with the shortcode, the fetched tweets can be displayed in posts and pages.
* **PHP Code** - with the PHP function, the fetched tweets can be embeded in the templates.
* **Custom Templates** - you can change the design by modifying/creating the template file.
* **Background Cache Renewal** - it renews the caches in the background so it will prevent the page load from suddenly getting stuck for fetching external sources. 
 
== Installation ==

= Install = 

1. Upload **`fetch-tweets.php`** and other files compressed in the zip folder to the **`/wp-content/plugins/`** directory.,
2. Activate the plugin through the 'Plugins' menu in WordPress.

= How to Use = 
1. Set a rule via **Dashboard** -> **Fetch Tweets** -> **Add Rule by USer Name** / **Add Rule by Keyword Search**.
2. To use it as a widget, go to **Appearance** -> **Widgets** and add **Fetch Tweets by Rule Set** to the desired sidebar. And select the rule in the widget form.
3. To use the shortcode to display tweets in posts and pages, simply enter the shortcode like below in the post,

`[fetch_tweets id="123"]` 

where 123 is the rule ID you just created. The ID can be found in the *Fetch Tweets* page in the administratin panel.

Go to the [Other Notes](http://wordpress.org/extend/plugins/fetch-tweets/other_notes/) section for more usage details.

== Other Notes ==

= Shortcode and Function Parameters =
The following parameters can be used for the shortcode or the PHP function of the plugin, <code>fetchTweets()</code>

* **id** - the ID(s) of the rule set. This cannot be used with the `tag` parameter. e.g.

`[fetch_tweets id="123"]`

`<?php fetchTweets( array( 'id' => 123 ) ); ?>`

In order to set multiple IDs, pass them with commas as the delimiter. e.g.

`[fetch_tweets id="123, 234, 345"]`

`<?php fetchTweets( array( 'id' => 123, 234, 345 ) ); ?>`

* **tag** - the tag(s) associated with the rule sets. This cannot be used with the `id` parameter. e.g.

`[fetch_tweets tag="WordPress"]`

`<?php fetchTweets( array( 'tag' => 'WordPress' ) ); ?>`

In order to set mutiple tags, pass them with commas as the delimiter. e.g.

`[fetch_tweets tag="WordPress, developer"]`

`<?php fetchTweets( array( 'tag' => 'WordPress, developer' ) ); ?>`

* **operator** - the database query operator that is performed with the *tag* parameters. Either **AND**, **NOT IN**, or **IN** can be used. If this parameter is not set, AND will be used as the default value. For more information about this operator, refer to the [Taxonomy Parameter](http://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters) section of Codex. e.g.

`[fetch_tweets tag="WordPress, PHP, JavaScript" operator="IN" ]`

`<?php fetchTweets( array( 'tag' => 'WordPress, PHP, JavaScript', 'operator' => 'IN' ) ); ?>`

`[fetch_tweets tag="developer" operator="NOT IN" ]`

`<?php fetchTweets( array( 'tag' => 'developer', 'operator' => 'NOT IN' ) ); ?>`

* **count** - the maximum number of tweets to display. e.g.

`[fetch_tweets id="456, 567" count="10" ]`

`<?php fetchTweets( array( 'id' => 456, 567, 'count' => 10 ) ); ?>`

* **avatar_size** - the size( max-width ) of the profile image in pixel. e.g.

`[fetch_tweets id="678" count="8" avatar_size="96" ]`

`<?php fetchTweets( array( 'id' => 678, 'count' => 8, 'avatar_size' => 96 ) ); ?>`

= How to Create Own Template =
**Step 1**
Copy the folder named ***plain*** in the plugin's template folder. Rename the copied folder to something you like.

**Step 2**
Edit the following files.
* **style.css**
* **template.php**
* **functions.php** ( optional )
* **settings.php** ( optional )

In the *style.css* file, include the comment area ( with /* */ ) at the top of the file with the following entries.
* **Template Name:** - the template name.
* **Author:** - your name.
* **Author URI:** - your web site url.
* **Description:** - a brief description about the template.
* **Version:** - the version number of the template.

e.g.

`/*
	Template Name: Sample
	Author: Michael Uno
	Author URI: http://en.michaeluno.jp
	Description: A very simple sample template added as a WordPress plugin.
	Version: 1.0.0
*/`

**Step 3** ( optional )
Include a thumbnail image. Prepare an image with the name screenshot.jpg, screenshot.png, or screenshot.gif, and place the image in the working(copied) folder.

**Step 4**
Create a folder named **fetch-tweets** in the theme folder. If you use, for instance, Twenty Twelve, the location would be `.../wp-content/themes/twentytwelve/fetch-tweets/`.

Place the working folder( the copied and renamed one ) in there. The plugin will automatically detect it and the template will be listed in the Template page of the admin page.

Optionally, a template can be added via a plugin. If you do so, add the template directory with the <code>fetch_tweets_filter_template_directories</code> filter hook.

e.g.
`add_filter( 'fetch_tweets_filter_template_directories', 'FetchTweets_AddSampleTemplateDirPath' );
function FetchTweets_AddSampleTemplateDirPath( $arrDirPaths ) {
	
	// Add the template directory to the passed array.
	$arrDirPaths[] = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample';
	return $arrDirPaths;
	
}`


== Frequently Asked Questions ==

= Where can I get the API keys? =
First you need to create an application to access the Twitter API [here](https://dev.twitter.com/apps). Then create *consumer key*, *consumer secret*, *access token*, and *access token secret*. Without these, you won't be able to fetch tweets.

= How can I create my own template file? =
See the How to Create Own Template section of the **[Other Notes]**(http://wordpress.org/plugins/responsive-column-widgets/other_notes/) page.

== Screenshots ==

1. ***Tweets Displayed in Page and Sidebar***
2. ***Fetching Rule List***
3. ***Widget Settings***

== Changelog ==

= 1.1.0 - 08/18/2013 = 
* Added the ability to reset the plugin options.
* Added the templates named **Single** and **Plain**.
* Changed the template system ( ***Breaking Change*** ).
* Changed to display the error message when the Twitter API returns an error. 

= 1.0.1 - 07/29/2013 =
* Added the ability for other plugins to override the registering classes of this plugin.
* Supported third party extensions to be added.
* Added the *widget-title* class selector to the widget title output.
* Changed the sub-menu positions and the menu name of the rule listing table page to Manage Rule from the plugin name.
* Changed the *ids* and *tags* parameters to be obsolete. These will be removed in near updates.
* Changed the *id* and *tag* parameters to accept comma-delimited elements like the *ids* and *tags* parameters. 
* Changed the variables passed to the template file. ( ***Breaking Change*** )

= 1.0.0.3 - 07/23/2013 =
* Added the ability to convert media links to the hyper links.
* Tweaked the default template style.
* Added the title field for the widget form.

= 1.0.0.2 - 07/22/2013 =
* Added the *operator* parameter for the *tag* and *tags* parameters that specifies the use of *AND*, *IN*, or *NOT IN* for the database query.
* Added the *tags* parameter that enables to fetch tweets with multiple tags.
* Added a widget that fetches tweets by tag.
* Fixed a bug that profile images get lost with the *tag* parameter since 1.0.0.1.
* Fixed a bug that caused a warning in the background, "PHP Warning:  in_array(): Wrong datatype for second argument in ...\wp-content\plugins\fetch-tweets\class\FetchTweets_WidgetByID_.php on line 76"

= 1.0.0.1 - 07/21/2013 =
* Added the *avater_size* parameter for the *fetchTweets()* function and the shortcode.
* Added the ability to specify the profile image size as well as the visibility of the image.
* Fixed a bug that caused a warning in the background, "PHP Notice: Undefined index: title in ...\wp-admin\includes\meta-boxes.php on line 352"
* Fixed a bug that some transients did not get renewed. 
* Tweaked the precision of converting urls, hashtags, and user mentions to the hyper-links.

= 1.0.0 - 07/20/2013 =
* Initial Release.
