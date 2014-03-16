<?php
/**
 * Handles Twitter API verification
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_TwitterAPI_Verification_ {

	function __construct( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret ) {
		
		$this->sConsumerKey = $sConsumerKey;
		$this->sConsumerSecret = $sConsumerSecret;
		$this->sAccessToken = $sAccessToken;
		$this->sAccessSecret = $sAccessSecret;
		
	}
	
	public function getStatus() {
		
		// Return the cached response if available.
		$_sCacheID = FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $this->sConsumerKey, $this->sConsumerSecret, $this->sAccessToken, $this->sAccessSecret ) ) );
		$_vData = get_transient( $_sCacheID );
		if ( false !== $_vData ) return $_vData;
		
		// Perform the requests.
		$_oConnect =  new FetchTweets_TwitterOAuth( $this->sConsumerKey, $this->sConsumerSecret, $this->sAccessToken, $this->sAccessSecret );
		$_aUser = $_oConnect->get( 'account/verify_credentials' );
		
		// If the user id could not be retrieved, it means it failed.
		if ( ! isset( $_aUser['id'] ) || ! $_aUser['id'] ) return array();
			
		// Otherwise, it is okay. Retrieve the current status.
		$_aStatus = $_oConnect->get( 'https://api.twitter.com/1.1/application/rate_limit_status.json?resources=search,statuses,lists' );
		
		// Set the cache.
		$_aData = is_array( $_aStatus ) ? $_aUser + $_aStatus : $_aUser;
		set_transient( $_sCacheID, $_aData, 60 );	// stores the cache only for 60 seconds. It is allowed 15 requests in 15 minutes.
		
		return $_aData;	
		
	}
	
	/**
	 * Renders the output of status table.
	 */
	static public function renderStatus( $aStatus ) {
		
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