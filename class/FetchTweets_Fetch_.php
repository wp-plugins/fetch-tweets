<?php
/**
 * Fetches and displays tweets.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @filters			fetch_tweets_template_path - specifies the template path.
 * @actions			fetch_tweets_action_transient_renewal - for WP Cron single event.
 * @actions			fetch_tweets_action_transient_add_oembed_elements - for WP Cron single event.
 */
abstract class FetchTweets_Fetch_ extends FetchTweets_Fetch_ByHomeTimeline {
	
	public function getTweetsOutput( $aArgs ) {	// called from the shortcode callback.
		
		// Capture the output buffer.
		ob_start(); // Start buffer.
		$this->drawTweets( $aArgs );
		$_sContent = ob_get_contents(); // Assign the content buffer to a variable.
		ob_end_clean(); // End buffer and remove the buffer.
		return $_sContent;
		
	}

	/**
	 * Prints tweets based on the given arguments.
	 * 
	 * @param			array			$aArgs 
	 * 	id - The post id. default: null. e.g. 125  or 124, 235
	 * 	tag - default: null. e.g. php or php, WordPress. In this method this tag is only used to pass the argument to the template filter.
	 *  sort - default: descending. Either ascending, descending, or random can be used.
	 * 	count - default: 20
	 * 	operator - default: AND. Either AND or IN or NOT IN is used.
	 *  q - default: null e.g. WordPress
	 *  screen_name - default: null e.g. miunosoft
	 *  include_rts - default: 0. Either 1 or 0.
	 *  exclude_replies - default: 0. Either 1 or 0.
	 *  cache - default: 1200
	 *	lang - default: null.  
	 *	result_type - default: mixed
	 *	list_id - default: null. e.g. 8044403
	 *	twitter_media - ( boolean ) determines whether the Twitter media should be displayed or not. Currently only photos are supported by the Twitter API.
	 *	external_media - ( boolean ) determines whether the plugin attempts to replace external media links to embedded elements.
	 *
	 * Template options
	 *	template - the template slug.
	 *	width - 
	 *	width_unit - 
	 *	height	- 
	 *	height_unit - 
	 *	avatar_size - default: 48 
	 * 
	 * */	
	public function drawTweets( $aArgs ) {
		
		$aRawArgs = ( array ) $aArgs; 
		$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $this->oOption->aOptions['default_values'], $this->oOption->aStructure_DefaultParams, $this->oOption->aStructure_DefaultTemplateOptions );
		$aArgs['id'] = isset( $aArgs['ids'] ) && ! empty( $aArgs['ids'] ) ? $aArgs['ids'] : $aArgs['id'];	// backward compatibility
		$aArgs['id'] = is_array( $aArgs['id'] ) ? $aArgs['id'] : preg_split( "/[,]\s*/", trim( ( string ) $aArgs['id'] ), 0, PREG_SPLIT_NO_EMPTY );

		// Debug
		// echo var_dump( $aArgs );
		// echo "<pre>" . htmlspecialchars( print_r( $aArgs, true ) ) . "</pre>";	
		// echo "<pre>" . htmlspecialchars( print_r( $this->oOption->aOptions, true ) ) . "</pre>";	
		// return;		

		$_aTweets = $this->getTweetsAsArray( $aArgs, $aRawArgs );
		if ( empty( $_aTweets ) || ! is_array( $_aTweets ) ) {
			_e( 'No result could be fetched.', 'fetch-tweets' );
			return;
		}
		if ( isset( $_aTweets['errors'][ 0 ]['message'], $_aTweets['errors'][ 0 ]['code'] ) ) {
			echo '<strong>Fetch Tweets</strong>: ' . $_aTweets['errors'][ 0 ]['message'] . ' Code:' . $_aTweets['errors'][ 0 ]['code'];			
			return;
		}

		// Format the tweet response array.
		$this->_formatTweetArrays( $_aTweets, $aArgs['avatar_size'], $aArgs['twitter_media'], $aArgs['external_media'] ); // the array is passed as reference.
	
		// Sort by time - the array is passed as reference.
		$this->_sortTweetArrays( $_aTweets, $aArgs['sort'] ); 

		// Truncate the array.
		if ( $aArgs['count'] && is_numeric( $aArgs['count'] ) ) {
			array_splice( $_aTweets, $aArgs['count'] );
		}

		/* Include the template to render the output - this method is also called from filter callbacks( which requires a return value ) but go ahead and render the output. */		
		$this->_includeTemplate( $_aTweets, $aArgs, $this->oOption->aOptions );
 		
	}

	
	/**
	 * Fetches tweets based on the argument.
	 * 
	 * @remark			The scope is public as the feed extension uses it.
	 * @param			array			$arrArgs			The argument array that is merged with the default option values.
	 * @param			array			$aRawArgs			The raw argument array that is not merged with any. Used by the _getTweetsAsArrayByPostIDs() method that fetches tweets by post ID.
	 */
	public function getTweetsAsArray( $aArgs, $aRawArgs ) {	

		if ( isset( $aArgs['q'] ) )	// custom call by search keyword
			return $this->getTweetsBySearch( $aArgs['q'], $aArgs['count'], $aArgs['lang'], $aArgs['result_type'], $aArgs['until'], $aArgs['geocode'], $aArgs['cache'] );
		else if ( isset( $aArgs['screen_name'] ) )	// custom call by screen name
			return $this->getTweetsByScreenNames( $aArgs['screen_name'], $aArgs['count'], $aArgs['include_rts'], $aArgs['exclude_replies'], $aArgs['cache'] );
		else if ( isset( $aArgs['list_id'] ) ) 	
			return $this->getTweetsByListID( $aArgs['list_id'], $aArgs['count'], $aArgs['include_rts'], $aArgs['cache'] );
		else if ( isset( $aArgs['account'] ) )
			return $this->_getTweetsByHomeTimeline( $aArgs['account'], $aArgs['exclude_replies'] );
		else	// normal
			return $this->_getTweetsAsArrayByPostIDs( $aArgs['id'], $aArgs, $aRawArgs );
		
	}
		protected function _getTweetsAsArrayByPostIDs( $vPostIDs, $aArgs, $aRawArgs ) {	
		
			$_aTweets = array();
			foreach( ( array ) $vPostIDs as $_iPostID ) {
				
				$aArgs['tweet_type'] = get_post_meta( $_iPostID, 'tweet_type', true );
				$aArgs['count'] = get_post_meta( $_iPostID, 'item_count', true );
				$aArgs['include_retweets'] = get_post_meta( $_iPostID, 'include_retweets', true );
				$aArgs['cache'] = get_post_meta( $_iPostID, 'cache', true );
				
				$_aRetrievedTweets = array();
				switch ( $aArgs['tweet_type'] ) {
					case 'search':
						$aArgs['q'] = get_post_meta( $_iPostID, 'search_keyword', true );	
						$aArgs['result_type'] = get_post_meta( $_iPostID, 'result_type', true );
						$aArgs['lang'] = get_post_meta( $_iPostID, 'language', true );
						$aArgs['until'] = get_post_meta( $_iPostID, 'until', true );
						$aArgs['geocentric_coordinate'] = get_post_meta( $_iPostID, 'geocentric_coordinate', true );
						$aArgs['geocentric_radius'] = get_post_meta( $_iPostID, 'geocentric_radius', true );
						$_sGeoCode = '';
						if ( 
							is_array( $aArgs['geocentric_coordinate'] ) && is_array( $aArgs['geocentric_radius'] )
							&& isset( $aArgs['geocentric_coordinate']['latitude'], $aArgs['geocentric_radius']['size'] ) 
							&& $aArgs['geocentric_coordinate']['latitude'] !== '' && $aArgs['geocentric_coordinate']['longitude'] !== ''	// the coordinate can be 0
							&& $aArgs['geocentric_radius']['size'] !== '' 
						) {
							// "latitude,longitude,radius",
							$_sGeoCode = trim( $aArgs['geocentric_coordinate']['latitude'] ) . "," . trim( $aArgs['geocentric_coordinate']['longitude'] ) 
								. "," . trim( $aArgs['geocentric_radius']['size'] ) . $aArgs['geocentric_radius']['unit'] ;
						}
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->getTweetsBySearch( $aArgs['q'], $aArgs['count'], $aArgs['lang'], $aArgs['result_type'], $aArgs['until'], $_sGeoCode, $aArgs['cache'] );
						break;
					case 'list':
						$aArgs['list_id'] = get_post_meta( $_iPostID, 'list_id', true );
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->getTweetsByListID( $aArgs['list_id'], $aArgs['count'], $aArgs['include_retweets'], $aArgs['cache'] );
						break;
					case 'home_timeline':
						$aArgs['account'] = get_post_meta( $_iPostID, 'account', true );
						$aArgs['exclude_replies'] = get_post_meta( $_iPostID, 'account', true );
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->_getTweetsByHomeTimeline( $aArgs['account'], $aArgs['exclude_replies'], $aArgs['cache'] );
						break;
					case 'screen_name':
					default:	
						$aArgs['screen_name'] = get_post_meta( $_iPostID, 'screen_name', true );	
						$aArgs['exclude_replies'] = get_post_meta( $_iPostID, 'exclude_replies', true );	
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->getTweetsByScreenNames( $aArgs['screen_name'], $aArgs['count'], $aArgs['include_retweets'], $aArgs['exclude_replies'], $aArgs['cache'] );
						break;				
				}	

				$_aTweets = array_merge( $_aRetrievedTweets, $_aTweets );
					
			}
			
			return $_aTweets;
			
		}
	
}