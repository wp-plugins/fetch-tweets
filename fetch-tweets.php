<?php
/* 
	Plugin Name: Fetch Tweets
	Plugin URI: http://en.michaeluno.jp/fetch-tweets
	Description: Fetches and displays tweets from Twitter with the the Twitter REST API v1.1.
	Author: miunosoft (Michael Uno)
	Author URI: http://michaeluno.jp
	Version: 1.0.0.3
	Requirements: PHP 5.2.4 or above, WordPress 3.2 or above.
*/ 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include_once( dirname( __FILE__ ) . '/class_final/FetchTweets_InitialLoader.php' );
new FetchTweets_InitialLoader( __FILE__ );

final class FetchTweets_Commons {
	
	public static $strPluginKey = 'fetch_tweets';
	public static $strAdminKey = 'fetch_tweets_admin';
	public static $strOptionKey = 'fetch_tweets_option';
	
	const TextDomain = 'fetch-tweets';
	const PluginName = 'Fetch Tweets';
	const PostTypeSlug = 'fetch_tweets';
	const Tag = 'fetch_tweets_tag';
	
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
	public static function getPluginURL( $strRelativePath='' ) {
		return plugins_url( $strRelativePath, __FILE__ );
	}
	
}

/*
 * User functions - users may use them in their templates.
 * */
function fetchTweets( $arrArgs ) {
	
	$oFetch = new FetchTweets_Fetch();
	if ( isset( $arrArgs['id'] ) ) 
		$oFetch->drawTweets( 
			$arrArgs['id'],
			isset( $arrArgs['count'] ) ? $arrArgs['count'] : null, 
			isset( $arrArgs['sort'] ) ? $arrArgs['sort'] : null,
			isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : null
		);	
	else if ( isset( $arrArgs['ids'] ) )	
		$oFetch->drawTweets( 
			is_array( $arrArgs['ids'] ) ? $arrArgs['ids'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['ids'] ), 0, PREG_SPLIT_NO_EMPTY ),
			isset( $arrArgs['count'] ) ? $arrArgs['count'] : null, 
			isset( $arrArgs['sort'] ) ? $arrArgs['sort'] : null,
			isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : null
		);
	else if ( isset( $arrArgs['tag'] ) ) 
		$oFetch->drawTweetsByTag( 
			trim( $arrArgs['tag'] ), 
			isset( $arrArgs['count'] ) ? $arrArgs['count'] : null, 
			isset( $arrArgs['sort'] ) ? $arrArgs['sort'] : null,
			isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : null
		);
	else if ( isset( $arrArgs['tags'] ) ) 
		$oFetch->drawTweetsByTag( 
			is_array( $arrArgs['tags'] ) ? $arrArgs['tags'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['tags'] ), 0, PREG_SPLIT_NO_EMPTY ),
			isset( $arrArgs['count'] ) ? $arrArgs['count'] : null, 
			isset( $arrArgs['sort'] ) ? $arrArgs['sort'] : null,
			isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : null,
			isset( $arrArgs['operator'] ) ? $arrArgs['operator'] : null
		);		

}

/*
 * For templates
 * */
function FetchTweets_humanTiming( $time ) {

	// by arnorhs http://stackoverflow.com/a/2916189
    $time = time() - $time; // to get the time since that moment

    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
    }
}