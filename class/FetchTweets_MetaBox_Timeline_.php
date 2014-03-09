<?php
/**
 * Creates a meta box with forms fields for Twitter home timeline.
 * 
 * @since			2
 * @filter			fetch_tweets_filter_authenticated_accounts			Receives an array of authenticated twitter accounts consisting of the values of screen names.
 */
class FetchTweets_MetaBox_Timeline_ extends FetchTweets_AdminPageFramework_MetaBox {
		
	/**
	 * Adds form fields for the options to fetch tweets by screen name to the meta box.
	 * 
	 * @since			2.0.0
	 */ 
	public function setUp() {
		
		$this->addSettingFields(
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'home_timeline',
				'hidden'		=>	true,
			),						
			array(
				'field_id'		=> 'account',
				'title'			=> __( 'Account', 'fetch-tweets' ),
				'type'			=> 'select',
				'label'			=>	apply_filters( 'fetch_tweets_filter_authenticated_accounts', $this->_getAuthenticatedAccounts() ),
				
			),	
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 200 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	200,
				),
			),						
			array(
				'field_id'		=> 'exclude_replies',
				'title'			=> 'Exclude Replies',
				'type'			=> 'checkbox',
				'label'			=> __( 'Replies will be excluded.', 'fetch-tweets' ),
				'description'	=> __( 'This prevents replies from appearing in the returned timeline.', 'fetch-tweets' ),
			),			
			// array(
				// 'field_id'		=> 'include_retweets',
				// 'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				// 'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				// 'type'			=> 'checkbox',
			// ),						
			array()
		);	
	
	}
		
		/**
		 * Returns the list of accounts.
		 */
		protected function _getAuthenticatedAccounts() {
			
			$_oOption = & $GLOBALS['oFetchTweets_Option'];
			$_aAccountList = array();
			$_aAccountList[0] = isset( $_oOption->aOptions['twitter_connect']['screen_name'] ) 
				? $_oOption->aOptions['twitter_connect']['screen_name']
				: $this->_getScreenName();
			return $_aAccountList;
			
		}
		/**
		 * Performs API request and retrieves the screen name
		 */
		protected function _getScreenName() {
			
			$_oOption = & $GLOBALS['oFetchTweets_Option'];
			
			// If the required option elements are not set, return an empty element.
			if ( ! isset( $_oOption->aOptions['twitter_connect']['access_token'], $_oOption->aOptions['twitter_connect']['access_secret'] ) ) {
				return '';
			}
			$_fIsManuallySet = $_oOption->isAuthKeysManuallySet();
			
			$_sAccessToken = $_oOption->aOptions['twitter_connect']['access_token'];
			$_sAccessSecret = $_oOption->aOptions['twitter_connect']['access_secret'];			
			$_sConsumerKey = $_fIsManuallySet ? $_oOption->getConsumerKey() : FetchTweets_Commons::ConsumerKey;
			$_sConsumerSecret = $_fIsManuallySet ? $_oOption->getConsumerSecret() : FetchTweets_Commons::ConsumerSecret;
			$oTwitterOAuth_Verification = new FetchTweets_TwitterAPI_Verification( $_sConsumerKey, $_sConsumerSecret, $_sAccessToken, $_sAccessSecret );
			$aStatus = $oTwitterOAuth_Verification->getStatus();
			return isset( $aStatus['screen_name'] )
				? $aStatus['screen_name']
				: '';
					
		}
	
	public function validation_FetchTweets_MetaBox_ScreenName( $arrInput ) {	// validation_ + extended class name
			
		$arrInput['item_count'] = $this->oUtil->fixNumber( 
			$arrInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			200
		);
				
		return $arrInput;
		
	}
	
}
