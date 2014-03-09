<?php
/* 
	Plugin Name: Fetch Tweets
	Plugin URI: http://en.michaeluno.jp/fetch-tweets
	Description: Fetches and displays tweets from twitter.com with the the Twitter REST API v1.1.
	Author: miunosoft (Michael Uno)
	Author URI: http://michaeluno.jp
	Version: 2b01
	Requirements: PHP 5.2.4 or above, WordPress 3.3 or above.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include_once( dirname( __FILE__ ) . '/class_final/FetchTweets_Bootstrap.php' );
new FetchTweets_Bootstrap( __FILE__ );

/*
 * User functions - users may use them in their templates.
 * */
function fetchTweets( $aArgs, $bEcho=true ) {
	
	$_sOutput = '';
	if ( ! class_exists( 'FetchTweets_Fetch' ) ) {
		$_sOutput = __( 'The class has not been loaded yet. Use this function after the <code>plugins_loaded</code> hook.', 'fetch-tweets' );
		if ( $bEcho ) {
			echo $_sOutput;
		} else {
			return $_sOutput;
		}
	}
	
	$_oFetch = new FetchTweets_Fetch();
	if ( isset( $aArgs['id'] ) || isset( $aArgs['ids'] ) || isset( $aArgs['q'] ) || isset( $aArgs['screen_name'] ) ) {
		$_sOutput = $_oFetch->getTweetsOutput( $aArgs );
	} else if ( isset( $aArgs['tag'] ) || isset( $aArgs['tags'] ) ) {
		$_sOutput = $_oFetch->getTweetsOutputByTag( $aArgs );
	}

	if ( $bEcho ) {
		echo $_sOutput;
	} else {
		return $_sOutput;
	}
		
}

