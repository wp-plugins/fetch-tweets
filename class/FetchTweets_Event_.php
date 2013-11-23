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
		
		// Objects
		$this->oBase64 = new FetchTweets_Base64;
		
		// if WP Cron is the one which loaded the page,
		if ( isset( $_GET['doing_wp_cron'] ) )	{
			
			// For transient (cache) renewal events
			add_action( 'fetch_tweets_action_transient_renewal', array( $this, 'renewTransients' ) );	
			
			// For transient (cache) formatting events - adds oEmbed elements.
			add_action( 'fetch_tweets_action_transient_add_oembed_elements', array( $this, 'addOEmbedElements' ) );
			
			// For SimplePie cache renewal events 
			add_action( 'fetch_tweets_action_simplepie_renew_cache', array( $this, 'renewSimplePieCaches' ) );
			
		}
				
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
		
		// Load styles of templates - Deprecated as if v1.3.3.2
		// if ( isset( $_GET['fetch_tweets_style'] ) )
			// $GLOBALS['oFetchTweets_Templates']->loadStyle( $_GET['fetch_tweets_style'] );
	
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
	
	/**
	 * Renew the cache of the given request URI
	 * 
	 */
	public function renewTransients( $arrRequestURI ) {

// Debug
// FetchTweets_Debug::getArray( $arrRequestURI, dirname( __FILE__ ) . '/request_uris.txt' );
		
		$strLockTransient = "Lock_" . md5( trim( $arrRequestURI['URI'] ) );
		
		// Check if the transient is locked
		if ( get_transient( $strLockTransient ) !== false ) {
// FetchTweets_Debug::logArray( 'The transient is locked: ' . $strLockTransient );		
			return;	// it means the cache is being modified.
		}
		
		// Set a lock flag transient that indicates the transient is being renewed.
		set_transient(
			$strLockTransient, 
			$arrRequestURI['URI'], // the value can be anything that yields true
			function_exists( 'ini_get' ) ? ini_get('max_execution_time') : 30
		);
		
		// Perform the cache renewal.
		$oFetch = new FetchTweets_Fetch;
		$oFetch->setAPIGETRequestCache( $arrRequestURI['URI'], $arrRequestURI['key'] );

		// Delete the lock transient
		delete_transient( $strLockTransient );
// FetchTweets_Debug::logArray( 'The cache renewed: ' . $strLockTransient );			
	}
	
	/**
	 * Re-saves the cache after adding oEmbed elements.
	 * 
	 * @since			1.3.0
	 */
	public function addOEmbedElements( $strTransientKey ) {

		// Check if the transient is locked
		$strLockTransient = "LockOEm_" . md5( $strTransientKey );	// up to 40 characters, the prefix can be up to 8 characters
		if ( get_transient( $strLockTransient ) !== false ) {
// FetchTweets_Debug::logArray( 'The oEmbed transient is locked: ' . $strTransientKey );		
			return;	// it means the cache is being modified.
			
		}	
		
		// Set a lock flag transient that indicates the transient is being renewed.
		set_transient(
			$strLockTransient, 
			$strTransientKey, // the value can be anything that yields true
			function_exists( 'ini_get' ) ? ini_get('max_execution_time') : 30
		);	
	
		// Perform oEmbed caching
		$oFetch = new FetchTweets_Fetch;
		
		// structure: array( 'mod' => time(), 'data' => $this->oBase64->encode( $vData ) ), 
		$arrTransient = $oFetch->getTransient( $strTransientKey );			
	
		// If the mandatory keys are not set, it's broken.
		if ( ! isset( $arrTransient['mod'], $arrTransient['data'] ) ) {
			delete_transient( $strTransientKey );
			return;
		}
		
		$arrTweets = ( array ) $this->oBase64->decode( $arrTransient['data'] );		
		$oFetch->addEmbeddableMediaElements( $arrTweets );		// the array is passed as reference.
		
		// Re-save the cache.
		$oFetch->setTransient( $strTransientKey, $arrTweets, $arrTransient['mod'] );	// the method handles the encoding.
	
		// Delete the lock transient
		delete_transient( $strLockTransient );

// FetchTweets_Debug::logArray( 'The oEmbed transient is renewed: ' . $strTransientKey );			
	}
}