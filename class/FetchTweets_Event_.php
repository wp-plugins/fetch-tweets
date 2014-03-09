<?php
/**
	An event handler class.
	
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
		
		// For transient (cache) renewal events
		add_action( 'fetch_tweets_action_transient_renewal', array( $this, '_replyToRenewTransients' ) );	
		
		// For transient (cache) formatting events - adds oEmbed elements.
		add_action( 'fetch_tweets_action_transient_add_oembed_elements', array( $this, '_replyToAddOEmbedElements' ) );
		
		// For SimplePie cache renewal events 
		add_action( 'fetch_tweets_action_simplepie_renew_cache', array( $this, '_replyToRenewSimplePieCaches' ) );		

		// This must be called after the above action hooks are added.
		new FetchTweets_Cron(
			array(
				'fetch_tweets_action_transient_renewal',
				'fetch_tweets_action_transient_add_oembed_elements',
				'fetch_tweets_action_simplepie_renew_cache',
			) 
		);	
				
		// Redirects
		if ( isset( $_GET['fetch_tweets_link'] ) && $_GET['fetch_tweets_link'] ) {			
			$oRedirect = new FetchTweets_Redirects;
			$oRedirect->go( $_GET['fetch_tweets_link'] );	// will exit there.
		}
			
		// Draw cached image.
		if ( isset( $_GET['fetch_tweets_image'] ) && $_GET['fetch_tweets_image'] && is_user_logged_in() ) {
			
			$oImageLoader = new FetchTweets_ImageHandler( 'FTWS' );
			$oImageLoader->draw( $_GET['fetch_tweets_image'] );
			exit;
			
		}			
		
		// For the activation hook
		add_action( 'fetch_tweets_action_setup_transients', array( $this, '_replyToSetUpTransients' ) );
			
	}
		
	
	public function _replyToSetUpTransients() {
		
		$oUA = new FetchTweets_UserAds();
		$oUA->setupTransients();		
		
	}

	public function _replyToRenewSimplePieCaches( $vURLs ) {
		
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
	public function _replyToRenewTransients( $aRequest ) {
		
		// Perform the cache renewal.
		$oFetch = new FetchTweets_Fetch;
		$oFetch->setAPIGETRequestCache( $aRequest['URI'], $aRequest['key'] );
		
	}
		
	/**
	 * Re-saves the cache after adding oEmbed elements.
	 * 
	 * @since			1.3.0
	 */
	public function _replyToAddOEmbedElements( $strTransientKey ) {

		// Check if the transient is locked
		$strLockTransient = FetchTweets_Commons::TransientPrefix . '_' . md5( "LockOEm_" . trim( $strTransientKey ) );	// up to 40 characters, the prefix can be up to 8 characters
		if ( get_transient( $strLockTransient ) !== false ) {
			return;	// it means the cache is being modified.
		}	
		
		// Set a lock flag transient that indicates the transient is being renewed.
		set_transient(
			$strLockTransient, 
			$strTransientKey, // the value can be anything that yields true
			FetchTweets_Utilities::getAllowedMaxExecutionTime()	
		);	
	
		// Perform oEmbed caching - no API request will be performed
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
		$oFetch->setTransient( $strTransientKey, $arrTweets, $arrTransient['mod'], true );	// the method handles the encoding.
	
		// Delete the lock transient
		delete_transient( $strLockTransient );

	}
				
					
}