<?php
abstract class FetchTweets_AdminPage_AddRuleByList extends FetchTweets_AdminPage_Setting {
			
	/*
	 * Add Rule by List Page
	 */
	public function do_fetch_tweets_add_rule_by_list() {	// do_ + page slug

		// $oFetch = new FetchTweets_Fetch;
		// $arrTweets = $oFetch->getListsByScreenName( 'Otto42' );
// Debug
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";			
		// $arrTweets = $oFetch->getTweetsAsArray( array( 'list_id' => '33331244' ) );
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";					
		
	}
	public function validation_fetch_tweets_add_rule_by_list( $aInput, $aOldInput ) {	// validation_{page slug}
				
		// Check if the input has been properly sent.
		if ( ! isset( $aInput['add_rule_by_list']['list_owner_screen_name'] ) ) {			
			$this->setSettingNotice( __( 'Something went wrong. Your input could not be received. Try again and if this happens again, contact the developer.', 'fetch-tweets' ) );
			return $aOldInput;
		}
		
		// Variables
		$fVerified = true;	// flag
		$arrErrors = array();	// error array
		$strOwnerScreenName = $aInput['add_rule_by_list']['list_owner_screen_name'];
		
		// The list owner screen name must be provided.
		if ( empty( $strOwnerScreenName ) ) {
			$arrErrors['add_rule_by_list']['list_owner_screen_name'] = __( 'The screen name of the list owner must be specified: ' ) . $strOwnerScreenName;
			$this->setFieldErrors( $arrErrors );		
			$this->setSettingNotice( __( 'There was an error in your input.', 'fetch-tweets' ) );
			return $aOldInput;						
		}
		
		// Fetch the lists by the screen name.
		$oFetch = new FetchTweets_Fetch;
		$arrLists = $oFetch->getListNamesFromScreenName( $strOwnerScreenName );
		if ( empty( $arrLists ) ) {
			$this->setSettingNotice( __( 'No list found.', 'fetch-tweets' ) );
			return $aOldInput;			
		}

		// Set the transient of the fetched IDs. This will be used right next page load.
		$strListCacheID = uniqid();
		set_transient( $strListCacheID, $arrLists, 60 );		
		die( wp_redirect( admin_url( "post-new.php?post_type=fetch_tweets&tweet_type=list&list_cache={$strListCacheID}&screen_name={$strOwnerScreenName}" ) ) );		
		
	}
		
					
}