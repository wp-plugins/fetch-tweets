<?php
abstract class FetchTweets_AdminPage_Authentication extends FetchTweets_AdminPage_Menu {
		
	/**
	 * Retrieves the verification status with the saved access keys.
	 * 
	 * This method first checks with the manually set authentication keys and if it fails, it checks with the automatically set authentication keys.
	 * 
	 * @since			1.3.0
	 * @return			array			The array which contains the verification status.
	 */
	protected function getVerificationStatus() {

		// If the access token and access secret keys have been manually set,
		$arrStatus = $this->oOption->isAuthKeysManuallySet()
			? $this->_verifyCrediential( $this->oOption->getConsumerKey(), $this->oOption->getConsumerSecret(), $this->oOption->getAccessToken(), $this->oOption->getAccessTokenSecret() )
			: array();
			
		if ( ! empty( $arrStatus ) ) return $arrStatus;
			
		// If the access token and secret keys have been automatically set,
		if ( $this->oOption->isAuthKeysAutomaticallySet() )
			$arrStatus = $this->_verifyCrediential( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $this->oOption->getAccessTokenAuto(), $this->oOption->getAccessTokenSecretAuto() );
			
		if ( $arrStatus ) return $arrStatus;
	
	}
	
		/**
		 * Checks the API credential is valid or not.
		 * 	 
		 * @since			1.3.0
		 * @return			array			the retrieved data.
		 * @remark			The returned data is a merged result of 'account/verify_credientials' and 'rate_limit_status'.
		 */
		protected function _verifyCrediential( $strConsumerKey, $strConsumerSecret, $strAccessToken, $strAccessSecret ) {
			
			// Return the cached response if available.
			$strCachID = FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $strConsumerKey, $strConsumerSecret, $strAccessToken, $strAccessSecret ) ) );
			$vData = get_transient( $strCachID );
			if ( $vData !== false ) return $vData;
			
			// Perform the requests.
			$oTwitterOAuth =  new FetchTweets_TwitterOAuth( $strConsumerKey, $strConsumerSecret, $strAccessToken, $strAccessSecret );
			$arrUser = $oTwitterOAuth->get( 'account/verify_credentials' );
			
			// If the user id could not be retrieved, it means it failed.
			if ( ! isset( $arrUser['id'] ) || ! $arrUser['id'] ) return array();
				
			// Otherwise, it is okay. Retrieve the current status.
			$arrStatus = $oTwitterOAuth->get( 'https://api.twitter.com/1.1/application/rate_limit_status.json?resources=search,statuses,lists' );
			
			// Set the cache.
			$arrData = is_array( $arrStatus ) ? $arrUser + $arrStatus : $arrUser;
			set_transient( $strCachID, $arrData, 60 );	// stores the cache only for 60 seconds. It is allowed 15 requests in 15 minutes.
			return $arrData;

		}

		
	/**
	 * Renders the authentication status table.
	 * 
	 * @since			1.3.0
	 * @param			array			$arrStatus			This arrays should be the merged array of the results of 'account/verify_credientials' and 'rate_limit_status' requests.
	 * 
	 */
	protected function _renderAuthenticationStatus( $arrStatus ) {
		
		$fIsValid = isset( $arrStatus['id'] ) && $arrStatus['id'] ? true : false;
		$strScreenName = isset( $arrStatus['screen_name'] ) ? $arrStatus['screen_name'] : "";
		
		$strUserTimelineLimit = isset( $arrStatus['resources']['statuses']['/statuses/user_timeline'] )
		? $arrStatus['resources']['statuses']['/statuses/user_timeline']['remaining'] . ' / ' . $arrStatus['resources']['statuses']['/statuses/user_timeline']['limit'] 
			. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $arrStatus['resources']['statuses']['/statuses/user_timeline']['reset'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . " )"
		: "";		
		$strSearchLimit = isset( $arrStatus['resources']['search']['/search/tweets'] ) 
			? $arrStatus['resources']['search']['/search/tweets']['remaining'] . ' / ' . $arrStatus['resources']['search']['/search/tweets']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $arrStatus['resources']['search']['/search/tweets']['reset'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . " )"
			: "";

		$strListLimit = isset( $arrStatus['resources']['lists']['/lists/statuses'] )
			? $arrStatus['resources']['lists']['/lists/statuses']['remaining'] . ' / ' . $arrStatus['resources']['lists']['/lists/statuses']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $arrStatus['resources']['lists']['/lists/statuses']['reset'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . " )"
			: "";
		
	?>		
		<h3><?php _e( 'Status', 'fetch-tweets' ); ?></h3>
		<table class="form-table auth-status">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Status', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $fIsValid ? '<span class="authenticated">' . __( 'Authenticated', 'fetch-tweets' ) . '</span>': '<span class="unauthenticated">' . __( 'Not authenticated', 'fetch-tweets' ) . '</span>'; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Screen Name', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strScreenName; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Timeline Request Limit', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strUserTimelineLimit; ?>
					</td>
				</tr>	
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Search Request Limit', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strSearchLimit; ?>
					</td>
				</tr>	
				<tr valign="top">
					<th scope="row">
						<?php _e( 'List Request Limit', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strListLimit; ?>
					</td>
				</tr>				
			</tbody>
		</table>
					
		<?php
// $this->oDebug->dumpArray( $arrStatus );		
	}
	/**
	 * Renders the authentication link buttons.
	 * @since			1.3.0
	 */
	protected function renderAuthenticationButtons( $arrStatus ) {
		
		$fIsValid = isset( $arrStatus['id'] ) && $arrStatus['id'] ? true : false;
	?>		
		<h3><?php _e( 'Authenticate', 'fetch-tweets' ); ?></h3>
		<table class="form-table auth-status">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Connect to Twitter', 'fetch-tweets' ); ?>
					</th>
					<td>
						<a href="http://www.google.com">
							<input type="submit" class="button button-primary" value="<?php echo $fIsValid ? __( 'Disconnect', 'fetch-tweets' ) : __( 'Connect', 'fetch-tweets' ) ; ?>" />
						</a>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Manual', 'fetch-tweets' ); ?>
					</th>
					<td>
						<a href="<?php echo add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication' ), admin_url( $GLOBALS['pagenow'] ) ); ?>">
							<input type="submit" class="button button-secondary" value="<?php _e( 'Set Keys Manually', 'fetch-tweets' ); ?>"/>
						</a>
					</td>
				</tr>		
			</tbody>
		</table>
					
		<?php		
	}
			
		
}