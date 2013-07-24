<?php
/*
 * 
 * @filters fetch_tweets_template_path - specifies the template path.
 * @actions: FTWS_action_transient_renewal - for WP Cron single event.
 * */
abstract class FetchTweets_Fetch_ {

	protected $arrExpiredTransientsRequestURIs = array(); // stores the expired transients' request URIs
	
	public function __construct() {
	
		// Set up the connection.
		$this->oOption = & $GLOBALS['oFetchTweets_Option'];
		$this->strConsumerKey = $this->oOption->getConsumerKey();
		$this->strConsumerSecret = $this->oOption->getConsumerSecret();
		$this->strAccessToken = $this->oOption->getAccessToken();
		$this->strAccessTokenSecret = $this->oOption->getAccessTokenSecret();
		
		$this->oTwitterOAuth =  new WP_TwitterOAuth( 
			$this->strConsumerKey, 
			$this->strConsumerSecret, 
			$this->strAccessToken, 
			$this->strAccessTokenSecret 
		);		
		
		// Set the template path.
		$strTemplatePathInThemeDir = get_template_directory() . '/fetch-tweets/show_tweets.php';
		$this->strTemplatePath = file_exists( $strTemplatePathInThemeDir ) 
			? $strTemplatePathInThemeDir 
			: dirname( FetchTweets_Commons::getPluginFilePath() ) . '/template/show_tweets.php';
		
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
		$arrArgs['tag'] = isset( $arrArgs['tags'] ) && ! empty( $arrArgs['tags'] ) ? $arrArgs['tags'] : $arrArgs['tag'];	// backward compatibility
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
	private function sanitizeFieldKey( $strField ) {
		switch( strtolower( trim( $strField ) ) ) {
			case 'id':
				return 'id';
			default:
			case 'slug':
				return 'slug';
		}		
	}
	private function sanitizeOperator( $strOperator ) {
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
		 * 	id - default: null. e.g. 125  or 124, 235
		 * 	tag - default: null. e.g. php or php, WordPress. In this method this tag is only used to pass the argument to the template filter.
		 *  sort - default: descending. Either ascending, descending, or random can be used.
		 * 	count - default: 20
		 *  avatar_size - default: 48
		 * 	operator - default: AND. Either AND or IN or NOT IN is used.
		 * */
		$arrArgs = ( array ) $arrArgs + $this->oOption->arrStructure_DefaultParams;
		$arrArgs['id'] = isset( $arrArgs['ids'] ) && ! empty( $arrArgs['ids'] ) ? $arrArgs['ids'] : $arrArgs['id'];	// backward compatibility
		$arrArgs['id'] = is_array( $arrArgs['id'] ) ? $arrArgs['id'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['id'] ), 0, PREG_SPLIT_NO_EMPTY );
	
		$arrTweets = $this->getTweetsAsArray( $arrArgs['id'] );
		if ( empty( $arrTweets ) ) {
			_e( 'No result has been fetched.', 'fetch-tweets' );
			return;
		}

		// Format the array.
		$this->formatTweetArrays( $arrTweets, $arrArgs['avatar_size'] ); // the array is passed as reference.
	
		// Sort by time.
		$this->sortTweetArrays( $arrTweets, $arrArgs['sort'] ); // the array is passed as reference.

		// Truncate the array.
		if ( $arrArgs['count'] && is_numeric( $arrArgs['count'] ) ) 
			array_splice( $arrTweets, $arrArgs['count'] );
	
// For debug
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";		
// return;
		
		// Include the template to render the output - this function is for filters( which requires a return value ) but go ahead and render the output.
		include( apply_filters( "fetch_tweets_template_path", $this->strTemplatePath, $arrArgs ) );
		
	}
	public function getTweetsAsArray( $vPostIDs, $intMaxCount=null ) {	// this is public as the feed extension uses it.
		
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
				case 'screen_name':
				default:	
					$strUser = get_post_meta( $intPostID, 'screen_name', true );			
					$fExcludeReplies = get_post_meta( $intPostID, 'exclude_replies', true );	
					$arrRetrievedTweets = $this->getTweetsByScreenName( $strUser, $intCount, $fIncludeRetweets, $fExcludeReplies, $intCacheDuration );
					break;				
			}	
			
			$arrTweets = array_merge( $arrRetrievedTweets, $arrTweets );
		}
		
		return $arrTweets;
		
	}
	private function formatTweetArrays( & $arrTweets, $intProfileImageSize ) {
		
		foreach( $arrTweets as $intIndex => &$arrTweet ) {
							
			// Check if it is a re-tweet.
			if ( isset( $arrTweet['retweeted_status']['text'] ) ) 				
				$arrTweet['retweeted_status'] = $this->formatTweetArray( $arrTweet['retweeted_status'], $intProfileImageSize );
			
			$arrTweet = $this->formatTweetArray( $arrTweet, $intProfileImageSize );
						
		}
		
	}
	private function formatTweetArray( $arrTweet, $intProfileImageSize=48 ) {
		
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
		$arrTweet['text'] = $this->makeClickableLinks( $arrTweet['text'], $arrTweet['entities']['urls'] );	// $arrTweet['text'] = $this->makeClickableLinksByRegex( $arrTweet['text'] );
		$arrTweet['text'] = $this->makeClickableMedia( $arrTweet['text'], $arrTweet['entities']['media'] );	
		$arrTweet['text'] = $this->makeClickableHashTags( $arrTweet['text'], $arrTweet['entities']['hashtags'] );	
		$arrTweet['text'] = $this->makeClickableUsers( $arrTweet['text'], $arrTweet['entities']['user_mentions'] );	
					
		// Adjust the profile image size.
		$arrTweet['user']['profile_image_url'] = $this->adjustProfileImageSize( $arrTweet['user']['profile_image_url'], $intProfileImageSize );
		$arrTweet['user']['profile_image_url_https'] = $this->adjustProfileImageSize( $arrTweet['user']['profile_image_url_https'], $intProfileImageSize );

		// Convert the 'created_at' value to be numeric time.
		$arrTweet['created_at'] = strtotime( $arrTweet['created_at'] );		
		
		return $arrTweet;
		
	}
	
	private function sortTweetArrays( & $arrTweets, $strOrderedBy='descending' ) {
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
	
	private function getTweetsBySearch( $strKeyword, $intCount, $strLang='en', $fIncludeRetweets=false, $strResultType='mixed', $intCacheDuration=600 ) {
		
		// Compose the request URI.
		$fIncludeEntities = true;
		$intCount = $intCount > 100 ? 100 : $intCount;
		$strRequestURI = "https://api.twitter.com/1.1/search/tweets.json"
			. "?q=" . urlencode_deep( $strKeyword )
			. "&result_type={$strResultType}"	//  mixed, recent, popular
			. "&count={$intCount}"
			. ( $strLang == 'none' ? "" : "&lang={$strLang}" )
			. "&include_rts=" . ( $fIncludeRetweets ? 1 : 0 )
			. "&include_entities=" . ( $fIncludeEntities ? 1 : 0 );
				
		// Create an ID from the URI.
		$strRequestID = 'FTWS_' . md5( $strRequestURI );
		
		// Retrieve the cache, and if there is, use it.
		$arrTransient = get_transient( $strRequestID );
		if ( 
			$arrTransient !== false 
			&& is_array( $arrTransient ) 
			&& isset( $arrTransient['mod'], $arrTransient['data'] )
		) {
			
			// Check the cache expiration.
			if ( $arrTransient['mod'] + $intCacheDuration < time() ) 	// expired
				$this->arrExpiredTransientsRequestURIs[] = array( 
					'URI'	=> $strRequestURI, 
					'type'	=> 'search',
				);
			return $arrTransient['data'];
			
		}
		
		// Perform the API request.
		// reference: https://dev.twitter.com/docs/api/1.1/get/search/tweets
		$arrTweets = ( array ) $this->oTwitterOAuth->get( $strRequestURI );
			
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";	
		// Check if an error has occured
		// if ( isset( $arrTweets['errors'][0]['message'] ) )
			// return array(  );
			
		// Return an empty array if the statuses key is not set.
		if ( ! isset( $arrTweets['statuses'] ) ) return array();
		
		// Save the cache
		set_transient(
			$strRequestID, 
			array( 'mod' => time(), 'data' => $arrTweets['statuses'] ), 
			9999999999 // this barely expires by itself. $intCacheDuration 
		);		
		
		// Return the result as array.
		return $arrTweets['statuses'];
			
	}
	private function getTweetsByScreenName( $strUser, $intCount, $fIncludeRetweets=false, $fExcludeReplies=false, $intCacheDuration=600 ) {
		
		// Compose the request URI.
		$intCount = $intCount > 200 ? 200 : $intCount;
		$strRequestURI = "https://api.twitter.com/1.1/statuses/user_timeline.json"
			. "?screen_name={$strUser}"
			. "&count={$intCount}"
			. "&include_rts=" . ( $fIncludeRetweets ? 1 : 0 )
			. "&exclude_replies=" . ( $fExcludeReplies ? 1 : 0 );
		
		// Create an ID from the URI.
		$strRequestID = 'FTWS_' . md5( $strRequestURI );

		// Retrieve the cache, and if there is, use it.
		$arrTransient = get_transient( $strRequestID );
		if ( 
			$arrTransient !== false 
			&& is_array( $arrTransient ) 
			&& isset( $arrTransient['mod'], $arrTransient['data'] )
		) {
			
			// Check the cache expiration.
			if ( $arrTransient['mod'] + $intCacheDuration < time() ) 	// expired
				$this->arrExpiredTransientsRequestURIs[] = array( 
					'URI'	=> $strRequestURI, 
					'type'	=> 'screen_name',
				);
			
			return $arrTransient['data'];
			
		}
		
		// Perform the API request.
		// reference: https://dev.twitter.com/docs/api/1.1/get/search/tweets
		$arrTweets =  $this->oTwitterOAuth->get( $strRequestURI );		
			
		if ( empty( $arrTweets ) ) return array();
			
		// Save the cache
		set_transient(
			$strRequestID, 
			array( 'mod' => time(), 'data' => $arrTweets ), 
			9999999999 // this barely expires by itself. $intCacheDuration 
		);
		
		// reference: https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
		return ( array ) $this->oTwitterOAuth->get( $strRequestURI );
		
	}
	
	private function makeClickableLinks( $strText, $arrURLs ) {
		
		// There are urls in the tweet text. So they need to be converted into hyper links.
		foreach( ( array ) $arrURLs as $arrURLDetails ) {
			
			$arrURLDetails = $arrURLDetails + array(	// avoid undefined index warnings.
				'url' => null,
				'expanded_url' => null,
				'display_url' => null,
			);
			
			$strText = str_replace( 
				$arrURLDetails['url'],	// needle 
				"<a href='{$arrURLDetails['expanded_url']}' target='_blank' rel='nofollow'>{$arrURLDetails['display_url']}</a>", 	// replace
				$strText 	// haystack
			);	
		}
		return $strText;
		
	}
	private function makeClickableMedia( $strText, $arrMedia ) {
		
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
	private function makeClickableHashTags( $strText, $arrHashTags ) {
		
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
	private function makeClickableUsers( $strText, $arrMentions ) {
		
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
	private function makeClickableLinksByRegex( $strText ) {	
		// since current format contains the entities element, this method is not used. However, at later some point, this may be used for other occasions.
		return preg_replace( '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@' , '<a href="$1" target="_blank">$1</a>', $strText );
	}	
	private function makeClickableUsersByRegex( $strText ) {
		return preg_replace( '/@(\w+?)(\W|$)/', '<a href="https://twitter.com/$1" target="_blank">@$1</a>$2', $strText );
	}
	private function makeClickableHashTagByRegex( $strText ) {
		// e.g. https://twitter.com/search?q=%23PHP&src=hash
		return preg_replace( '/#(\w+?)(\W|$)/', '<a href="https://twitter.com/search?q=%23$1&src=hash" target="_blank">#$1</a>$2', $strText );
	}		
	private function adjustProfileImageSize( $strURL, $intImageSize ) {
		
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
	
	/*
	 * Callbacks
	 * */
	public function updateCacheItems() {	// for the shutdown hook
		
		if ( empty( $this->arrExpiredTransientsRequestURIs ) ) return;
		
		// Perform multi-dimensional array_unique()
		$this->arrExpiredTransientsRequestURIs = array_map( "unserialize", array_unique( array_map( "serialize", $this->arrExpiredTransientsRequestURIs ) ) );
				
		// Schedules the action to run in the background with WP Cron.
		if ( wp_next_scheduled( 'FTWS_action_transient_renewal', array( $this->arrExpiredTransientsRequestURIs ) ) ) 
			return;		
		wp_schedule_single_event( 
			time(), 
			'FTWS_action_transient_renewal', 	// the other event class will check this action hook and executes it with WP Cron.
			array( $this->arrExpiredTransientsRequestURIs )
		);
		
	}
}