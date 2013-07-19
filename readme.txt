=== Fetch Tweets ===
Contributors: Michael Uno, miunosoft
Donate link: http://en.michaeluno.jp/donate
Tags: twitter, tweets, tweet, widget, widgets, post, posts, API, oAuth
Requires at least: 3.2
Tested up to: 3.5.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Fetches tweets from twitter.com with the Twitter API.

== Description ==

<h4>Features</h4>
* **User Timeline** - by specifying the user name, the time line can be fetched.
* **Search Results** - by specifying the search keyword, the results can be fetched.
* **Widget** - tweets can be displayed in the widget that the plugin provides.
* **Shortcode** - with the shortcode, the fetched tweets can be displayed in posts and pages.
* **PHP Code** - with the PHP function, the fetched tweets can be embeded in the templates.
* **Custom Templates** - the user can change how the results get shown by modifying/creating the template file.
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
* **id** - the ID of the rule set. This cannot be used with the `ids` and `tag` parameters. e.g.

`[fetch_tweets id="123"]`

`fetchTweets( array( 'id' => 123 ) )`

* **ids** - the IDs of the rule set separated by commas. This cannot be used with the `id` and `tag` parameters. e.g.

`[fetch_tweets ids="123, 234, 345"]`

`fetchTweets( array( 'ids' => 123, 234, 345 ) )`

* **tag** - the tag associated with the rule sets. This cannot be used with the `id` and `ids` parameters. e.g.

`[fetch_tweets tag="WordPress"]`

`fetchTweets( array( 'tag' => 'WordPress' ) )`

* **count** - the maximum number of tweets to display.

== Frequently Asked Questions ==

= Where can I get the API keys? =
First you need to create an application to access the Twitter API here[https://dev.twitter.com/apps]. And create *consumer key*, *consumer secret*, *access token*, and *access token secret*. Without these, you won't be able to fetch tweets.

= How can I create my own template file? =
Edit the file named *show_tweets.php* in the *template* directory of the plugin. Create a directory named *fetch-tweets* under the current theme directory and put the modified file there. If you use, for instance, Twenty Twelve, place the file as follows:

`wp-content\themes\twentytwelve\fetch-tweets\show_tweets.php`

== Screenshots ==

== Changelog ==

= 1.0.0 =
* Initial Release
