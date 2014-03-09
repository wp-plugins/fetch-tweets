<?php
/**
 * Provides methods to fetch tweets by home timeline.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_Fetch_ByHomeTimeline extends FetchTweets_Fetch_ByScreenName {
	
	/**
	 * Retrieves tweets of the given account.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/statuses/home_timeline
	 * @param			integer			$iAccountID			The account ID. If 0, it means the main account.
	 * @param			boolean			$fExcludeReplies	Indicates whether replies should be excluded or not.
	 */
	protected function _getTweetsByHomeTimeline( $iAccountID, $fExcludeReplies, $iCacheDuration ) {
		
		$_sConsumerKey = $iAccountID == 0 ? $this->sConsumerKey : $this->_getAccessTokenByAccountID( $iAccountID );
		$_sConsumerSecret = $iAccountID == 0 ? $this->sConsumerSecret : $this->_getAccessTokenByAccountID( $iAccountID );
		$_sAccessToken = $iAccountID == 0 ? $this->sAccessToken : $this->_getAccessTokenByAccountID( $iAccountID );
		$_sAccessSecret = $iAccountID == 0 ? $this->sAccessSecret : $this->_getAccessSecretByAccountID( $iAccountID );
		$strRequestURI = "https://api.twitter.com/1.1/statuses/home_timeline.json"
			. "?"
			. "&count=200"	// 200 is the max
			. "&include_entities=1"
			. "&exclude_replies=" . ( $fExcludeReplies ? 1 : 0 )
			. "&consumer_key=" . $_sConsumerKey			//	this is not an API parameter but for the plugin transient ID
			. "&consumer_secret=" . $_sConsumerSecret		//	this is not an API parameter but for the plugin transient ID
			. "&access_token=" . $_sAccessToken			//	this is not an API parameter but for the plugin transient ID
			. "&access_secret=" . $_sAccessSecret		// 	this is not an API parameter but for the plugin transient ID
		;
		
		return $this->doAPIRequest_Get( $strRequestURI, null, $iCacheDuration );
					
		
	}
		/**
		 * 
		 * @since			2
		 */
		protected function _getConsumerKeyByAccountID( $iAccountID ) {
//TODO			
return "---";			
		}
		/**
		 * 
		 * @since			2
		 */		
		protected function _getConsumerSecretByAccountID( $iAccountID ) {
//TODO			
return "---";			
		}	
		/**
		 * 
		 * @since			2
		 */		
		protected function _getAccessTokenByAccountID( $iAccountID ) {
//TODO			
return "---";
		}
		/**
		 * 
		 * @since			2
		 */		
		protected function _getAccessSecretByAccountID( $iAccountID ) {
//TODO			
return "---";			
		}
	
}