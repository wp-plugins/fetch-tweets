<?php
class FetchTweets_MetaBox_Options_ extends FetchTweets_AdminPageFramework_MetaBox {
	
	public function setUp() {
		
		switch ( $this->getTweetType() ) {
			case 'search':
				$this->addFieldsForTweetsBySearch();
				break;
			case 'screen_name':
				$this->addFieldsForTweetsByScreenName();
				break;
			case 'list':
				$this->addFieldsForTweetsByList();
				break;
			default:	
				break;				
		}			

		// Common fields among the other field types including search, screen_name, and list.
		$this->addSettingFields(									
			array(
				'strFieldID'		=> 'cache',
				'strTitle'			=> __( 'Cache Duration', 'fetch-tweets' ),
				'strDescription'	=> __( 'The cache lifespan in seconds. For no cache, set 0.', 'fetch-tweets' ) . ' ' . __( 'Default:', 'fetch-tweets' ) . ': 1200',
				'strType'			=> 'number',
				'vDefault'			=> 60 * 20,	// 20 minutes
			),			
			array()
		);
		
	}
	
	private function getTweetType() {

		// If the 'action' query value is edit, search for the meta field value which previously set when it is saved.
		if ( isset( $_GET['action'], $_GET['post'] ) && $_GET['action'] == 'edit' ) 
			return get_post_meta( $_GET['post'], 'tweet_type', true );
	
		// If the GET 'tweet_type' query value is set, use it.
		if ( isset( $_GET['tweet_type'] ) && $_GET['tweet_type'] ) return $_GET['tweet_type'];
		
		// return the default type
		return 'screen_name';
		
	}	
	
	/**
	 * Adds form fields for the options to fetch tweets by screen name to the meta box.
	 * 
	 * @since			1.0.0
	 */ 
	protected function addFieldsForTweetsByScreenName() {
		$this->addSettingFields(
			array(
				'strFieldID'		=> 'tweet_type',
				'strType'			=> 'hidden',
				'vValue'			=> 'screen_name',
			),						
			array(
				'strFieldID'		=> 'screen_name',
				'strTitle'			=> __( 'User Name', 'fetch-tweets' ),
				'strDescription'	=> __( 'The user name (screen name) that is used after the @ mark or the end of the twitter url.', 'fetch-tweets' ) . '',
				'strType'			=> 'text',
			),	
			array(
				'strFieldID'		=> 'item_count',
				'strTitle'			=> __( 'Item Count', 'fetch-tweets' ),
				'strDescription'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 200 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'strType'			=> 'number',
				'vDefault'			=> 20,
				'vMax'				=> 200,				
			),				
			array(
				'strFieldID'		=> 'search_keyword',
				'strType'			=> 'hidden',
			),		
			array(
				'strFieldID'		=> 'language',
				'strType'			=> 'hidden',
			),				
			array (
				'strFieldID'		=> 'result_type',
				'strType'			=> 'hidden',
			),			
			array(
				'strFieldID'		=> 'exclude_replies',
				'strTitle'			=> 'Exclude Replies',
				'strType'			=> 'checkbox',
				'vLabel'			=> __( 'This prevents replies from appearing in the returned timeline.', 'fetch-tweets' ),
			),		
			array(	// since 1.2.0
				'strFieldID'		=> 'list_id',			
				'strType'			=> 'hidden',
			),			
			array(
				'strFieldID'		=> 'include_retweets',
				'strTitle'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'vLabel'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
			),			
			array(	// since 1.3.3
				'strFieldID'		=> 'until',
				'strType'			=> 'hidden',
			),			
			array(	// since 1.3.3
				'strFieldID'		=> 'geocentric_coordinate',
				'strType'			=> 'hidden',
			),
			array(	// since 1.3.3
				'strFieldID'		=> 'geocentric_radius',
				'strType'			=> 'hidden',
			),					
			array()
		);	
	
	}
	
	/**
	 * Adds form fields for the options to fetch tweets by keyword search to the meta box.
	 * 
	 * @since			1.0.0
	 */ 
	protected function addFieldsForTweetsBySearch() {
		
		$this->addSettingFields(		
			array(
				'strFieldID'		=> 'tweet_type',
				'strType'			=> 'hidden',
				'vValue'			=> 'search',
			),			
			array(	// non-used fields must be set as hidden since the callback function will assign a value.
				'strFieldID'		=> 'screen_name',
				'strType'			=> 'hidden',
			),				
			array(
				'strFieldID'		=> 'search_keyword',
				'strTitle'			=> __( 'Search Keyword', 'fetch-tweets' ),
				'strDescription'	=> sprintf( __( 'The keyword to search. For a complex combination of terms and operators, refer to the <strong>Search Operators</strong> section of <a href="%1$s" target="_blank">Using the Twitter Search API</a>.', 'fetch-tweets' ), 'https://dev.twitter.com/docs/using-search' ) 
					. ' e.g. <code>love OR hate</code>, <code>#wordpress</code>',
				'strType'			=> 'text',
			),
			array(
				'strFieldID'		=> 'item_count',
				'strTitle'			=> __( 'Item Count', 'fetch-tweets' ),
				'strDescription'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'strType'			=> 'number',
				'vDefault'			=> 20,
				'vMax'				=> 100,				
			),				
			array(
				'strFieldID'		=> 'language',
				'strTitle'			=> __( 'Language ', 'fetch-tweets' ),
				'strType'			=> 'select',
				'vLabel' => array( 
					'none' => __( 'Any Language', 'fetch-tweets' ),
					'am' => __( 'Amharic (አማርኛ)', 'fetch-tweets' ),
					'ar' => __( 'Arabic (العربية)', 'fetch-tweets' ),
					'bg' => __( 'Bulgarian (Български)', 'fetch-tweets' ),
					'bn' => __( 'Bengali (বাংলা)', 'fetch-tweets' ),
					'bo' => __( 'Tibetan (བོད་སྐད)', 'fetch-tweets' ),
					'chr' => __( 'Cherokee (ᏣᎳᎩ)', 'fetch-tweets' ),
					'da' => __( 'Danish (Dansk)', 'fetch-tweets' ),
					'de' => __( 'German (Deutsch)', 'fetch-tweets' ),
					'dv' => __( 'Maldivian (ދިވެހި)', 'fetch-tweets' ),
					'el' => __( 'Greek (Ελληνικά)', 'fetch-tweets' ),
					'en' => __( 'English (English)', 'fetch-tweets' ),
					'es' => __( 'Spanish (Español)', 'fetch-tweets' ),
					'fa' => __( 'Persian (فارسی)', 'fetch-tweets' ),
					'fi' => __( 'Finnish (Suomi)', 'fetch-tweets' ),
					'fr' => __( 'French (Français)', 'fetch-tweets' ),
					'gu' => __( 'Gujarati (ગુજરાતી)', 'fetch-tweets' ),
					'iw' => __( 'Hebrew (עברית)', 'fetch-tweets' ),
					'hi' => __( 'Hindi (हिंदी)', 'fetch-tweets' ),
					'hu' => __( 'Hungarian (Magyar)', 'fetch-tweets' ),
					'hy' => __( 'Armenian (Հայերեն)', 'fetch-tweets' ),
					'in' => __( 'Indonesian (Bahasa Indonesia)', 'fetch-tweets' ),
					'is' => __( 'Icelandic (Íslenska)', 'fetch-tweets' ),
					'it' => __( 'Italian (Italiano)', 'fetch-tweets' ),
					'iu' => __( 'Inuktitut (ᐃᓄᒃᑎᑐᑦ)', 'fetch-tweets' ),
					'ja' => __( 'Japanese (日本語)', 'fetch-tweets' ),
					'ka' => __( 'Georgian (ქართული)', 'fetch-tweets' ),
					'km' => __( 'Khmer (ខ្មែរ)', 'fetch-tweets' ),
					'kn' => __( 'Kannada (ಕನ್ನಡ)', 'fetch-tweets' ),
					'ko' => __( 'Korean (한국어)', 'fetch-tweets' ),
					'lo' => __( 'Lao (ລາວ)', 'fetch-tweets' ),
					'lt' => __( 'Lithuanian (Lietuvių)', 'fetch-tweets' ),
					'ml' => __( 'Malayalam (മലയാളം)', 'fetch-tweets' ),
					'my' => __( 'Myanmar (မြန်မာဘာသာ)', 'fetch-tweets' ),
					'ne' => __( 'Nepali (नेपाली)', 'fetch-tweets' ),
					'nl' => __( 'Dutch (Nederlands)', 'fetch-tweets' ),
					'no' => __( 'Norwegian (Norsk)', 'fetch-tweets' ),
					'or' => __( 'Oriya (ଓଡ଼ିଆ)', 'fetch-tweets' ),
					'pa' => __( 'Panjabi (ਪੰਜਾਬੀ)', 'fetch-tweets' ),
					'pl' => __( 'Polish (Polski)', 'fetch-tweets' ),
					'pt' => __( 'Portuguese (Português)', 'fetch-tweets' ),
					'ru' => __( 'Russian (Русский)', 'fetch-tweets' ),
					'si' => __( 'Sinhala (සිංහල)', 'fetch-tweets' ),
					'sv' => __( 'Swedish (Svenska)', 'fetch-tweets' ),
					'ta' => __( 'Tamil (தமிழ்)', 'fetch-tweets' ),
					'te' => __( 'Telugu (తెలుగు)', 'fetch-tweets' ),
					'th' => __( 'Thai (ไทย)', 'fetch-tweets' ),
					'tl' => __( 'Tagalog (Tagalog)', 'fetch-tweets' ),
					'tr' => __( 'Turkish (Türkçe)', 'fetch-tweets' ),
					'ur' => __( 'Urdu (ﺍﺭﺩﻭ)', 'fetch-tweets' ),
					'vi' => __( 'Vietnamese (Tiếng Việt)', 'fetch-tweets' ),
					'zh' => __( 'Chinese (中文)', 'fetch-tweets' ),
				),		
				'vDefault' 			=> 'none',	
			),				
			array(
				'strFieldID'		=> 'result_type',
				'strTitle'			=> __( 'Result Type', 'fetch-tweets' ),
				'strType'			=> 'radio',
				'vLabel' => array( 
					'mixed' => 'mixed' . ' - ' . __( 'includes both popular and real time results in the response.', 'fetch-tweets' ),
					'recent' => 'recent' . ' - ' . __( 'returns only the most recent results in the response.', 'fetch-tweets' ),
					'popular' => 'popular' . ' - ' . __( 'return only the most popular results in the response.', 'fetch-tweets' ),
				),
				'vDefault' => 'mixed',
			),
			array(	// since 1.3.3
				'strFieldID'		=> 'until',
				'strTitle'			=> __( 'Date Until', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'strDescription'	=> __( 'Returns tweets generated before the given date. Set blank not to specify any date.', 'fetch-tweets' ),
				'strType'			=> 'date',
				'vDateFormat' 		=> 'yy-mm-dd',	// yy/mm/dd is the default format.
			),
			array(	// since 1.3.3
				'strFieldID'		=> 'geocentric_coordinate',
				'strTitle'			=> __( 'Geometric Coordinate', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'strDescription'	=> __( 'Restricts tweets to users located within a given radius of the given latitude/longitude. Set them empty not to set any.', 'fetch-tweets' ),
				'strType'			=> 'text',
				'vLabel'			=> array(
					'latitude' => __( 'Latitude', 'fetch-tweets' ),
					'longitude' => __( 'Longitude', 'fetch-tweets' ),
				),
			),
			array(	// since 1.3.3
				'strFieldID'		=> 'geocentric_radius',
				'strTitle'			=> __( 'Geometric Radius', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'strType'			=> 'size',
				'vDefault'			=> array( 'size' => '', 'unit' => 'mi' ),
				'vSizeUnits'		=> array(
					'mi' => __( 'miles', 'fetch-tweets' ), 'km' => __( 'kilometers', 'fetch-tweets' ) 
				),
				'strDescription'	=> __( 'Set this empty not to set any. In order to perform the geometric search, this option and the above coordinate must be specified.', 'fetch-tweets' ),
			),			
			array(
				'strFieldID'		=> 'exclude_replies',
				'strType'			=> 'hidden',
			),
			array(	// since 1.2.0
				'strFieldID'		=> 'list_id',			
				'strType'			=> 'hidden',
			),		
			array(
				'strFieldID'		=> 'include_retweets',
				'strType'			=> 'hidden',
			),			
			array()
		);
		
	}	

	/**
	 * Adds form fields for the options to fetch tweets by list to the meta box.
	 * 
	 * @since			1.2.0
	 */ 
	protected function addFieldsForTweetsByList() {
		
		$strScreenName = $this->getScreenName();
		$arrLists = $this->getLists( $strScreenName );
		
		$this->addSettingFields(		
			array(
				'strFieldID'		=> 'tweet_type',
				'strType'			=> 'hidden',
				'vValue'			=> 'list',
			),			
			array(
				'strFieldID'		=> 'list_id',
				'strTitle'			=> __( 'Lists', 'fetch-tweets' ),
				'strType'			=> 'select',
				'vLabel'			=> $arrLists,
			),
			array(	// non-used fields must be set as hidden since the callback function will assign a value.
				'strFieldID'		=> 'screen_name',
				'strType'			=> 'hidden',
				'vValue'			=> $strScreenName,
			),				
			array(
				'strFieldID'		=> 'search_keyword',
				'strType'			=> 'hidden',
			),
			array(
				'strFieldID'		=> 'item_count',
				'strTitle'			=> __( 'Item Count', 'fetch-tweets' ),
				'strDescription'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'strType'			=> 'number',
				'vDefault'			=> 20,
				'vMax'				=> 100,				
			),				
			array(
				'strFieldID'		=> 'language',
				'strType'			=> 'hidden',
			),				
			array(
				'strFieldID'		=> 'result_type',
				'strType'			=> 'hidden',
			),		
			array(
				'strFieldID'		=> 'exclude_replies',
				'strType'			=> 'hidden',
			),
			array(
				'strFieldID'		=> 'include_retweets',
				'strTitle'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'vLabel'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
			),				
			array(	// since 1.3.3
				'strFieldID'		=> 'until',
				'strType'			=> 'hidden',
			),		
			array(	// since 1.3.3
				'strFieldID'		=> 'geocentric_coordinate',
				'strType'			=> 'hidden',
			),
			array(	// since 1.3.3
				'strFieldID'		=> 'geocentric_radius',
				'strType'			=> 'hidden',
			),					
			array()
		);
				
		
		
		
	}
	/**
	 * Returns an array of lists received from the previous page; otherwise, fetches lists from the set screen name.
	 * 
	 */	 
	protected function getLists( $strScreenName='' ) {
		
		// If the cache is set from the previous page, use that.
		$strListTransient = isset( $_GET['list_cache'] ) ? $_GET['list_cache'] : '';
		if ( ! empty( $strListTransient ) ) {
			$arrLists = ( array ) get_transient( $strListTransient );
			delete_transient( $strListTransient );
			return $arrLists;
		}
		
		if ( empty( $strScreenName ) ) return array();	
		
		// Fetch lists from the given screen name.
		$oFetch = new FetchTweets_Fetch;
		$arrLists = $oFetch->getListNamesFromScreenName( $strScreenName );
		return $arrLists;
		
	}
	/**
	 * Returns the associated screen name (twitter user name) of the post.
	 * 
	 * @return			string				The screen name associated with the post.
	 * @since			1.2.0
	 */
	protected function getScreenName() {
		
		// If the 'action' query value is edit, search for the meta field value which previously set when it is saved.
		if ( isset( $_GET['action'], $_GET['post'] ) && $_GET['action'] == 'edit' ) 
			return get_post_meta( $_GET['post'], 'screen_name', true );
	
		// If the GET 'tweet_type' query value is set, use it.
		if ( isset( $_GET['screen_name'] ) && $_GET['screen_name'] ) return $_GET['screen_name'];
		
		return '';
		
	}
	
	public function validation_FetchTweets_MetaBox_Options( $arrInput ) {	// validation_ + extended class name
			
		$arrInput['item_count'] = $this->oUtil->fixNumber( 
			$arrInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			$arrInput['tweet_type'] == 'search' ? 100 : 200 	// max
		);
		
		$arrInput['item_count'] = $this->oUtil->fixNumber(
			$arrInput['item_count'], // number to sanitize
			300,	// default
			0	// min
		);
		
		return $arrInput;
		
	}
	
}
