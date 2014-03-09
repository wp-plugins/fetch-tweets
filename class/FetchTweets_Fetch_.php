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
abstract class FetchTweets_Fetch_ extends FetchTweets_Fetch_Format {
	
	public function getTweetsOutputByTag( $arrArgs ) {
		
		// Called from the shortcode callback.
		// Capture the output buffer
		ob_start(); // start buffer
		$this->drawTweetsByTag( $arrArgs );
		$strContent = ob_get_contents(); // assign the content buffer to a variable
		ob_end_clean(); // end buffer and remove the buffer		
		return $strContent;
		
	}	
	public function drawTweetsByTag( $arrArgs ) {

		// Called from either the above getTweetsOutputByTag() method for the shortcode callbeck or fetchTweets() function.
		$arrArgs['tag'] = isset( $arrArgs['tags'] ) && ! empty( $arrArgs['tags'] ) 
			? $arrArgs['tags'] 
			: ( isset( $arrArgs['tag'] ) 
				? $arrArgs['tag']
				: null );	// backward compatibility
		$arrArgs['tag'] = is_array( $arrArgs['tag'] ) ? $arrArgs['tag'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['tag'] ), 0, PREG_SPLIT_NO_EMPTY );
		$arrArgs = ( array ) $arrArgs + $this->oOption->aOptions['default_values'] + $this->oOption->aStructure_DefaultParams + $this->oOption->aStructure_DefaultTemplateOptions;
		$arrArgs['id'] = isset( $arrArgs['tag_field_type'] ) && in_array( strtolower( $arrArgs['tag_field_type'] ), array( 'id', 'slug' ) )
			? $this->getPostIDsByTag( $arrArgs['tag'], $arrArgs['tag_field_type'], trim( $arrArgs['operator'] ) )
			: $this->getPostIDsByTagName( $arrArgs['tag'], trim( $arrArgs['operator'] ) );
	
		$this->drawTweets( $arrArgs );
			
	}
	public function getPostIDsByTagName( $vTermNames, $strOperator='AND' ) {	// public as the feeder extension uses it.
		
		$arrTermSlugs = array();
		foreach( ( array ) $vTermNames as $strTermName ) {
			
			$arrTerm = get_term_by( 'name', $strTermName, FetchTweets_Commons::TagSlug, ARRAY_A );
			$arrTermSlugs[] = $arrTerm['slug'];
			
		}
		return $this->getPostIDsByTag( $arrTermSlugs, 'slug', $strOperator );
				
	}
	public function getPostIDsByTag( $arrTermSlugs, $strFieldType='slug', $strOperator='AND' ) {	// public as the feeder extension uses it.

		if ( empty( $arrTermSlugs ) )
			return array();
			
		$strFieldType = $this->sanitizeFieldKey( $strFieldType );

		$arrPostObjects = get_posts( 
			array(
				'post_type' => FetchTweets_Commons::PostTypeSlug,	// fetch_tweets
				'posts_per_page' => -1, // ALL posts
				'tax_query' => array(
					array(
						'taxonomy' => FetchTweets_Commons::TagSlug,	// fetch_tweets_tag
						'field' => $strFieldType,	// id or slug
						'terms' => $arrTermSlugs,	// the array of term slugs
						'operator' => $this->sanitizeOperator( $strOperator ),	// 'IN', 'NOT IN', 'AND. If the item is only one, use AND.
					)
				)
			)
		);
		$arrIDs = array();
		foreach( $arrPostObjects as $oPost )
			$arrIDs[] = $oPost->ID;
		return array_unique( $arrIDs );
		
	}
		protected function sanitizeFieldKey( $strField ) {
			switch( strtolower( trim( $strField ) ) ) {
				case 'id':
					return 'id';
				default:
				case 'slug':
					return 'slug';
			}		
		}
		protected function sanitizeOperator( $strOperator ) {
			switch( strtoupper( trim( $strOperator ) ) ) {
				case 'NOT IN':
					return 'NOT IN';
				case 'IN':
					return 'IN';
				default:
				case 'AND':
					return 'AND';
			}
		}
	
	public function getTweetsOutput( $arrArgs ) {	// called from the shortcode callback.
		
		// Capture the output buffer.
		ob_start(); // Start buffer.
		$this->drawTweets( $arrArgs );
		$strContent = ob_get_contents(); // Assign the content buffer to a variable.
		ob_end_clean(); // End buffer and remove the buffer.
		return $strContent;
		
	}

	public function drawTweets( $arrArgs ) {

		/*
		 * $arrArgs 
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
		
		$arrRawArgs = ( array ) $arrArgs; 
		$arrArgs = FetchTweets_Utilities::uniteArrays( $arrRawArgs, $this->oOption->aOptions['default_values'], $this->oOption->aStructure_DefaultParams, $this->oOption->aStructure_DefaultTemplateOptions );
		// $arrArgs = $arrRawArgs + $this->oOption->aOptions['default_values'] + $this->oOption->aStructure_DefaultParams + $this->oOption->aStructure_DefaultTemplateOptions;
		$arrArgs['id'] = isset( $arrArgs['ids'] ) && ! empty( $arrArgs['ids'] ) ? $arrArgs['ids'] : $arrArgs['id'];	// backward compatibility
		$arrArgs['id'] = is_array( $arrArgs['id'] ) ? $arrArgs['id'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['id'] ), 0, PREG_SPLIT_NO_EMPTY );

		// Debug
		// echo var_dump( $arrArgs );
		// echo "<pre>" . htmlspecialchars( print_r( $arrArgs, true ) ) . "</pre>";	
		// echo "<pre>" . htmlspecialchars( print_r( $this->oOption->aOptions, true ) ) . "</pre>";	
		// return;		

		$arrTweets = $this->getTweetsAsArray( $arrArgs, $arrRawArgs );
		if ( empty( $arrTweets ) || ! is_array( $arrTweets ) ) {
			_e( 'No result could be fetched.', 'fetch-tweets' );
			return;
		}
		if ( isset( $arrTweets['errors'][ 0 ]['message'], $arrTweets['errors'][ 0 ]['code'] ) ) {
			echo '<strong>Fetch Tweets</strong>: ' . $arrTweets['errors'][ 0 ]['message'] . ' Code:' . $arrTweets['errors'][ 0 ]['code'];			
			return;
		}

		// Format the array.
		$this->formatTweetArrays( $arrTweets, $arrArgs['avatar_size'], $arrArgs['twitter_media'], $arrArgs['external_media'] ); // the array is passed as reference.
		// Sort by time.
		$this->sortTweetArrays( $arrTweets, $arrArgs['sort'] ); // the array is passed as reference.

		// Truncate the array.
		if ( $arrArgs['count'] && is_numeric( $arrArgs['count'] ) ) 
			array_splice( $arrTweets, $arrArgs['count'] );

		/*
		 * Include the template to render the output - this method is also called from filter callbacks( which requires a return value ) but go ahead and render the output.		
		 * */		
		 
		// Make it easier for the template script to access the plugin options.
		$aOptions = $this->oOption->aOptions; 
		
		// Retrieve the template slug we are going to use.
		$arrArgs['template'] = $this->getTemplateSlug( $arrArgs['id'], $arrArgs['template'] );
		
		// Call the template. ( template.php )
		$strTemplatePath = apply_filters( "fetch_tweets_template_path", $this->getTemplatePath( $arrArgs['id'], $arrArgs['template'] ), $arrArgs );
		include( $strTemplatePath );
		
	}
	protected function getTemplateSlug( $arrPostIDs, $strTemplateSlug='' ) {
					
		// Return the one defined in the caller argument.
		if ( $strTemplateSlug && isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] ) )
			return $this->checkNecessaryFileExists( $strTemplateSlug );
		
		// Return the one defined in the custom post rule.
		if ( isset( $arrPostIDs[ 0 ] ) )
			$strTemplateSlug = get_post_meta( $arrPostIDs[ 0 ], 'fetch_tweets_template', true );

		$strTemplateSlug = $this->checkNecessaryFileExists( $strTemplateSlug );
		
		// Find the default template slug.
		if ( 
			empty( $strTemplateSlug ) 
			|| ! isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] ) 
		)
			return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug();
		
		// Something wrong happened.
		return $strTemplateSlug;
		
	}
	protected function checkNecessaryFileExists( $strTemplateSlug ) {
		
		// Check if the necessary file is present. Otherwise, return the default template slug.
		if ( 
			( ! empty( $strTemplateSlug ) || $strTemplateSlug != '' ) 
			&& ( 
				! isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] )	// this happens when the options have been reset.
				|| ! file_exists( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strDirPath'] . '/template.php' )
				|| ! file_exists( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strDirPath'] . '/style.css' )
			)
		)
			return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug();		
		
		return $strTemplateSlug;
		
	}
	protected function getTemplatePath( $arrPostIDs, $strTemplateSlug ) {
		
		if ( empty( $strTemplateSlug ) && isset( $arrPostIDs[ 0 ] ) )
			$strTemplateSlug = get_post_meta( $arrPostIDs[ 0 ], 'fetch_tweets_template', true );
		
		if ( empty( $strTemplateSlug ) || ! isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] ) )
			return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplatePath();
			
		$strTemplatePath = $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strTemplatePath'];
		$strTemplatePath = ( ! $strTemplatePath || ! file_exists( $strTemplatePath ) )
			? dirname( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strCSSPath'] ) . '/template.php'
			: $strTemplatePath;
		return $strTemplatePath;			
		
	}
	
	/**
	 * Fetches tweets based on the argument.
	 * 
	 * @param			array			$arrArgs			The argument array that is merged with the default option values.
	 * @param			array			$arrRawArgs			The raw argument array that is not merged with any. Used by the getTweetsAsArrayByPostIDs() method that fetches tweets by post ID.
	 */
	public function getTweetsAsArray( $arrArgs, $arrRawArgs ) {	// this is public as the feed extension uses it.

		if ( isset( $arrArgs['q'] ) )	// custom call by search keyword
			return $this->getTweetsBySearch( $arrArgs['q'], $arrArgs['count'], $arrArgs['lang'], $arrArgs['result_type'], $arrArgs['until'], $arrArgs['geocode'], $arrArgs['cache'] );
		else if ( isset( $arrArgs['screen_name'] ) )	// custom call by screen name
			return $this->getTweetsByScreenNames( $arrArgs['screen_name'], $arrArgs['count'], $arrArgs['include_rts'], $arrArgs['exclude_replies'], $arrArgs['cache'] );
		else if ( isset( $arrArgs['list_id'] ) ) 	
			return $this->getTweetsByListID( $arrArgs['list_id'], $arrArgs['count'], $arrArgs['include_rts'], $arrArgs['cache'] );
		else	// normal
			return $this->getTweetsAsArrayByPostIDs( $arrArgs['id'], $arrArgs, $arrRawArgs );
		
	}
	protected function getTweetsAsArrayByPostIDs( $vPostIDs, $arrArgs, $arrRawArgs ) {	
	
		$arrTweets = array();
		foreach( ( array ) $vPostIDs as $intPostID ) {
			
			$arrArgs['tweet_type'] = get_post_meta( $intPostID, 'tweet_type', true );
			$arrArgs['count'] = get_post_meta( $intPostID, 'item_count', true );
			$arrArgs['include_retweets'] = get_post_meta( $intPostID, 'include_retweets', true );
			$arrArgs['cache'] = get_post_meta( $intPostID, 'cache', true );

			switch ( $arrArgs['tweet_type'] ) {
				case 'search':
					$arrArgs['q'] = get_post_meta( $intPostID, 'search_keyword', true );	
					$arrArgs['result_type'] = get_post_meta( $intPostID, 'result_type', true );
					$arrArgs['lang'] = get_post_meta( $intPostID, 'language', true );
					$arrArgs['until'] = get_post_meta( $intPostID, 'until', true );
					$arrArgs['geocentric_coordinate'] = get_post_meta( $intPostID, 'geocentric_coordinate', true );
					$arrArgs['geocentric_radius'] = get_post_meta( $intPostID, 'geocentric_radius', true );
					$strGeoCode = '';
					if ( 
						is_array( $arrArgs['geocentric_coordinate'] ) && is_array( $arrArgs['geocentric_radius'] )
						&& isset( $arrArgs['geocentric_coordinate']['latitude'], $arrArgs['geocentric_radius']['size'] ) 
						&& $arrArgs['geocentric_coordinate']['latitude'] !== '' && $arrArgs['geocentric_coordinate']['longitude'] !== ''	// the coordinate can be 0
						&& $arrArgs['geocentric_radius']['size'] !== '' 
					) {
						// "latitude,longitude,radius",
						$strGeoCode = trim( $arrArgs['geocentric_coordinate']['latitude'] ) . "," . trim( $arrArgs['geocentric_coordinate']['longitude'] ) 
							. "," . trim( $arrArgs['geocentric_radius']['size'] ) . $arrArgs['geocentric_radius']['unit'] ;
					}
					$arrArgs = FetchTweets_Utilities::uniteArrays( $arrRawArgs, $arrArgs ); // The direct input takes its precedence.
					$arrRetrievedTweets = $this->getTweetsBySearch( $arrArgs['q'], $arrArgs['count'], $arrArgs['lang'], $arrArgs['result_type'], $arrArgs['until'], $strGeoCode, $arrArgs['cache'] );
					break;
				case 'list':
					$arrArgs['list_id'] = get_post_meta( $intPostID, 'list_id', true );					
					$arrArgs = FetchTweets_Utilities::uniteArrays( $arrRawArgs, $arrArgs ); // The direct input takes its precedence.
					$arrRetrievedTweets = $this->getTweetsByListID( $arrArgs['list_id'], $arrArgs['count'], $arrArgs['include_retweets'], $arrArgs['cache'] );
					break;
				case 'screen_name':
				default:	
					$arrArgs['screen_name'] = get_post_meta( $intPostID, 'screen_name', true );	
					$arrArgs['exclude_replies'] = get_post_meta( $intPostID, 'exclude_replies', true );	
					$arrArgs = FetchTweets_Utilities::uniteArrays( $arrRawArgs, $arrArgs ); // The direct input takes its precedence.
					$arrRetrievedTweets = $this->getTweetsByScreenNames( $arrArgs['screen_name'], $arrArgs['count'], $arrArgs['include_retweets'], $arrArgs['exclude_replies'], $arrArgs['cache'] );
					break;				
			}	

			$arrTweets = array_merge( $arrRetrievedTweets, $arrTweets );
				
		}
		
		return $arrTweets;
		
	}
				
	/**
	 * Fetches tweets by search keyword.
	 * 
	 * @see			https://dev.twitter.com/docs/api/1.1/get/search/tweets
	 */ 
	protected function getTweetsBySearch( $strKeyword, $intCount=100, $strLang='en', $strResultType='mixed', $strUntil='', $strGeoCode='', $intCacheDuration=600 ) {

		// Compose the request URI.
		$intCount = 100;	// as of v1.3.4, request will be performed with the maximum count so that the caches will be reused for ones with lesser counts.
		$strRequestURI = "https://api.twitter.com/1.1/search/tweets.json"
			. "?q=" . urlencode_deep( $strKeyword )	// . "?q=" . urlencode_deep( 'from:personA+OR+from:personB+OR+from:personC+OR+from:personC' )  
			. "&result_type={$strResultType}"	//  mixed, recent, popular
			. "&count={$intCount}"
			. ( $strLang == 'none' ? "" : "&lang={$strLang}" )
			. ( empty( $strUntil ) ? "" : "&until={$strUntil}" )
			. ( empty( $strGeoCode ) ? "" : "&geocode={$strGeoCode}" )
			. "&include_entities=1";		
		return $this->doAPIRequest_Get( $strRequestURI, 'statuses', $intCacheDuration );
					
	}
	
	
	/**
	 * Returns an array holding the list IDs and names from the given owner screen name.
	 * 
	 * @remark			This is used for the form filed select option.
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
	
	/**
	 * Fetches tweets by screen names.
	 * 
	 * The plural form of the getTweetsByScreenName() method. Multiple screen names can be passed separated by commas.
	 * 
	 * @since			1.3.3
	 */
	protected function getTweetsByScreenNames( $strUsers, $intCount, $fIncludeRetweets=false, $fExcludeReplies=false, $intCacheDuration=1200 ) {

		$arrTweets = array();
		$arrScreenNames = FetchTweets_Utilities::convertStringToArray( $strUsers, ',' );
		foreach( $arrScreenNames as $strScreenName ) 			
			$arrTweets = array_merge( $this->getTweetsByScreenName( $strScreenName, $intCount, $fIncludeRetweets, $fExcludeReplies, $intCacheDuration ), $arrTweets );
			
		return $arrTweets;
		
	}
	
	/**
	 * Fetches tweets by screen name.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
	 * @since			1.0.0
	 */ 
	protected function getTweetsByScreenName( $strUser, $intCount, $fIncludeRetweets=false, $fExcludeReplies=false, $intCacheDuration=1200 ) {

		// Compose the request URI.
		// $intCount = ( ( int ) $intCount ) > 200 ? 200 : $intCount;
		$intCount = 200;	// as of 1.3.4 the maximum number of tweets are fetched so that the data can be reused for different count requests.
		$strRequestURI = "https://api.twitter.com/1.1/statuses/user_timeline.json"
			. "?screen_name={$strUser}"
			. "&count={$intCount}"
			. "&include_rts=" . ( $fIncludeRetweets ? 1 : 0 )
			. "&exclude_replies=" . ( $fExcludeReplies ? 1 : 0 );
		
		return $this->doAPIRequest_Get( $strRequestURI, null, $intCacheDuration );
				
	}
	
}