<?php
abstract class FetchTweets_AdminPage_Setting extends FetchTweets_AdminPage_Template {
			
	/*
	 * Authentication In-Page Tabs
	 */
	public function load_fetch_tweets_settings() { // load_ + page slug
		session_start();	// Start session to store access token.
	}
	public function load_fetch_tweets_settings_twitter_connect() {
				
		// Check if the session array has the access token; otherwise, clear the session.
		if ( 
			empty( $_SESSION['access_token'] ) 
			|| empty( $_SESSION['access_token']['oauth_token'] ) 
			|| empty( $_SESSION['access_token']['oauth_token_secret'] ) 
		) 		
			session_destroy();
						
	}
	public function load_fetch_tweets_settings_twitter_clear_session() {

		/* Clear sessions */
		session_destroy();
		 
		/* Redirect to page with the connect to Twitter option. */
		wp_redirect( admin_url( $GLOBALS['pagenow'] . "?page=fetch_tweets_settings&tab=twitter_connect" ) );
	
	}
	
	/**
	 * Redirects to the twitter to get authenticated.
	 * 
	 * @since			1.3.0
	 * @remark			This is redirected from the "Connect to Twitter" button.
	 */
	public function load_fetch_tweets_settings_twitter_redirect() {	// load_ + page slug + tab
	
		/* Build TwitterOAuth object with client credentials. */
		$_oConnect = new FetchTweets_TwitterOAuth( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret );
		 
		/* Get temporary credentials - Requesting authentication tokens, the parameter is the URL we will be redirected to. */
		$_aRequestToken = $_oConnect->getRequestToken( 
			add_query_arg( 
				array(
					'post_type' => 'fetch_tweets',
					'page' => 'fetch_tweets_settings',
					'tab' => 'twitter_callback'
				),
				admin_url( $GLOBALS['pagenow'] ) 
			)
		);

		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $_sTemporaryToken = $_aRequestToken['oauth_token'];
		$_SESSION['oauth_token_secret'] = $_aRequestToken['oauth_token_secret'];
		
		/* If last connection failed don't display authorization link. */
		switch ( $_oConnect->http_code ) {
			case 200:	/* Build authorize URL and redirect user to Twitter. */
				wp_redirect( $_oConnect->getAuthorizeURL( $_sTemporaryToken ) );	// goes to twitter.com
			break;
			default:	/* Show notification if something went wrong. */
				die( __( 'Could not connect to Twitter. Refresh the page or try again later.', 'fetch-tweets' ) );
		}
		exit;
	
	}	
	
	/**
	 * Receives the callback from Twitter authentication and saves the access token.
	 * 
	 * @remark			This method is triggered when the user get redirected back to the admin page
	 */
	public function load_fetch_tweets_settings_twitter_callback() {
				
		/* If the oauth_token is old redirect to the authentication page. */
		if (
			isset( $_REQUEST['oauth_token'] ) 
			&& $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']
		) {
			$_SESSION['oauth_status'] = 'oldtoken';
			wp_redirect( add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication' ), admin_url( $GLOBALS['pagenow'] ) ) );
			// wp_redirect( admin_url( $GLOBALS['pagenow'] . "?page=fetch_tweets_settings&tab=twitter_clear_session" ) );
		}

		$oOption = & $GLOBALS['oFetchTweets_Option'];

		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$oConnect = new FetchTweets_TwitterOAuth( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret'] );

		/* Request access tokens from twitter */
		$arrAccessTokens = $oConnect->getAccessToken( $_REQUEST['oauth_verifier'] );

		/* Save the access tokens. Normally these would be saved in a database for future use. */
		$_SESSION['access_token'] = $arrAccessTokens;
		$oOption->saveAccessToken( $arrAccessTokens['oauth_token'], $arrAccessTokens['oauth_token_secret'] );

		/* Remove no longer needed request tokens */
		unset( $_SESSION['oauth_token'] );
		unset( $_SESSION['oauth_token_secret'] );

		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if ( 200 == $oConnect->http_code ) {
			
			/* The user has been verified and the access tokens can be saved for future use */
			$_SESSION['status'] = 'verified';		  
			wp_redirect( add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_connect' ), admin_url( $GLOBALS['pagenow'] ) ) );
		  
		} else {
			
		  /* Save HTTP status for error dialogue on authentication page.*/
		  // Let the user set authentication keys manually		  
		  wp_redirect( add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication' ), admin_url( $GLOBALS['pagenow'] ) ) );
	  
		}
	
	}	
	
	// The connect page
	public function do_form_fetch_tweets_settings_twitter_connect() {	// do_form_ + page slug + _ + tab slug
		
		// Store the status array into the property. This property will be referred when the Connect to Twitter button is rendered.
		$this->arrStatus = $this->_getVerificationStatus();
		$this->_renderAuthenticationStatus( $this->arrStatus );
				
	}

	
	public function do_form_fetch_tweets_settings_authentication() {	// do_form_ + page slug + _ + tab slug
		
		$this->arrStatus = $this->_getVerificationStatus();
		$this->_renderAuthenticationStatus( $this->arrStatus );
		
	}
		
	/**
	 * Filters the output of the Connect To Twitter button.
	 * 
	 * If it's not authenticated yet, the label becomes "Connect"; otherwise, "Disconnect"
	 */
	public function field_FetchTweets_AdminPage_twitter_connect_connect_to_twitter( $strField ) {		// field_{instantiated class name}_{section id}_{field id}
		
		return ! ( isset( $this->arrStatus['id'] ) && $this->arrStatus['id'] )
			? $strField		// the connect button
			: '<span style="display: inline-block; min-width:120px;">'
					. '<input id="twitter_connect_connect_to_twitter__0" class="button button-primary" type="submit" name="disconnect_from_twitter" value="' . __( 'Disconnect', 'fetch-tweets' ) . '">&nbsp;&nbsp;'
				.'</span>'; // the disconnect button
				
	}
	
	public function validation_fetch_tweets_settings( $arrInput, $arrOriginal ) {
		
		// If the disconnect button is pressed, delete the authentication keys.
		if ( isset( $_POST['disconnect_from_twitter'] ) ) {
			
			$arrInput = is_array( $arrInput ) ? $arrInput : array();	// in WP v3.4.2, when the Disconnect button is pressed an $arrInput was passed as an empty string. Something went wrong.
			delete_transient( FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $this->oOption->getConsumerKey(), $this->oOption->getConsumerSecret(), $this->oOption->getAccessToken(), $this->oOption->getAccessTokenSecret() ) ) ) );
			delete_transient( FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $this->oOption->getAccessTokenAuto(), $this->oOption->getAccessTokenSecretAuto() ) ) ) );
			unset( $arrInput['authentication_keys'] );
			unset( $arrInput['twitter_connect'] );
			
		}

		return $arrInput;
		
	}
	
	/*
	 * Settings Page
	 */
	public function do_before_fetch_tweets_settings() {	// do_before_ + page slug
		$this->setPageTitleVisibility( false );
	}
			
	public function validation_fetch_tweets_settings_general( $arrInput, $arrOriginal ) {
		
		$arrInput['default_values']['count'] = $this->oUtil->fixNumber(
			$arrInput['default_values']['count'],
			$GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['count'],
			1
		);
		
		return $arrInput;
		
	}
	public function validation_fetch_tweets_settings_reset( $arrInput, $arrOriginal ) {
				
		// Variables
		$fChanged = false;
				
		// Make it one dimensional.
		$arrSubmit = array();
		foreach ( $arrInput as $strSection => $arrFields ) 
			$arrSubmit = $arrSubmit + $arrFields;				
			
		// If the Perform button is not set, return.
		if ( ! isset( $arrSubmit['submit_reset_settings'] ) ) {
			$this->setSettingNotice( __( 'Nothing changed.', 'fetch-tweets' ) );	
			return $arrOriginal;
		}

		if ( isset( $arrSubmit['clear_caches'] ) && $arrSubmit['clear_caches'] ) {
			FetchTweets_Transient::clearTransients();
			$fChanged = true;
			$this->setSettingNotice( __( 'The caches have been cleared.', 'fetch-tweets' ) );
		}
		
		// $this->oDebug->getArray( $arrSubmit, dirname( __FILE__ ) . '/submit.txt' );
		// $this->oDebug->getArray( $GLOBALS['oFetchTweets_Option']->aOptions, dirname( __FILE__ ) . '/options.txt' );
		
		if ( isset( $arrSubmit['option_sections'] ) ) {
			if ( isset( $arrSubmit['option_sections']['all'] ) && $arrSubmit['option_sections']['all'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_All' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['genaral'] ) && $arrSubmit['option_sections']['general'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_General' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['template'] ) && $arrSubmit['option_sections']['template'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_Template' ), 999 );
			}		
		}
		
		if ( ! $fChanged ) {
			$this->setSettingNotice( __( 'Nothing changed.', 'fetch-tweets' ) );	
		}
		return $arrOriginal;	// no need to update the options.
		
	}
	public function deleteOptions_All() {
		delete_option( FetchTweets_Commons::AdminOptionKey );
	}
	public function deleteOptions_General() {
		// Currently not working: Somehow the options get recovered.
		unset( $GLOBALS['oFetchTweets_Option']->aOptions );
		$GLOBALS['oFetchTweets_Option']->saveOptions();		
	}
	public function deleteOptions_Template() {		
		// Currently not working: Somehow the options get recovered.

		unset( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'] );
		unset( $GLOBALS['oFetchTweets_Option']->aOptions['arrDefaultTemplate'] );		
		$GLOBALS['oFetchTweets_Option']->saveOptions();

	}
					
}