<?php
/**
 * Provides methods to fetch tweets by list.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_Fetch_ByList extends FetchTweets_Fetch_BySearch {
	
	/**
	 * Returns an array holding the list IDs and names from the given owner screen name.
	 * 
	 * @remark			This is used for the form field select option.
	 * @since			1.2.0
	 */
	public function getListNamesFromScreenName( $strScreenName ) {
		
		$arrListDetails = $this->getListsByScreenName( $strScreenName );
		$arrListIDs = array();
		foreach( $arrListDetails as $arrListDetail ) 
			$arrListIDs[ $arrListDetail['id'] ] = $arrListDetail['name'];
		return $arrListIDs;
		
	}
	
	/**
	 * Fetches lists and their details owned by the specified user.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/lists/list
	 * @since			1.2.0
	 */ 
	protected function getListsByScreenName( $strScreenName, $intCacheDuration=600 ) {
		
		// Compose the request URI.			
		// https://api.twitter.com/1.1/lists/list.json?screen_name=twitterapi 
		$strRequestURI = "https://api.twitter.com/1.1/lists/list.json?"
			. "screen_name={$strScreenName}";
			
		return $this->doAPIRequest_Get( $strRequestURI, null, $intCacheDuration );
		
	}
	
	/**
	 * Fetches tweets by list ID.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/lists/statuses
	 * @since			1.2.0
	 */ 
	protected function getTweetsByListID( $strListID, $intCount=20, $fIncludeRetweets=false, $intCacheDuration=600 ) {
		
		// Compose the request URI.
		// $intCount = ( ( int ) $intCount ) > 200 ? 200 : $intCount;
		$intCount = 200;	// as of 1.3.4 the maximum number of tweets are fetched so that the data can be reused for different count requests.
			
		// https://api.twitter.com/1.1/lists/statuses.json?slug=teams&owner_screen_name=MLS&count=1 
		$strRequestURI = "https://api.twitter.com/1.1/lists/statuses.json?"
			. "list_id={$strListID}"
			// . "&slug={$strListSlug}"
			// . "&owner_screen_name={$strOwnerScreenName}"
			// . "&owner_id={$strOwnerID}"
			// . "&since_id={$strSinceID}"
			// . "&max_id={$strMaxID}"
			. "&count={$intCount}"
			. "&include_rts=" . ( $fIncludeRetweets ? 1 : 0 );
			// . "&exclude_replies=" . ( $fExcludeReplies ? 1 : 0 );
		
		return $this->doAPIRequest_Get( $strRequestURI, null, $intCacheDuration );
	
	}
	
	
}