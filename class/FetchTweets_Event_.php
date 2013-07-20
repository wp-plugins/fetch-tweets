<?php
/**
	Event handler.
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 * 
	
*/
abstract class FetchTweets_Event_ {

	public function __construct() {
		
		// For transient (cache) renewal events
		if ( isset( $_GET['doing_wp_cron'] ) )	// if WP Cron is the one which loaded the page,
			add_action( 'FTWS_action_transient_renewal', array( $this, 'renewTransients' ) );	
			
		// For SimplePie cache renewal events 
		if ( isset( $_GET['doing_wp_cron'] ) )	// if WP Cron is the one which loaded the page,
			add_action( 'FTWS_action_simplepie_renew_cache', array( $this, 'renewSimplePieCaches' ) );
				
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
		add_action( 'FTWS_action_setup_transients', array( $this, 'setUpTransients' ) );
		
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
	
	public function renewTransients( $arrRequestURIs ) {

// $oDebug	= new FetchTweets_Debug;
// $oDebug->getArray( $arrRequestURIs, dirname( __FILE__ ) . '/request_uris.txt' );

		$oFetch = new FetchTweets_Fetch;
		foreach( ( array ) $arrRequestURIs as $arrRewuestURI ) {
			
			// $arrRewuestURI['URL'] 
			// $arrRewuestURI['type'] - screen_name or search
			
			$arrTweets = ( array ) $oFetch->oTwitterOAuth->get( $arrRewuestURI['URI'] );
			
			// If there is a problem, skip.
			if ( $arrRewuestURI['type'] == 'search' && ! isset( $arrTweets['statuses'] ) ) continue;
			
			$arrTweets = $arrRewuestURI['type'] == 'search' ? $arrTweets['statuses'] : $arrTweets;
			if ( empty( $arrTweets ) ) continue;
			
			// Save the cache
			set_transient(
				'FTWS_' . md5( $arrRewuestURI['URI'] ), 
				array( 'mod' => time(), 'data' => $arrTweets ), 
				9999999999 // this barely expires by itself. $intCacheDuration 
			);			
			
		}
	}
}