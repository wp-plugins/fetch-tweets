<?php
/* 
	Plugin Name: Fetch Tweets
	Plugin URI: http://en.michaeluno.jp/fetch-tweets
	Description: Fetches and displays tweets from twitter.com with the the Twitter REST API v1.1.
	Author: miunosoft (Michael Uno)
	Author URI: http://michaeluno.jp
	Version: 1.3.3.4
	Requirements: PHP 5.2.4 or above, WordPress 3.3 or above.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include_once( dirname( __FILE__ ) . '/class_final/FetchTweets_Bootstrap.php' );
new FetchTweets_Bootstrap( __FILE__ );

final class FetchTweets_Commons {
	
	public static $strPluginKey = 'fetch_tweets';
	public static $strAdminKey = 'fetch_tweets_admin';
	public static $strOptionKey = 'fetch_tweets_option';
	
	const TextDomain = 'fetch-tweets';
	const PluginName = 'Fetch Tweets';
	const PostTypeSlug = 'fetch_tweets';
	const PostTypeSlugAccounts = 'fetchtweets_accounts';		// post type slugs cannot exceed 20 characters. 
	const TagSlug = 'fetch_tweets_tag';
	const AdminOptionKey = 'fetch_tweets_admin';
	const PageSettingsSlug = 'fetch_tweets_settings';
	const TransientPrefix = 'FTWS';
	const ConsumerKey = '97LqHiMs06VhV2rf5tUQw';
	const ConsumerSecret = 'FIH9cr0eXtd7q9caYVqBjd5mvfUS6hZqREYsUhh9wA';
	
	public static function getPluginKey() {
		return self::$strPluginKey;
	}
	public static function getAdminKey() {
		return self::$strAdminKey;
	}
	public static function getOptionKey() {
		return self::$strOptionKey;
	}	
	public static function getPluginFilePath() {
		return __FILE__;
	} 
	public static function getPluginDirPath() {
		return dirname( __FILE__ );
	}
	public static function getPluginURL( $strRelativePath='' ) {
		return plugins_url( $strRelativePath, __FILE__ );
	}
	
}

/*
 * User functions - users may use them in their templates.
 * */
function fetchTweets( $arrArgs ) {
	
	$oFetch = new FetchTweets_Fetch();
	if ( isset( $arrArgs['id'] ) || isset( $arrArgs['ids'] ) || isset( $arrArgs['q'] ) || isset( $arrArgs['screen_name'] ) ) 
		$oFetch->drawTweets( $arrArgs );
	else if ( isset( $arrArgs['tag'] ) || isset( $arrArgs['tags'] ) ) 
		$oFetch->drawTweetsByTag( $arrArgs );

}

