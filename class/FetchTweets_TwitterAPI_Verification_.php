<?php
/**
 * Handles Twitter API verification
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_TwitterAPI_Verification_ {

	function __construct( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret ) {
		
		$this->sConsumerKey = $sConsumerKey;
		$this->sConsumerSecret = $sConsumerSecret;
		$this->sAccessToken = $sAccessToken;
		$this->sAccessSecret = $sAccessSecret;
		
	}
	
	public function getStatus() {
		
		// Return the cached response if available.
		$_sCacheID = FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $this->sConsumerKey, $this->sConsumerSecret, $this->sAccessToken, $this->sAccessSecret ) ) );
		$_vData = get_transient( $_sCacheID );
		if ( $_vData !== false ) return $_vData;
		
		// Perform the requests.
		$_oTwitterOAuth =  new FetchTweets_TwitterOAuth( $this->sConsumerKey, $this->sConsumerSecret, $this->sAccessToken, $this->sAccessSecret );
		$_aUser = $_oTwitterOAuth->get( 'account/verify_credentials' );
		
		// If the user id could not be retrieved, it means it failed.
		if ( ! isset( $_aUser['id'] ) || ! $_aUser['id'] ) return array();
			
		// Otherwise, it is okay. Retrieve the current status.
		$_aStatus = $_oTwitterOAuth->get( 'https://api.twitter.com/1.1/application/rate_limit_status.json?resources=search,statuses,lists' );
		
		// Set the cache.
		$_aData = is_array( $_aStatus ) ? $_aUser + $_aStatus : $_aUser;
		set_transient( $_sCacheID, $_aData, 60 );	// stores the cache only for 60 seconds. It is allowed 15 requests in 15 minutes.
		
		// Save the screen name to the option
		$_oOption = & $GLOBALS['oFetchTweets_Option'];
		$_oOption->saveScreenName( $_aData['screen_name'] );
		
		return $_aData;	
		
	}
	
}