<?php
/**
	A cron task hander class.
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.3.3.11	
*/
class FetchTweets_Cron  {
		
	/**
	 * Handles Fetch Tweets cron tasks.
	 * 
	 * Called from the constructor. 
	 * 
	 * @since			1.3.3.11
	 */
	protected function _handleCronTasks( $aActionHooks ) {
		
		$_aTasks = get_transient( 'doing_fetch_tweets_cron' );
		$_bIsBackground = isset( $_aTasks['called'] );
		$_nCalledTime = isset( $_aTasks['called'] ) ? $_aTasks['called'] : 0;
		$_nLockedTime = isset( $_aTasks['locked'] ) ? $_aTasks['locked'] : 0;
		unset( $_aTasks['called'], $_aTasks['locked'] );	// leave only task elements.
		
		// If called in a generic page load,
		if ( ! $_bIsBackground ) {
			return;
		} 

		// At this point, the process is called in the background.
		if ( empty( $_aTasks ) ) {
			$_aTasks = $this->_getScheduledCronTasksByActionName( $aActionHooks );
		}
		
		// If the task is still empty,
		if ( empty( $_aTasks ) ) {
			delete_transient( 'doing_fetch_tweets_cron' );
			return;
		}
		
		// If it's still locked do nothing. Locked duration: 10 seconds.
		if ( $_nLockedTime + 10 > microtime( true ) ) {
			return;
		}
		
		$_aTasks['locked'] = microtime( true );
		set_transient( 'doing_fetch_tweets_cron', $_aTasks, $this->getAllowedMaxExecutionTime() );	// lock the process.
		$this->_doTasks( $_aTasks );	
		delete_transient( 'doing_fetch_tweets_cron' );	// release it
		
	}

	/**
	 * Performs the plugin-specific scheduled tasks in the background.
	 * 
	 * This should only be called when the 'doing_fetch_tweets_cron' transient is present. 
	 * 
	 * @since 1.3.3.7
	 */
	protected function _doTasks( $aTasks ) {
		
		$_aWPCronTasks = _get_cron_array();
		
		foreach( $aTasks as $iTimeStamp => $aCronHooks ) {
			
			if ( ! is_array( $aCronHooks ) ) continue;		// the 'locked' key flag element should be skipped
			foreach( $aCronHooks as $sActionName => $_aActions ) {
				
				foreach( $_aActions as $sHash => $aArgs ) {
									
					// In case WP Cron has done the task,
					if ( ! isset( $_aWPCronTasks[ $iTimeStamp ][ $sActionName ][ $sHash ] ) ) continue;
									
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
		
	}
	
	/**
	 * Sets plugin specific cron tasks by extracting plugin's cron jobs from the WP cron job array.
	 *  
	 * @since 1.3.3.7
	 */
	protected function _getScheduledCronTasksByActionName( $aActionHooks ) {
		
		$_aTheTasks = array();		
		$_aTasks = _get_cron_array();
		if ( ! $_aTasks ) return $_aTheTasks;	// if the cron tasks array is empty, do nothing. 

		$_iGMTTime = microtime( true );	// the current time stamp in micro seconds.
		$_aScheduledTimeStamps = array_keys( $_aTasks );
		if ( isset( $_aScheduledTimeStamps[ 0 ] ) && $_aScheduledTimeStamps[ 0 ] > $_iGMTTime ) return $_aTheTasks; // the first element has the least number.
				
		foreach ( $_aTasks as $_iTimeStamp => $_aScheduledActionHooks ) {
			if ( $_iTimeStamp > $_iGMTTime ) break;	// see the definition of the wp_cron() function.
			foreach ( ( array ) $_aScheduledActionHooks as $_sScheduledActionHookName => $_aArgs ) {
				if ( in_array( $_sScheduledActionHookName, $aActionHooks ) ) {
					$_aTheTasks[ $_iTimeStamp ][ $_sScheduledActionHookName ] = $_aArgs;
		
				}
			}
		}
		return $_aTheTasks;
				
	}
	
	protected function _setPluginCronTask( $aTasks ) {
		
		if ( empty( $aTasks ) ) return;

		set_transient( 'doing_fetch_tweets_cron', $aTasks, $this->getAllowedMaxExecutionTime() );
		FetchTweets_Cron::triggerBackgroundProcess();	
		
		
	}

	/**
	 * Retrieves the server set allowed maximum PHP script execution time.
	 * 
	 */
	static protected function getAllowedMaxExecutionTime( $iDefault=30, $iMax=120 ) {
		
		$iSetTime = function_exists( 'ini_get' ) && ini_get( 'max_execution_time' ) 
			? ( int ) ini_get( 'max_execution_time' ) 
			: $iDefault;
		
		return $iSetTime > $iMax
			? $iMax
			: $iSetTime;
		
		
	}
			
	/**
	 * Accesses the site in the background.
	 * 
	 * This is used to trigger cron events in the background and sets a static flag so that it ensures it is done only once per page load.
	 * 
	 * @since			1.3.3.11
	 */
	static public function triggerBackgroundProcess() {
		
		// if this is called during the WP cron job, do not trigger a background process as WP Cron will take care of the scheduled tasks.
		if ( isset( $_GET['doing_wp_cron'] ) ) return;	
		if ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] == 'admin-ajax.php' ) return;	// WP Heart-beat API
		
		static $_bIsCalled;
		if ( $_bIsCalled ) return;
		$_bIsCalled = true;		
		
		if ( did_action( 'shutdown' ) ) {
			FetchTweets_Cron::_replyToAccessSite();
		}
		add_action( 'shutdown', 'FetchTweets_Cron::_replyToAccessSite', 999 );	// do not pass self::_replyToAccessSite.

	}	
		/**
		 * A callback for the accessSiteAtShutDown() method.
		 * 
		 * @since			1.3.3.11
		 */
		
		static public function _replyToAccessSite() {
			
			// Check if a tweet cache renewal event is stored
			$_aTasks = get_transient( 'doing_fetch_tweets_cron' );
			$_bHasTaskSet = ( false !== $_aTasks );
			$_aTasks = $_aTasks ? $_aTasks : array();
			if ( ! empty( $_aTasks ) ) {	// if already set				
				$_nCalled = isset( $_aTasks['called'] ) ? $_aTasks['called'] : 0;
				if ( $_nCalled + 10 > microtime( true ) ) {					
					return;	// if it's called within 10 seconds from the last time of calling this method, do nothing to avoid excessive calls.
				}
			}
			
			$_aTasks['called'] = isset( $_aTasks['called'] ) ? $_aTasks['called'] : microtime( true );
			if ( ! $_bHasTaskSet ) {
				set_transient( 'doing_fetch_tweets_cron', $_aTasks, self::getAllowedMaxExecutionTime() );	// set a locked key so it prevents duplicated function calls due to too many calls caused by simultaneous accesses.
			}

			wp_remote_get( // this forces the task to be performed right away in the background.		
				site_url(), 
				array( 'timeout' => 0.01, 'sslverify'   => false, ) 
			);	
			
		}		
					
}