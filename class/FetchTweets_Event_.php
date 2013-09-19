<?php
/**
	Event handler.
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 * @actionhooks
 *  - fetch_tweets_action_setup_transients
 *  - fetch_tweets_action_simplepie_renew_cache
 *  - fetch_tweets_action_transient_renewal
	
*/
abstract class FetchTweets_Event_ {

	public function __construct() {
		
		// For transient (cache) renewal events
		if ( isset( $_GET['doing_wp_cron'] ) )	// if WP Cron is the one which loaded the page,
			add_action( 'fetch_tweets_action_transient_renewal', array( $this, 'renewTransients' ) );	
			
		// For SimplePie cache renewal events 
		if ( isset( $_GET['doing_wp_cron'] ) )	// if WP Cron is the one which loaded the page,
			add_action( 'fetch_tweets_action_simplepie_renew_cache', array( $this, 'renewSimplePieCaches' ) );
				
		// Redirects
		if ( isset( $_GET['fetch_tweets_link'] ) && $_GET['fetch_tweets_link'] ) {			
			$oRedirect = new FetchTweets_Redirects;
			$oRedirect->go( $_GET['fetch_tweets_link'] );	// will exit there.
		}
			
		// Draw cached image.
		if ( isset( $_GET['fetch_tweets_image'] ) && $_GET['fetch_tweets_image'] ) {
			
			$oImageLoader = new FetchTweets_ImageHandler( 'FTWS' );
			$oImageLoader->draw( $_GET['fetch_tweets_image'] );
			exit;
			
		}			
		
		// For the activation hook
		add_action( 'fetch_tweets_action_setup_transients', array( $this, 'setUpTransients' ) );
		
		// Load styles of templates
		add_action( 'wp_enqueue_scripts', array( $GLOBALS['oFetchTweets_Templates'], 'enqueueActiveTemplateStyles' ) );
		if ( isset( $_GET['fetch_tweets_style'] ) )
			$GLOBALS['oFetchTweets_Templates']->loadStyle( $_GET['fetch_tweets_style'] );
			
		// add_action( 'admin_enqueue_scripts', array( $GLOBALS['oFetchTweets_Templates'], 'enqueueActiveTemplateStyles' ) );
		
	}

	public function setUpTransients() {
		
		$oUA = new FetchTweets_UserAds();
		$oUA->setupTransients();		
		
	}
	
	public function renewSimplePieCaches( $vURLs ) {
		
		// Setup Caches
		$oFeed = new FetchTweets_SimplePie();

		// Set urls
		$oFeed->set_feed_url( $vURLs );	
		
		// this should be set after defining $vURLs
		$oFeed->set_cache_duration( 0 );	// 0 seconds, means renew the cache right away.
	
		// Set the background flag to True so that it won't trigger the event action recursively.
		$oFeed->setBackground( true );
		$oFeed->init();	
		
	}
	
	public function renewTransients( $arrRequestURI ) {

// Debug
// FetchTweets_Debug::getArray( $arrRequestURI, dirname( __FILE__ ) . '/request_uris.txt' );

		$oFetch = new FetchTweets_Fetch;
		$oFetch->setAPIGETRequestCache( $arrRequestURI['URI'], $arrRequestURI['key'] );

	}
}