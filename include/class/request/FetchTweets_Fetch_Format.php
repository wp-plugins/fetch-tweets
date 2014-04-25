<?php
/**
 * Formats fetched tweets data.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			1.3.4
 */
abstract class FetchTweets_Fetch_Format extends FetchTweets_Fetch_APIRequest {
	
	public function __construct() {
		
		// Properties
		$this->fIsSSL = is_ssl();		
		
		// Objects
		$this->oEmbed = new FetchTweets_oEmbed;
				
		parent::__construct();
		
	}
	
	/**
	 * Sorts tweet array elements.
	 * 
	 */
	protected function _sortTweetArrays( & $arrTweets, $strOrderedBy='descending' ) {
		switch( strtolower( $strOrderedBy ) ) {
			case 'ascending':
				uasort( $arrTweets, array( $this, '_sortByTimeAscending' ) );
				break;
			case 'random':
				shuffle( $arrTweets );
			case 'descending':
			default:
				uasort( $arrTweets, array( $this, '_sortByTimeDescending' ) );
				break;	
		}
	}	
		public function _sortByTimeDescending( $a, $b ) {	// callback for the uasort() method.
			return ( int ) $b['created_at'] - ( int ) $a['created_at'];
		}			
		public function _sortByTimeAscending( $a, $b ) {	// callback for the uasort() method.
			return ( int ) $a['created_at'] - ( int ) $b['created_at'];
		}			
	
	/**
	 * Formats the tweets.
	 * 
	 * @since			1.x
	 * @since			1.3.3			Added the ability to eliminate duplicated items for mash up results.
	 */
	protected function _formatTweetArrays( & $arrTweets, $intProfileImageSize, $fTwitterMedia=true, $fExternalMedia=true ) {
		
		$arrTweetIDs = array();
		
		foreach( $arrTweets as $intIndex => &$arrTweet ) {
			
			if ( ! is_array( $arrTweet ) ) continue;
			
			if ( in_array( $arrTweet[ 'id_str' ], $arrTweetIDs ) ) continue;
			$arrTweetIDs[] = $arrTweet[ 'id_str' ];
										
			// Check if it is a re-tweet.
			if ( isset( $arrTweet['retweeted_status']['text'] ) ) 				
				$arrTweet['retweeted_status'] = $this->formatTweetArray( $arrTweet['retweeted_status'], $intProfileImageSize, $fTwitterMedia, $fExternalMedia );
			
			$arrTweet = $this->formatTweetArray( $arrTweet, $intProfileImageSize, $fTwitterMedia, $fExternalMedia );
						
		}
		
	}
	
		/**
		 * 
		 * @remark			The profile image size won't be passed unless the call is made from a widget or shortcode with direct argument.
		 * In other words, for preview pages, the profile image url needs to be taken cared of separately.
		 */
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
			$arrTweet['text'] = $this->makeClickableLinks( $arrTweet['text'], $arrTweet['entities']['urls'] );
			$arrTweet['text'] = $this->makeClickableMedia( $arrTweet['text'], $arrTweet['entities']['media'] );	
			$arrTweet['text'] = $this->makeClickableHashTags( $arrTweet['text'], $arrTweet['entities']['hashtags'] );	
			$arrTweet['text'] = $this->makeClickableUsers( $arrTweet['text'], $arrTweet['entities']['user_mentions'] );
			
			// Insert external media files at the bottom of the tweet.
			if ( $fExternalMedia )
				$arrTweet['text'] .= isset( $arrTweet['entities']['embed_external_media'] )
					? $arrTweet['entities']['embed_external_media']
					: $this->getExternalMedia( $arrTweet['entities']['urls'] );
				
			// Insert twitter media files at the bottom of the tweet. 
			if ( $fTwitterMedia ) 
				$arrTweet['text'] .= isset( $arrTweet['entities']['embed_twitter_media'] )
					? $arrTweet['entities']['embed_twitter_media']
					: $this->getTwitterMedia( $arrTweet['entities']['media'] );
						
			// Adjust the profile image size.
			$arrTweet['user']['profile_image_url'] = $this->adjustProfileImageSize( $arrTweet['user']['profile_image_url'], $intProfileImageSize );
			$arrTweet['user']['profile_image_url_https'] = $this->adjustProfileImageSize( $arrTweet['user']['profile_image_url_https'], $intProfileImageSize );

			// Convert the 'created_at' value to be numeric time.
			$arrTweet['created_at'] = strtotime( $arrTweet['created_at'] );		
			
			return $arrTweet;
			
		}
		
	protected function makeClickableLinks( $strText, $arrURLs ) {
				
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
	/**
	 * 
	 * url format example: 
	 * 	http://a0.twimg.com/profile_images/.../..._normal.jpeg
	 * 	https://si0.twimg.com/profile_images/../..._normal.jpeg		
	 * @see			https://dev.twitter.com/docs/user-profile-images-and-banners
	 */
	protected function adjustProfileImageSize( $strURL, $intImageSize ) {

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
	 * Returns the external media files to the tweet text.
	 * 
	 * @remark			The supported providers depend on the WordPress oEmbed class. It has a filter for the providers so it can be customized.
	 * @since			1.2.0
	 */ 
	protected function getExternalMedia( $arrURLs ) {

		// There are urls in the tweet text. So they need to be converted into hyper links.
		$arrOutput = array();
		foreach( ( array ) $arrURLs as $arrURLDetails ) {
			
			$arrURLDetails = $arrURLDetails + array(	// avoid undefined index warnings.
				'url' => null,
				'expanded_url' => null,
				'display_url' => null,
			);

			if ( empty( $arrURLDetails['expanded_url'] ) ) continue;
			
			$strEmbed = $this->oEmbed->get_html( $arrURLDetails['expanded_url'], array( 'discover' => false, ) );
			if ( empty( $strEmbed ) ) continue;
			
			$arrOutput[] = "<div class='fetch-tweets-external-media'>"
				. $strEmbed
				. "</div>";

		}
		return implode( PHP_EOL, $arrOutput );
	
	}
	
	/**
	 * Returns the Twitter media files to the tweet text.
	 * 
	 * @remark			Currently only photos are supported.
	 * @since			1.2.0
	 */ 
	protected function getTwitterMedia( $arrMedia ) {
		
		$arrOutput = array();
		foreach( ( array ) $arrMedia as $arrMedium ) {
			
			// avoid undefined index warnings.
			$arrMedium = $arrMedium + array(
				'type' => null,
				'expanded_url' => null,
				'media_url' => null,		
				'media_url_https' => null,				
			);
			
			if ( $arrMedium['type'] != 'photo' || ! $arrMedium['media_url'] ) continue;
			
			$arrOutput[] = "<div class='fetch-tweets-media-photo'>"
					. "<a href='{$arrMedium['expanded_url']}'>"
						. "<img src='" . ( $this->fIsSSL ? $arrMedium['media_url_https'] : $arrMedium['media_url'] ) . "'>"
					. "</a>"
				. "</div>";
		
		}
		return ( empty( $arrOutput ) 
				? ''
				: "<div class='fetch-tweets-media'>" 
					. implode( PHP_EOL, $arrOutput ) 
				. "</div>" 
			);
		
	}
		
		
	/**
	 * Adds the embeddable media elements to the tweets array.
	 * 
	 * @remark			This should be called from an action event which runs in the background because this takes some time.
	 * @since			1.3.0
	 */
	public function addEmbeddableMediaElements( &$arrTweets ) {

		foreach( $arrTweets as $intIndex => &$arrTweet ) {
							
			if ( isset( $arrTweet['retweeted_status']['text'] ) ) 	// Check if it is a re-tweet.
				$arrTweet['retweeted_status'] = $this->_addEmbeddableMediaElement( $arrTweet['retweeted_status'] );
			
			$arrTweet = $this->_addEmbeddableMediaElement( $arrTweet );
						
		}					
	
	}
		/**
		 * Adds the embeddable media element to the single tweet element.
		 * 
		 * This is a helper method for the above addEmbeddableMediaElements() method.
		 * 
		 * @since			1.3.0
		 * @remark			The element with the keys 'embed_external_media' and 'embed_twitter_media' will be inserted into the 'entities' key element.
		 * @return			array			The modified tweet element array.
		 */
		protected function _addEmbeddableMediaElement( $arrTweet ) {
			
			if ( isset( $arrTweet['entities']['urls'] ) ) 
				$arrTweet['entities']['embed_external_media'] = $this->getExternalMedia( $arrTweet['entities']['urls'] );
							
			if ( isset( $arrTweet['entities']['media'] ) ) 
				$arrTweet['entities']['embed_twitter_media'] = $this->getTwitterMedia( $arrTweet['entities']['media'] );
			
			return $arrTweet;
			
		}
		
}