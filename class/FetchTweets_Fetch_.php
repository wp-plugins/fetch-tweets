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
		$oOption = & $GLOBALS['oFetchTweets_Option'];
		$this->strConsumerKey = $oOption->getConsumerKey();
		$this->strConsumerSecret = $oOption->getConsumerSecret();
		$this->strAccessToken = $oOption->getAccessToken();
		$this->strAccessTokenSecret = $oOption->getAccessTokenSecret();
		
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
	
	public function getTweetsByTag( $strTag, $intTotalCount=null, $strOrderedBy=null ) {
		
		// Capture the output buffer
		ob_start(); // start buffer
		$this->drawTweetsByTag( $strTag, $intTotalCount, $strOrderedBy );
		$strContent = ob_get_contents(); // assign the content buffer to a variable
		ob_end_clean(); // end buffer and remove the buffer		
		return $strContent;
		
	}	
	public function drawTweetsByTag( $strTag, $intTotalCount=null, $strOrderedBy=null  ) {
		
		$this->drawTweets( 
			$this->getPostIDsByTag( $strTag ), 
			$intTotalCount, 
			$strOrderedBy,
			$strTag			
		);
			
	}
	private function getPostIDsByTag( $strTermName ) {
		
		$arrTerm = get_term_by( 'name', $strTermName, FetchTweets_Commons::Tag, ARRAY_A );
		$arrPostObjects = get_posts( 
			array(
				'post_type' => FetchTweets_Commons::PostTypeSlug,	// fetch_tweets
				'tax_query' => array(
					array(
						'taxonomy' => FetchTweets_Commons::Tag,	// fetch_tweets_tag
						'field' => 'slug',
						'terms' => $arrTerm['slug'],
					)
				)
			)
		);
		$arrIDs = array();
		foreach( $arrPostObjects as $oPost )
			$arrIDs[] = $oPost->ID;
		return $arrIDs;
		
	}
	
	public function getTweets( $vPostIDs, $intTotalCount=null, $strOrderedBy=null ) {
		
		// Capture the output buffer
		ob_start(); // start buffer
		$this->drawTweets( $vPostIDs, $intTotalCount, $strOrderedBy );
		$strContent = ob_get_contents(); // assign the content buffer to a variable
		ob_end_clean(); // end buffer and remove the buffer		
		return $strContent;
		
	}
	public function drawTweets( $vPostIDs, $intTotalCount=null, $strOrderedBy=null, $strTag=null ) {
		
		/*
		 * $strTag is only used to pass it to the template path filter to tell it it is a call of a tag.
		 * */
		
		$arrTweets = array();
		foreach( ( array ) $vPostIDs as $intPostID ) {
			
			$strTweetType = get_post_meta( $intPostID, 'tweet_type', true );
			$intCount = get_post_meta( $intPostID, 'item_count', true );
			// $strTemplatePath = get_post_meta( $intPostID, 'template_path', true );
			$fIncludeRetweets = get_post_meta( $intPostID, 'include_retweets', true );
			$intCacheDuration = get_post_meta( $intPostID, 'cache_duration', true );
			
			switch ( $strTweetType ) {
				case 'search':
					$strKeyword = get_post_meta( $intPostID, 'search_keyword', true );			
					$strResultType = get_post_meta( $intPostID, 'result_type', true );	
					$strLang = get_post_meta( $intPostID, 'language', true );
	// echo "keyword: " . urlencode_deep( $strKeyword ) . "<br />";				
	// echo "count: {$intCount} <br />";				
	// echo "lang: {$strLang} <br />";				
	// echo "fIncludeRetweets: {$fIncludeRetweets} <br />";				
	// echo "strResultType: {$strResultType} <br />";	
				
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
		
		if ( empty( $arrTweets ) ) {
			_e( 'No result fetched.', 'fetch-tweets' );
			return;
		}

		// Format the array.
		foreach( $arrTweets as $intIndex => &$arrTweet ) {
							
			// Check if it is a retweet.
			if ( isset( $arrTweet['retweeted_status']['text'] ) ) 				
				$arrTweet['retweeted_status'] = $this->formatTweetArray( $arrTweet['retweeted_status'] );
			
			$arrTweet = $this->formatTweetArray( $arrTweet );
						
		}
		
		// Sort by time.
		switch( strtolower( $strOrderedBy ) ) {
			case 'ascending':
				uasort( $arrTweets, array( $this, 'sortByTimeAscending' ) );
				break;
			case 'random':
				shuffle( $arrTweets );
			case 'desc':
			default:
				uasort( $arrTweets, array( $this, 'sortByTimeDescending' ) );
				break;	
		}

		// Truncate the array.
		if ( $intTotalCount && is_numeric( $intTotalCount ) ) 
			array_splice( $arrTweets, $intTotalCount );
		
		// For debug
		// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";		
		// return;
		
		// Include the template to render the output - this function is for filters but go ahead and render the output.
		include( apply_filters( "fetch_tweets_template_path", $this->strTemplatePath, isset( $strTag ) ? $strTag : $vPostIDs ) );
		
	}
	private function formatTweetArray( $arrTweet ) {
		
		$arrTweet = $arrTweet + array( 
			'text' => null,
			'created_at' => null,
		);	
		
		// Make the urls in the text hyper-links.
		$arrTweet['text'] = $this->makeClickableLinks( $arrTweet['text'] );
		$arrTweet['text'] = $this->makeClickableUsers( $arrTweet['text'] );	
		$arrTweet['text'] = $this->makeClickableHashTag( $arrTweet['text'] );	
						
		// Convert the 'created_at' value to be numeric time.
		$arrTweet['created_at'] = strtotime( $arrTweet['created_at'] );		
		
		return $arrTweet;
		
	}
	public function sortByTimeDescending ( $a, $b ) {	// callback for the uasort() method.
		return $b['created_at'] - $a['created_at'];
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
			
// echo "URL: {$strRequestURI}<br />";			
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
			if ( $arrTransient['mod'] + $intCacheDuration < time() ) {	// expired
				$this->arrExpiredTransientsRequestURIs[] = array( 
					'URI'	=> $strRequestURI, 
					'type'	=> 'search',
				);
// $oDebug	= new FetchTweets_Debug;
// $oDebug->getArray( $strRequestURI, dirname( __FILE__ ) . '/expired_request.txt' );
			}
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
		$intCount = $intCount > 100 ? 100 : $intCount;
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
			if ( $arrTransient['mod'] + $intCacheDuration < time() ) {	// expired
				$this->arrExpiredTransientsRequestURIs[] = array( 
					'URI'	=> $strRequestURI, 
					'type'	=> 'screen_name',
				);
// $oDebug	= new FetchTweets_Debug;
// $oDebug->getArray( $strRequestURI, dirname( __FILE__ ) . '/expired_request.txt' );

			}				
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
	private function makeClickableLinks( $strText ) {
		return preg_replace( '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@' , '<a href="$1" target="_blank">$1</a>', $strText );
	}	
	private function makeClickableUsers( $strText ) {
		return preg_replace( '/@(\w+?)(\W|$)/', '<a href="https://twitter.com/$1" target="_blank">@$1</a>$2', $strText );
	}
	private function makeClickableHashTag( $strText ) {
		// e.g. https://twitter.com/search?q=%23PHP&src=hash
		return preg_replace( '/#(\w+?)(\W|$)/', '<a href="https://twitter.com/search?q=%23$1&src=hash" target="_blank">#$1</a>$2', $strText );
	}		
	
	/*
	 * Callbacks
	 * */
	public function updateCacheItems() {	// for the shutdown hook
		
		if ( empty( $this->arrExpiredTransientsRequestURIs ) ) return;
		
		// multi-dimensional array_unique
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