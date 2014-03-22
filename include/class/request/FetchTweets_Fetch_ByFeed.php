<?php
/**
 * Provides methods to fetch tweets by JSON feed.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2.0.1
 */
abstract class FetchTweets_Fetch_ByFeed extends FetchTweets_Fetch_ByHomeTimeline {
	
	/**
	 * Retrieves tweets of the given feed.
	 * 
	 * @since			2.0.1
	 */
	protected function _getTweetsByJSONFeed( $sFeedURL, $iCacheDuration=600 ) {
		
		// Sanitize and validate the url.
// TODO: validate the url and if it fails, return an empty array.		
		
		return $this->doAPIRequest_Get( $sFeedURL, '_not_api_request', $iCacheDuration );
		
		
	}
	
	
}