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
	protected function _getVerificationStatus() {

		// If the access token and access secret keys have been manually set,
		$_aStatus = $this->oOption->isAuthKeysManuallySet()
			? $this->_getAuthenticationStatus( $this->oOption->getConsumerKey(), $this->oOption->getConsumerSecret(), $this->oOption->getAccessToken(), $this->oOption->getAccessTokenSecret() )
			: array();
			
		if ( ! empty( $_aStatus ) ) return $_aStatus;
			
		// If the access token and secret keys have been automatically set,
		if ( $this->oOption->isAuthKeysAutomaticallySet() ) {
			$_aStatus = $this->_getAuthenticationStatus( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $this->oOption->getAccessTokenAuto(), $this->oOption->getAccessTokenSecretAuto() );
		}
	
		return $_aStatus;
	
	}
	
		/**
		 * Checks the API credential is valid or not.
		 * 	 
		 * @since			1.3.0
		 * @return			array			the retrieved data.
		 * @remark			The returned data is a merged result of 'account/verify_credientials' and 'rate_limit_status'.
		 */
		protected function _getAuthenticationStatus( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret ) {
			
			$oTwitterOAuth_Verification = new FetchTweets_TwitterAPI_Verification( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret );
			return $oTwitterOAuth_Verification->getStatus();
			
		}

		
	/**
	 * Renders the authentication status table.
	 * 
	 * @since			1.3.0
	 * @remark			$aStatus can be null when a cache for the API request is not stored.
	 * @param			array			$aStatus			This arrays should be the merged array of the results of 'account/verify_credientials' and 'rate_limit_status' requests.
	 * 
	 */
	protected function _renderAuthenticationStatus( $aStatus ) {
		
		$_fIsValid = isset( $aStatus['id'] ) && $aStatus['id'] ? true : false;
		$_sScreenName = isset( $aStatus['screen_name'] ) ? $aStatus['screen_name'] : "";
		
		$_iOffsetSeconds = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		$_sHomeimelineLimit = isset( $aStatus['resources']['statuses']['/statuses/home_timeline'] )
			? $aStatus['resources']['statuses']['/statuses/home_timeline']['remaining'] . ' / ' . $aStatus['resources']['statuses']['/statuses/home_timeline']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $aStatus['resources']['statuses']['/statuses/home_timeline']['reset'] + $_iOffsetSeconds ) . " )"
			: "";		
		
		$_sUserTimelineLimit = isset( $aStatus['resources']['statuses']['/statuses/user_timeline'] )
			? $aStatus['resources']['statuses']['/statuses/user_timeline']['remaining'] . ' / ' . $aStatus['resources']['statuses']['/statuses/user_timeline']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $aStatus['resources']['statuses']['/statuses/user_timeline']['reset'] + $_iOffsetSeconds ) . " )"
			: "";		
			
		$_sSearchLimit = isset( $aStatus['resources']['search']['/search/tweets'] ) 
			? $aStatus['resources']['search']['/search/tweets']['remaining'] . ' / ' . $aStatus['resources']['search']['/search/tweets']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $aStatus['resources']['search']['/search/tweets']['reset'] + $_iOffsetSeconds ) . " )"
			: "";

		$_sListLimit = isset( $aStatus['resources']['lists']['/lists/statuses'] )
			? $aStatus['resources']['lists']['/lists/statuses']['remaining'] . ' / ' . $aStatus['resources']['lists']['/lists/statuses']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $aStatus['resources']['lists']['/lists/statuses']['reset'] + $_iOffsetSeconds ) . " )"
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
						<?php echo $_fIsValid ? '<span class="authenticated">' . __( 'Authenticated', 'fetch-tweets' ) . '</span>': '<span class="unauthenticated">' . __( 'Not authenticated', 'fetch-tweets' ) . '</span>'; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Screen Name', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $_sScreenName; ?>
					</td>
				</tr>
			</tbody>
		</table>				
		<h3><?php _e( 'Request Limits', 'fetch-tweets' ); ?></h3>			
		<table class="form-table auth-status">
			<tbody>		
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Home Timeline', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $_sHomeimelineLimit; ?>
					</td>
				</tr>					
				<tr valign="top">
					<th scope="row">
						<?php _e( 'User Timeline', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $_sUserTimelineLimit; ?>
					</td>
				</tr>	
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Search', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $_sSearchLimit; ?>
					</td>
				</tr>	
				<tr valign="top">
					<th scope="row">
						<?php _e( 'List', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $_sListLimit; ?>
					</td>
				</tr>				
			</tbody>
		</table>
					
		<?php
// $this->oDebug->dumpArray( $aStatus );		
	}
		
}