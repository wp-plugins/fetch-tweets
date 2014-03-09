<?php
/**
 * Handles caching of fetched data.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			1.3.4
 */
abstract class FetchTweets_Fetch_Cache {

	protected $arrExpiredTransientsRequestURIs = array(); // stores the expired transients' request URIs
	
	public function __construct() {
	
		// Set up the connection.
		$this->oOption = & $GLOBALS['oFetchTweets_Option'];		
		
		$this->oTwitterOAuth =  $this->oOption->isAuthKeysManuallySet()
			? new FetchTweets_TwitterOAuth( 
				$this->oOption->getConsumerKey(), 
				$this->oOption->getConsumerSecret(), 
				$this->oOption->getAccessToken(), 
				$this->oOption->getAccessTokenSecret()
			)
			: new FetchTweets_TwitterOAuth( 
				FetchTweets_Commons::ConsumerKey, 
				FetchTweets_Commons::ConsumerSecret, 
				$this->oOption->getAccessTokenAuto(), 
				$this->oOption->getAccessTokenSecretAuto()
			);
						
		$this->oBase64 = new FetchTweets_Base64;	
		
		// Schedule the transient update task.
		add_action( 'shutdown', array( $this, 'updateCacheItems' ) );
		
	}
			
	/**
	 * Performs the API request and sets the cache.
	 * 
	 * @access			public
	 * @remark			The scope is public since the cache renewal event also uses it.
	 */
	public function setAPIGETRequestCache( $strRequestURI, $strArrayKey=null, $strRequestID='' ) {

		// Perform the API request.
		$arrTweets =  $this->oTwitterOAuth->get( $strRequestURI );		
			
		// If the array key is specified, return the contents of the key element. Otherwise, return the retrieved array intact.
		if ( ! is_null( $strArrayKey ) && isset( $arrTweets[ $strArrayKey ] ) )
			$arrTweets = $arrTweets[ $strArrayKey ];
					
		// If empty, return an empty array.
		if ( empty( $arrTweets ) ) return array();
		
		// If the result is not an array, something went wrong.
		if ( ! is_array( $arrTweets ) )
			return ( array ) $arrTweets;
		
		// If an error occurs, do not set the cache.	
		if ( ! $this->oOption->aOptions['cache_settings']['cache_for_errors'] ) {
			if ( isset( $arrTweets['errors'][ 0 ]['message'], $arrTweets['errors'][ 0 ]['code'] ) ) {
				$arrTweets['errors'][ 0 ]['message'] .= "<!-- Request URI: {$strRequestURI} -->";	
				return ( array ) $arrTweets;
			}
		}
		
		// Save the cache
		$strRequestID = empty( $strRequestID ) 
			? FetchTweets_Commons::TransientPrefix . "_" . md5( trim( $strRequestURI ) )
			: $strRequestID;
		$this->setTransient( $strRequestID, $arrTweets );

		return ( array ) $arrTweets;
		
	}
	
	/**
	 * A wrapper method for the set_transient() function.
	 * 
	 * @since			1.2.0
	 * @since			1.3.0			Made it public as the event method uses it.
	 */
	public function setTransient( $strTransientKey, $vData, $intTime=null, $fIgnoreLock=false ) {

		$strLockTransient = FetchTweets_Commons::TransientPrefix . '_' . md5( "Lock_" . $strTransientKey );
		
		// Give some time to the server to store transients in case simultaneous accesses cursors.
		if ( FetchTweets_Cron::isBackground() ) {
			sleep( 1 );
		}
		
		// Check if the transient is locked
		if ( ! $fIgnoreLock && get_transient( $strLockTransient ) !== false ) {
			return;	// it means the cache is being modified.
		}
		
		// Set a lock flag transient that indicates the transient is being renewed.
		if ( ! $fIgnoreLock ) {			
			set_transient(
				$strLockTransient, 
				time(), // the value can be anything that yields true
				10
			);	
		}
	
		// Store the cache
		set_transient(
			$strTransientKey, 
			array( 'mod' => $intTime ? $intTime : time(), 'data' => $this->oBase64->encode( $vData ) )
		);
	
		// Schedules the action to run in the background with WP Cron. If already scheduled, skip.
		// This adds the embedding elements which takes some time to process.
		if ( $intTime || wp_next_scheduled( 'fetch_tweets_action_transient_add_oembed_elements', array( $strTransientKey ) ) ) return;
		wp_schedule_single_event( 
			time(), 
			'fetch_tweets_action_transient_add_oembed_elements', 	// the FetchTweets_Event class will check this action hook and executes it with WP Cron.
			array( $strTransientKey )	// must be enclosed in an array.
		);	
		FetchTweets_Cron::triggerBackgroundProcess( null, true );

		// Delete the lock transient
		// delete_transient( $strLockTransient );

	}
	
	/**
	 * A wrapper method for the get_transient() function.
	 * 
	 * This method does retrieves the transient with the given transient key. In addition, it checks if it is an array; otherwise, it makes it an array.
	 * 
	 * @access			public
	 * @since			1.2.0
	 * @since			1.3.0				Made it public as the event method uses it.
	 */ 
	public function getTransient( $strTransientKey, $fForceArray=true ) {
		
		$vData = get_transient( $strTransientKey );
		
		// if it's false, no transient is stored. Otherwise, some values are in there.
		if ( $vData === false ) return false;
							
		// If it does not have to be an array. Return the raw result.
		if ( ! $fForceArray ) return $vData;
		
		// If it's array, okay.
		if ( is_array( $vData ) ) return $vData;
		
		
		// Maybe it's encoded
		if ( is_string( $vData ) && is_serialized( $vData ) ) 
			return unserialize( $vData );
		
			
		// Maybe it's an object. In that case, convert it to an associative array.
		if ( is_object( $vData ) )
			return get_object_vars( $vData );
			
		// It's an unknown type. So cast array and return it.
		return ( array ) $vData;
			
	}
	
	/*
	 * Callbacks
	 * */
	public function updateCacheItems() {	// for the shutdown hook
		
		if ( empty( $this->arrExpiredTransientsRequestURIs ) ) return;
		
		// Perform multi-dimensional array_unique()
		$this->arrExpiredTransientsRequestURIs = array_map( "unserialize", array_unique( array_map( "serialize", $this->arrExpiredTransientsRequestURIs ) ) );
		
		$intScheduled = 0;
		foreach( $this->arrExpiredTransientsRequestURIs as $arrExpiredCacheRequest ) {
			
			/* the structure of $arrExpiredCacheRequest = array(
				'URI'	=> the API request URI
				'key'	=> the array key that holds the result. e.g. for search results, the 'statuses' key holds the fetched tweets.
			*/
			
			// Check if the URI key holds a valid url.
			if ( ! filter_var( $arrExpiredCacheRequest['URI'], FILTER_VALIDATE_URL ) ) continue;
			
			// Schedules the action to run in the background with WP Cron.
			// If already scheduled, skip.
			if ( wp_next_scheduled( 'fetch_tweets_action_transient_renewal', array( $arrExpiredCacheRequest ) ) ) continue; 
			
			wp_schedule_single_event( 
				time(), 
				'fetch_tweets_action_transient_renewal', 	// the FetchTweets_Event class will check this action hook and executes it with WP Cron.
				array( $arrExpiredCacheRequest )	// must be enclosed in an array.
			);	
			$intScheduled++;
			
		}
		if ( $intScheduled ) {
			FetchTweets_Cron::triggerBackgroundProcess();	
		}
				
	}
	
	
		/**
		 * Retrieves the server set allowed maximum PHP script execution time.
		 * 
		 */
		protected function _getAllowedMaxExecutionTime( $iDefault=30, $iMax=120 ) {
			
			$iSetTime = function_exists( 'ini_get' ) && ini_get( 'max_execution_time' ) 
				? ( int ) ini_get( 'max_execution_time' ) 
				: $iDefault;
			
			return $iSetTime > $iMax
				? $iMax
				: $iSetTime;
			
		}
}