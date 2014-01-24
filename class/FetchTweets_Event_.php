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
		
		$aFetchTweetsCronTasks = get_transient( 'doing_fetch_tweets_cron' );
		if ( $aFetchTweetsCronTasks === false ) {			
			$this->checkWPCronOfFetchTweets( 
				array(
					'fetch_tweets_action_transient_renewal',
					'fetch_tweets_action_transient_add_oembed_elements',
					'fetch_tweets_action_simplepie_renew_cache',
				) 
			);
		} else {
			
			$this->forceWPCronOfFetchTweets( $aFetchTweetsCronTasks );	// performs the plugin-specific scheduled tasks in the background.
			
			// What the 'doing_fetch_tweets_cron' transient is set means that this is loaded in the background. 
			// However, do not exit here because there are cases that an error oocurs during calling the scheduled tasks and the script gets terminated.
			// If that happens, the plugin will not delete the 'doing_fetch_tweets_cron' transient.
			
		}
					
		// For transient (cache) renewal events
		add_action( 'fetch_tweets_action_transient_renewal', array( $this, 'renewTransients' ) );	
		
		// For transient (cache) formatting events - adds oEmbed elements.
		add_action( 'fetch_tweets_action_transient_add_oembed_elements', array( $this, 'addOEmbedElements' ) );
		
		// For SimplePie cache renewal events 
		add_action( 'fetch_tweets_action_simplepie_renew_cache', array( $this, 'renewSimplePieCaches' ) );
			

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
		add_action( 'fetch_tweets_action_setup_transients', array( $this, 'setUpTransients' ) );
			
	}

	/**
	 * Performs the plugin-specific scheduled tasks in the background.
	 * 
	 * This should only be called when the 'doing_fetch_tweets_cron' transient is present. 
	 * 
	 * @since 1.3.3.7
	 */
	protected function forceWPCronOfFetchTweets( $aFetchTweetsCronTasks ) {
		
		if ( isset( $aFetchTweetsCronTasks['locked'] ) ) return;
			return;
				
		$aFetchTweetsCronTasks['locked'] = microtime( true );
		set_transient( 'doing_fetch_tweets_cron', $aFetchTweetsCronTasks, $this->getAllowedMaxExecutionTime() );	// set a locked key so it prevents duplicated function calls due to too many calls caused by simultaneous accesses.
		
		foreach ( $aFetchTweetsCronTasks as $iTimeStamp => $aCronHooks ) {
			
			foreach ( $aCronHooks as $sActionName => $aTasks ) {
				
				foreach( $aTasks as $sHash => $aArgs ) {
									
					$sSchedule = $aArgs['schedule'];
					if ( $sSchedule != false ) {
						$aNewArgs = array( $iTimeStamp, $sSchedule, $sActionName, $aArgs['args'] );
						call_user_func_array( 'wp_reschedule_event', $aNewArgs );
					}
					
					wp_unschedule_event( $iTimeStamp, $sActionName, $aArgs['args'] );
					do_action_ref_array( $sActionName, $aArgs['args'] );
					
				}
				 
			}
			
		}
		
		delete_transient( 'doing_fetch_tweets_cron' );
		
	}
	
	/**
	 * Forces WP Cron tasks to be perfomed as some servers disable it.
	 * 
	 * @since 1.3.3.7
	 */
	protected function checkWPCronOfFetchTweets( $aActionHooks ) {
				
		$aTasks = _get_cron_array();
		if ( ! $aTasks ) return;	// if the cron tasks array is empty, do nothing. 

		$iGMTTime = microtime( true );	// the current time stamp in micro seconds.
		$aScheduledTimeStamps = array_keys( $aTasks );
		if ( isset( $aScheduledTimeStamps[0] ) && $aScheduledTimeStamps[0] > $iGMTTime ) return; // the first element has the least number.
		
		$aFetchTweetsCronTasks = array();
		foreach ( $aTasks as $iTimeStamp => $aScheduledActionHooks ) {
			if ( $iTimeStamp > $iGMTTime ) break;	// see the definition of the wp_cron() function.
			foreach ( ( array ) $aScheduledActionHooks as $sScheduledActionHookName => $aArgs ) 
				if ( in_array( $sScheduledActionHookName, $aActionHooks ) ) 
					$aFetchTweetsCronTasks[ $iTimeStamp ][ $sScheduledActionHookName ] = $aArgs;
		}
FetchTweets_Debug::logArray( 'Triggered CRON' ); 		
FetchTweets_Debug::logArray( 'max allwoed execution time: ' . $this->getAllowedMaxExecutionTime() ); 		
// FetchTweets_Debug::logArray( $aFetchTweetsCronTasks ); 		

		set_transient( 'doing_fetch_tweets_cron', $aFetchTweetsCronTasks, $this->getAllowedMaxExecutionTime() );
		wp_remote_get( site_url(), array( 'timeout' => 0.01, 'sslverify'   => false, ) );	// this forces the task to be performed right away in the background.
		
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
			
			$strLockTransient = FetchTweets_Commons::TransientPrefix . '_' . md5( "Lock_" . trim( $arrRequestURI['URI'] ) );
			
			// Check if the transient is locked
			if ( get_transient( $strLockTransient ) !== false ) {
				return;	// it means the cache is being modified.
			}
			
			// Set a lock flag transient that indicates the transient is being renewed.
			set_transient(
				$strLockTransient, 
				$arrRequestURI['URI'], // the value can be anything that yields true
				$this->getAllowedMaxExecutionTime()	// function_exists( 'ini_get' ) && ini_get( 'max_execution_time' ) ? ( ini_get( 'max_execution_time' ) > 120 ? 120 : ini_get( 'max_execution_time' ) ) : 30
			);
			
			// Perform the cache renewal.
			$oFetch = new FetchTweets_Fetch;
			$oFetch->setAPIGETRequestCache( $arrRequestURI['URI'], $arrRequestURI['key'] );

			// Delete the lock transient
			delete_transient( $strLockTransient );

		}
		
		/**
		 * Re-saves the cache after adding oEmbed elements.
		 * 
		 * @since			1.3.0
		 */
		public function addOEmbedElements( $strTransientKey ) {

			// Check if the transient is locked
			$strLockTransient = FetchTweets_Commons::TransientPrefix . '_' . md5( "LockOEm_" . trim( $strTransientKey ) );	// up to 40 characters, the prefix can be up to 8 characters
			if ( get_transient( $strLockTransient ) !== false ) {
				return;	// it means the cache is being modified.
			}	
			
			// Set a lock flag transient that indicates the transient is being renewed.
			set_transient(
				$strLockTransient, 
				$strTransientKey, // the value can be anything that yields true
				$this->getAllowedMaxExecutionTime()	// function_exists( 'ini_get' ) && ini_get( 'max_execution_time' ) ? ( ini_get( 'max_execution_time' ) > 120 ? 120 : ini_get( 'max_execution_time' ) ) : 30
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

		}
		
			private function getAllowedMaxExecutionTime( $iDefault=30, $iMax=120 ) {
				
				$iSetTime = function_exists( 'ini_get' ) && ini_get( 'max_execution_time' ) 
					? ( int ) ini_get( 'max_execution_time' ) 
					: $iDefault;
				
				return $iSetTime > $iMax
					? $iMax
					: $iSetTime;
				
				
			}
}