<?php
/**
 * Fetches and displays tweets.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @filters			fetch_tweets_template_path - specifies the template path.
 * @actions			fetch_tweets_action_transient_renewal - for WP Cron single event.
 */
abstract class FetchTweets_Fetch_ {

	protected $arrExpiredTransientsRequestURIs = array(); // stores the expired transients' request URIs
	
	public function __construct() {
	
		// Set up the connection.
		$this->oOption = & $GLOBALS['oFetchTweets_Option'];		
		$this->oTwitterOAuth =  new WP_TwitterOAuth( 
			$this->oOption->getConsumerKey(), 
			$this->oOption->getConsumerSecret(), 
			$this->oOption->getAccessToken(), 
			$this->oOption->getAccessTokenSecret()
		);		
			
		// Objects
		$this->oEmbed = new FetchTweets_oEmbed;
		$this->oBase64 = new FetchTweets_Base64;	
			
		// Schedule the transient update task.
		add_action( 'shutdown', array( $this, 'updateCacheItems' ) );
		
	}
	
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
		 
		$arrArgs = ( array ) $arrArgs + $this->oOption->arrOptions['fetch_tweets_settings']['default_values'] + $this->oOption->arrStructure_DefaultParams + $this->oOption->arrStructure_DefaultTemplateOptions;
		$arrArgs['id'] = isset( $arrArgs['ids'] ) && ! empty( $arrArgs['ids'] ) ? $arrArgs['ids'] : $arrArgs['id'];	// backward compatibility
		$arrArgs['id'] = is_array( $arrArgs['id'] ) ? $arrArgs['id'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['id'] ), 0, PREG_SPLIT_NO_EMPTY );

// Debug
// echo var_dump( $arrArgs );
// echo "<pre>" . htmlspecialchars( print_r( $arrArgs, true ) ) . "</pre>";	
// echo "<pre>" . htmlspecialchars( print_r( $this->oOption->arrOptions, true ) ) . "</pre>";	
// return;		

		$arrTweets = $this->getTweetsAsArray( $arrArgs );
		if ( empty( $arrTweets ) || ! is_array( $arrTweets ) ) {
			_e( 'No result could be fetched.', 'fetch-tweets' );
			return;
		}
		if ( isset( $arrTweets['errors'][ 0 ]['message'], $arrTweets['errors'][ 0 ]['code'] ) ) {
			echo '<strong>Fetch Tweets</strong>: ' . $arrTweets['errors'][ 0 ]['message'] . ' Code:' . $arrTweets['errors'][ 0 ]['code'];			
			return;
		}
	
		// Format the array.
// var_dump( $arrTweets );		
		$this->formatTweetArrays( $arrTweets, $arrArgs['avatar_size'], $arrArgs['twitter_media'], $arrArgs['external_media'] ); // the array is passed as reference.
	
		// Sort by time.
		$this->sortTweetArrays( $arrTweets, $arrArgs['sort'] ); // the array is passed as reference.

		// Truncate the array.
		if ( $arrArgs['count'] && is_numeric( $arrArgs['count'] ) ) 
			array_splice( $arrTweets, $arrArgs['count'] );
	
// For debug
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";		
// return;
		
		/*
		 * Include the template to render the output - this method is also called from filter callbacks( which requires a return value ) but go ahead and render the output.		
		 * */		
		 
		// Make it easier for the template script to access the plugin options.
		$arrOptions = $this->oOption->arrOptions; 
		
		// Retrieve the template slug we are going to use.
		$arrArgs['template'] = $this->getTemplateSlug( $arrArgs['id'], $arrArgs['template'] );
		
		// Call the template. ( template.php )
		$strTemplatePath = apply_filters( "fetch_tweets_template_path", $this->getTemplatePath( $arrArgs['id'], $arrArgs['template'] ), $arrArgs );
		include( $strTemplatePath );
		
	}
	protected function getTemplateSlug( $arrPostIDs, $strTemplateSlug='' ) {
					
		// Return the one defined in the caller argument.
		if ( $strTemplateSlug && isset( $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ] ) )
			return $this->checkNecessaryFileExists( $strTemplateSlug );
		
		// Return the one defined in the custom post rule.
		if ( isset( $arrPostIDs[ 0 ] ) )
			$strTemplateSlug = get_post_meta( $arrPostIDs[ 0 ], 'fetch_tweets_template', true );

		$strTemplateSlug = $this->checkNecessaryFileExists( $strTemplateSlug );
		
		// Find the default template slug.
		if ( 
			empty( $strTemplateSlug ) 
			|| ! isset( $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ] ) 
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
				! isset( $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ] )	// this happens when the options have been reset.
				|| ! file_exists( $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ]['strDirPath'] . '/template.php' )
				|| ! file_exists( $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ]['strDirPath'] . '/style.css' )
			)
		)
			return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug();		
		
		return $strTemplateSlug;
		
	}
	protected function getTemplatePath( $arrPostIDs, $strTemplateSlug ) {
		
		if ( empty( $strTemplateSlug ) && isset( $arrPostIDs[ 0 ] ) )
			$strTemplateSlug = get_post_meta( $arrPostIDs[ 0 ], 'fetch_tweets_template', true );
		
		if ( empty( $strTemplateSlug ) || ! isset( $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ] ) )
			return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplatePath();
			
		$strTemplatePath = $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ]['strTemplatePath'];
		$strTemplatePath = ( ! $strTemplatePath || ! file_exists( $strTemplatePath ) )
			? dirname( $this->oOption->arrOptions['arrTemplates'][ $strTemplateSlug ]['strCSSPath'] ) . '/template.php'
			: $strTemplatePath;
		return $strTemplatePath;			
		
	}
	public function getTweetsAsArray( $arrArgs ) {	// this is public as the feed extension uses it.
	
		if ( isset( $arrArgs['q'] ) )	// custom call by search keyword
			return $this->getTweetsBySearch( $arrArgs['q'], $arrArgs['count'], $arrArgs['lang'], $arrArgs['include_rts'], $arrArgs['result_type'], $arrArgs['cache'] );
		else if ( isset( $arrArgs['screen_name'] ) )	// custom call by screen name
			return $this->getTweetsByScreenName( $arrArgs['screen_name'], $arrArgs['count'], $arrArgs['include_rts'], $arrArgs['exclude_replies'], $arrArgs['cache'] );
		else if ( isset( $arrArgs['list_id'] ) ) 	
			return $this->getTweetsByListID( $arrArgs['list_id'], $arrArgs['count'], $arrArgs['include_rts'], $arrArgs['cache'] );
		else	// normal
			return $this->getTweetsAsArrayByPostID( $arrArgs['id'] );
		
	}
	protected function getTweetsAsArrayByPostID( $vPostIDs, $intMaxCount=null ) {	
		
		$arrTweets = array();
		foreach( ( array ) $vPostIDs as $intPostID ) {
			
			$strTweetType = get_post_meta( $intPostID, 'tweet_type', true );
			$intCount = get_post_meta( $intPostID, 'item_count', true );
			$fIncludeRetweets = get_post_meta( $intPostID, 'include_retweets', true );
			$intCacheDuration = get_post_meta( $intPostID, 'cache_duration', true );
			
			switch ( $strTweetType ) {
				case 'search':
					$strKeyword = get_post_meta( $intPostID, 'search_keyword', true );			
					$strResultType = get_post_meta( $intPostID, 'result_type', true );	
					$strLang = get_post_meta( $intPostID, 'language', true );				
					$arrRetrievedTweets = $this->getTweetsBySearch( $strKeyword, $intCount, $strLang, $fIncludeRetweets, $strResultType, $intCacheDuration );
					break;
				case 'list':
					$intListID = get_post_meta( $intPostID, 'list_id', true );						
					$arrRetrievedTweets = $this->getTweetsByListID( $intListID, $intCount, $fIncludeRetweets, $intCacheDuration );
					break;
				case 'screen_name':
				default:	
					$strUser = get_post_meta( $intPostID, 'screen_name', true );			
					$fExcludeReplies = get_post_meta( $intPostID, 'exclude_replies', true );	
					$arrRetrievedTweets = $this->getTweetsByScreenName( $strUser, $intCount, $fIncludeRetweets, $fExcludeReplies, $intCacheDuration );
					break;				
			}	
			
			$arrTweets = array_merge( $arrRetrievedTweets, $arrTweets );
			
			// Truncate the array.
			// if ( $intCount && is_numeric( $intCount ) ) 
				// array_splice( $arrTweets, $intCount );
				
		}
		
		return $arrTweets;
		
	}
	protected function formatTweetArrays( & $arrTweets, $intProfileImageSize, $fTwitterMedia=true, $fExternalMedia=true ) {
		
		foreach( $arrTweets as $intIndex => &$arrTweet ) {
							
			// Check if it is a re-tweet.
			if ( isset( $arrTweet['retweeted_status']['text'] ) ) 				
				$arrTweet['retweeted_status'] = $this->formatTweetArray( $arrTweet['retweeted_status'], $intProfileImageSize, $fTwitterMedia, $fExternalMedia );
			
			$arrTweet = $this->formatTweetArray( $arrTweet, $intProfileImageSize, $fTwitterMedia, $fExternalMedia );
						
		}
		
	}
	protected function formatTweetArray( $arrTweet, $intProfileImageSize=48, $fTwitterMedia=true, $fExternalMedia=true ) {
		
		// Avoid undefined index warnings.
		$arrTweet = $arrTweet + array( 
			'text' => null,
			'created_at' => null,
			'entities' => null,
			'user' => null,
		);	
		$arrTweet['entities'] = $arrTweet['entities'] + array(
			'hashtags' => null,
			'symbols' => null,
			'urls' => null,
			'user_mentions' => null,
			'media'	=> null,
		);
		$arrTweet['user'] = $arrTweet['user'] + array(
			'profile_image_url' => null, 		
			'profile_image_url_https' => null,
		);
				
		// Make the urls in the text hyper-links.
		$arrTweet['text'] = $this->makeClickableLinks( $arrTweet['text'], $arrTweet['entities']['urls'], $fExternalMedia );
		$arrTweet['text'] = $this->makeClickableMedia( $arrTweet['text'], $arrTweet['entities']['media'] );	
		$arrTweet['text'] = $this->makeClickableHashTags( $arrTweet['text'], $arrTweet['entities']['hashtags'] );	
		$arrTweet['text'] = $this->makeClickableUsers( $arrTweet['text'], $arrTweet['entities']['user_mentions'] );
		
		// Insert twitter media files at the bottom of the tweet. For external media files, the above makeClickableLinks() method will take care of it.
		if ( $fTwitterMedia ) 
			$arrTweet['text'] = $this->appendTwitterMedia( $arrTweet['text'], $arrTweet['entities']['media'] );
					
		// Adjust the profile image size.
		$arrTweet['user']['profile_image_url'] = $this->adjustProfileImageSize( $arrTweet['user']['profile_image_url'], $intProfileImageSize );
		$arrTweet['user']['profile_image_url_https'] = $this->adjustProfileImageSize( $arrTweet['user']['profile_image_url_https'], $intProfileImageSize );

		// Convert the 'created_at' value to be numeric time.
		$arrTweet['created_at'] = strtotime( $arrTweet['created_at'] );		
		
		return $arrTweet;
		
	}
	
	protected function sortTweetArrays( & $arrTweets, $strOrderedBy='descending' ) {
		switch( strtolower( $strOrderedBy ) ) {
			case 'ascending':
				uasort( $arrTweets, array( $this, 'sortByTimeAscending' ) );
				break;
			case 'random':
				shuffle( $arrTweets );
			case 'descending':
			default:
				uasort( $arrTweets, array( $this, 'sortByTimeDescending' ) );
				break;	
		}
	}	
	public function sortByTimeDescending( $a, $b ) {	// callback for the uasort() method.
		return ( int ) $b['created_at'] - ( int ) $a['created_at'];
	}			
	public function sortByTimeAscending( $a, $b ) {	// callback for the uasort() method.
		return ( int ) $a['created_at'] - ( int ) $b['created_at'];
	}		
	
	protected function getTweetsBySearch( $strKeyword, $intCount, $strLang='en', $fIncludeRetweets=false, $strResultType='mixed', $intCacheDuration=600 ) {

		// Compose the request URI.
		$fIncludeEntities = true;
		$intCount = ( ( int ) $intCount ) > 100 ? 100 : $intCount;
		$strRequestURI = "https://api.twitter.com/1.1/search/tweets.json"
			. "?q=" . urlencode_deep( $strKeyword )
			. "&result_type={$strResultType}"	//  mixed, recent, popular
			. "&count={$intCount}"
			. ( $strLang == 'none' ? "" : "&lang={$strLang}" )
			. "&include_rts=" . ( $fIncludeRetweets ? 1 : 0 )
			. "&include_entities=" . ( $fIncludeEntities ? 1 : 0 );
						
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
		$intCount = ( ( int ) $intCount ) > 200 ? 200 : $intCount;
			
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
	 * Fetches tweets by screen name.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
	 * @since			1.0.0
	 */ 
	protected function getTweetsByScreenName( $strUser, $intCount, $fIncludeRetweets=false, $fExcludeReplies=false, $intCacheDuration=1200 ) {
		
		// Compose the request URI.
		$intCount = ( ( int ) $intCount ) > 200 ? 200 : $intCount;
		$strRequestURI = "https://api.twitter.com/1.1/statuses/user_timeline.json"
			. "?screen_name={$strUser}"
			. "&count={$intCount}"
			. "&include_rts=" . ( $fIncludeRetweets ? 1 : 0 )
			. "&exclude_replies=" . ( $fExcludeReplies ? 1 : 0 );
		
		return $this->doAPIRequest_Get( $strRequestURI, null, $intCacheDuration );
				
	}
	
	
	/**
	 * Performs the Twitter API request by the given URI.
	 * 
	 * This checks the existent caches and if it's not expired it uses the cache.
	 * 
	 * @since			1.2.0
	 * @access			protected
	 * @param			string			$strRequestURI				The GET request URI with the query.
	 * @param			string			$strArrayKey				The key name of the result tweet array. The search results holds the tweets in the "status" array; in that case "status" needs to be passed.
	 * @param			integer			$intCacheDuration			The cache duration in seconds.
	 * @return			array
	 */ 
	protected function doAPIRequest_Get( $strRequestURI, $strArrayKey=null, $intCacheDuration=600 ) {
	
		// Create an ID from the URI.
		$strRequestID = FetchTweets_Commons::TransientPrefix . "_" . md5( trim( $strRequestURI ) );

		// Retrieve the cache, and if there is, use it.
		$arrTransient = $this->getTransient( $strRequestID );
		if ( 
			$arrTransient !== false 
			&& is_array( $arrTransient ) 
			&& isset( $arrTransient['mod'], $arrTransient['data'] )
		) {
			
			// Check the cache expiration.
			if ( ( $arrTransient['mod'] + ( ( int ) $intCacheDuration ) ) < time() ) 	// expired
				$this->arrExpiredTransientsRequestURIs[] = array( 
					// these keys will be checked in the cache renewal events.
					'URI'	=> $strRequestURI, 	
					'key'	=> $strArrayKey,
				);
		
			return ( array ) $this->oBase64->decode( $arrTransient['data'] );
			
		}

		return $this->setAPIGETRequestCache( $strRequestURI, $strArrayKey );
		
	}	
	
	/**
	 * Performs the API request and sets the cache.
	 * 
	 * @access			public
	 * @remark			The scope is public since the cache renewal event also uses it.
	 */
	public function setAPIGETRequestCache( $strRequestURI, $strArrayKey=null, $strRequestID='' ) {
		
		// Perform the API request.
		$arrTweets =  $this->oTwitterOAuth->get( $strRequestURI );		
			
		// If the array key is specified, return the contents of the key element. Otherwise, return the retrieved array intact.
		if ( ! is_null( $strArrayKey ) && isset( $arrTweets[ $strArrayKey ] ) )
			$arrTweets = $arrTweets[ $strArrayKey ];

// Debug
// FetchTweets_Debug::getArray( $arrTweets, dirname( __FILE__ ) . '/cache_renewed.txt' );
			
		// If empty, return an ampty array.
		if ( empty( $arrTweets ) ) return array();
			
		// If an error occurs, do not set the cache.
		if ( isset( $arrTweets['errors'][ 0 ]['message'], $arrTweets['errors'][ 0 ]['code'] ) ) {
			$arrTweets['errors'][ 0 ]['message'] .= "<!-- Request URI: {$strRequestURI} -->";	
			return ( array ) $arrTweets;
		}
			
		// Save the cache
		$strRequestID = empty( $strRequestID ) 
			? FetchTweets_Commons::TransientPrefix . "_" . md5( trim( $strRequestURI ) )
			: $strRequestID;
		$this->setTransient( $strRequestID, $arrTweets );

		return ( array ) $arrTweets;
		
	}
	
	/**
	 * A wrapper method for the set_transient() function.
	 * 
	 * @since			1.2.0
	 */
	protected function setTransient( $strTransientKey, $vData ) {
		
		set_transient(
			$strTransientKey, 
			array( 'mod' => time(), 'data' => $this->oBase64->encode( $vData ) ), 
			9999999999 // this barely expires by itself. $intCacheDuration 
		);
		
	}
	
	/**
	 * A wrapper method for the get_transient() function.
	 * 
	 * This method does retrieves the transient with the given transient key. In addition, it checks if it is an array; otherwise, it makes it an array.
	 * 
	 * @since			1.2.0
	 */ 
	protected function getTransient( $strTransientKey, $fForceArray=true ) {
		
		$vData = get_transient( $strTransientKey );
		
		// if it's false, no transient is stored. Otherwise, some values are in there.
		if ( $vData === false ) return false;
							
		// If it does not have to be an array. Return the raw result.
		if ( ! $fForceArray ) return $vData;
		
		// If it's array, okay.
		if ( is_array( $vData ) ) return $vData;
		
		
		// Maybe it's encoded
		if ( is_string( $vData ) && is_serialized( $vData ) ) 
			return unserialize( $vData );
		
			
		// Maybe it's an object. In that case, convert it to an associative array.
		if ( is_object( $vData ) )
			return get_object_vars( $vData );
			
		// It's an unknown type. So cast array and return it.
		return ( array ) $vData;
			
	}
	
	protected function makeClickableLinks( $strText, $arrURLs, $fExternalMedia=true ) {
				
		// There are urls in the tweet text. So they need to be converted into hyper links.
		foreach( ( array ) $arrURLs as $arrURLDetails ) {
			
			$arrURLDetails = $arrURLDetails + array(	// avoid undefined index warnings.
				'url' => null,
				'expanded_url' => null,
				'display_url' => null,
			);
			
			
			$strEmbeddedElement = $fExternalMedia ? $this->oEmbed->get_html( $arrURLDetails['expanded_url'], array( 'discover' => false, ) ) : '';
				
			$strText = str_replace( 
				$arrURLDetails['url'],	// needle 
				$strEmbeddedElement
					? "<div class='fetch-tweets-external-media'>{$strEmbeddedElement}</div>"
					: "<a href='{$arrURLDetails['expanded_url']}' target='_blank' rel='nofollow'>{$arrURLDetails['display_url']}</a>", 	// replace
				$strText 	// haystack
			);	
		}
		return $strText;
		
	}
	protected function makeClickableMedia( $strText, $arrMedia ) {
		
		// This method converts media links in the tweet text.
		foreach( ( array ) $arrMedia as $arrDetails ) {
			
			$arrDetails = $arrDetails + array(	// avoid undefined index warnings.
				'media_url' => null,
				'media_url_https' => null,
				'url' => null,
				'display_url' => null,
				'expanded_url' => null,
				'type' => null,
				'sizes' => null,	// array()
				'id' => null,
				'id_str' => null,
				'indices' => null,	// array()
			);
			
			$strText = str_replace( 
				$arrDetails['url'],	// needle 
				"<a href='{$arrDetails['expanded_url']}' target='_blank' rel='nofollow'>{$arrDetails['display_url']}</a>", 	// replace
				$strText 	// haystack
			);	
		}
		return $strText;
		
	}
	protected function makeClickableHashTags( $strText, $arrHashTags ) {
		
		// There are urls in the tweet text. So we need to convert them into hyper links.
		foreach( ( array ) $arrHashTags as $arrDetails ) {
			
			$arrDetails = $arrDetails + array(	// avoid undefined index warnings.
				'text' => null,
				'indices' => null,
			);
			
			$strText = preg_replace( 
				'/#(\Q' . $arrDetails['text'] . '\E)(\W|$)/', 	// needle
				'<a href="https://twitter.com/search?q=%23$1&src=hash" target="_blank" rel="nofollow">#$1</a>$2',	// replacement
				$strText 	// haystack
			);
			
		}
		return $strText;
		
	}
	protected function makeClickableUsers( $strText, $arrMentions ) {
		
		// There are urls in the tweet text. So they need to be converted into hyper links.
		foreach( ( array ) $arrMentions as $arrDetails ) {
			
			$arrDetails = $arrDetails + array(	// avoid undefined index warnings.
				'screen_name' => null,
				'name' => null,
				'id' => null, 
				'id_str' => null,
				'indices' => null,
			);
			
			$strText = preg_replace( 
				'/@(\Q' . $arrDetails['screen_name'] . '\E)(\W|$)/i', 	// needle, case insensitive
				'<a href="https://twitter.com/$1" target="_blank" rel="nofollow">@$1</a>$2',	// replacement
				$strText 	// haystack
			);
			
		}
		return $strText;
		
	}
	protected function makeClickableLinksByRegex( $strText ) {	
		// since current format contains the entities element, this method is not used. However, at later some point, this may be used for other occasions.
		return preg_replace( '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@' , '<a href="$1" target="_blank">$1</a>', $strText );
	}	
	protected function makeClickableUsersByRegex( $strText ) {
		return preg_replace( '/@(\w+?)(\W|$)/', '<a href="https://twitter.com/$1" target="_blank">@$1</a>$2', $strText );
	}
	protected function makeClickableHashTagByRegex( $strText ) {
		// e.g. https://twitter.com/search?q=%23PHP&src=hash
		return preg_replace( '/#(\w+?)(\W|$)/', '<a href="https://twitter.com/search?q=%23$1&src=hash" target="_blank">#$1</a>$2', $strText );
	}		
	protected function adjustProfileImageSize( $strURL, $intImageSize ) {
		
		// reference: https://dev.twitter.com/docs/user-profile-images-and-banners
		// url example: 
		// http://a0.twimg.com/profile_images/.../..._normal.jpeg
		// https://si0.twimg.com/profile_images/../..._normal.jpeg		
		
		if ( empty( $strURL ) ) return $strURL;
		
		$intImageSize = ! is_numeric( $intImageSize ) ? 48 : $intImageSize;
		
		$strNeedle = '/\/.+\K(_normal)(?=(\..+$)|$)/';
		if ( $intImageSize <= 24 )
			return preg_replace( $strNeedle, '_mini', $strURL );
		if ( $intImageSize <= 48 )
			return $strURL;
		if ( $intImageSize <= 73 )
			return preg_replace( $strNeedle, '_bigger', $strURL );
		return preg_replace( $strNeedle, '', $strURL );	// the original picture size.
		
	}
	
	/**
	 * Appends the Twitter media files to the tweet text.
	 * 
	 * @remark			Currently only photos are supported.
	 * @since			1.2.0
	 */ 
	protected function appendTwitterMedia( $strTweet, $arrMedia ) {
		
		$arrOutput = array();
		foreach( ( array ) $arrMedia as $arrMedium ) {
			
			// avoid undefined index warnings.
			$arrMedium = $arrMedium + array(
				'type' => null,
				'expanded_url' => null,
				'media_url' => null,			
			);
			
			if ( $arrMedium['type'] != 'photo' || ! $arrMedium['media_url'] ) continue;
			
			$arrOutput[] = "<div class='fetch-tweets-media-photo'><a href='{$arrMedium['expanded_url']}'><img src='{$arrMedium['media_url']}'></a></div>";
		
		}
		return $strTweet 
			. ( empty( $arrOutput ) 
				? ''
				: "<div class='fetch-tweets-media'>" 
					. implode( PHP_EOL, $arrOutput ) 
				. "</div>" 
			);
		
	}
	
	/*
	 * Callbacks
	 * */
	public function updateCacheItems() {	// for the shutdown hook
		
		if ( empty( $this->arrExpiredTransientsRequestURIs ) ) return;
		
		// Perform multi-dimensional array_unique()
		$this->arrExpiredTransientsRequestURIs = array_map( "unserialize", array_unique( array_map( "serialize", $this->arrExpiredTransientsRequestURIs ) ) );
		
		foreach( $this->arrExpiredTransientsRequestURIs as $arrExpiredCacheRequest ) {
			
			/* the structure of $arrExpiredCacheRequest = array(
				'URI'	=> the API request URI
				'key'	=> the array key that holds the result. e.g. for search results, the 'statuses' key holds the fetched tweets.
			*/
			
			// Check if the URI key holds a valid url.
			if ( ! filter_var( $arrExpiredCacheRequest['URI'], FILTER_VALIDATE_URL ) ) continue;
			
			// Schedules the action to run in the background with WP Cron.
			// If already scheduled, skip.
			if ( wp_next_scheduled( 'fetch_tweets_action_transient_renewal', $arrExpiredCacheRequest ) ) continue; 
			
			wp_schedule_single_event( 
				time(), 
				'fetch_tweets_action_transient_renewal', 	// the FetchTweets_Event class will check this action hook and executes it with WP Cron.
				array( $arrExpiredCacheRequest )	// must be enclosed in an array.
			);	
			
		}
				
	}
}