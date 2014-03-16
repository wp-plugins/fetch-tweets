<?php
/**
 * 
 * @deprecated			2
 */
class FetchTweets_MetaBox_Options_ extends FetchTweets_AdminPageFramework_MetaBox {
	
	public function start_FetchTweets_MetaBox_Options() {
		
		// Register the date custom field type
		new FetchTweets_DateCustomFieldType( get_class( $this ) );		
		
	}
	
	public function setUp() {
		
		switch ( $this->_getTweetType() ) {
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
		
	}
	
	private function _getTweetType() {

		// If the GET 'tweet_type' query value is set, use it.
		if ( isset( $_GET['tweet_type'] ) && $_GET['tweet_type'] ) return $_GET['tweet_type'];
	
		// If the 'action' query value is edit, search for the meta field value which previously set when it is saved.
		if ( isset( $_GET['action'], $_GET['post'] ) && $_GET['action'] == 'edit' ) 
			return get_post_meta( $_GET['post'], 'tweet_type', true );
		
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
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'screen_name',
				'hidden'		=>	true,
			),						
			array(
				'field_id'		=> 'screen_name',
				'title'			=> __( 'User Name', 'fetch-tweets' ),
				'description'	=> __( 'The user name (screen name) that is used after the @ mark or the end of the twitter url.', 'fetch-tweets' ) . '',
				'type'			=> 'text',
			),	
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 200 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	200,
				),
			),				
			array(
				'field_id'		=> 'search_keyword',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),		
			array(
				'field_id'		=> 'language',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),				
			array (
				'field_id'		=> 'result_type',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),			
			array(
				'field_id'		=> 'exclude_replies',
				'title'			=> 'Exclude Replies',
				'type'			=> 'checkbox',
				'label'			=> __( 'Replies will be excluded.', 'fetch-tweets' ),
				'description'	=> __( 'This prevents replies from appearing in the returned timeline.', 'fetch-tweets' ),
			),		
			array(	// since 1.2.0
				'field_id'		=> 'list_id',			
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),			
			array(
				'field_id'		=> 'include_retweets',
				'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'type'			=> 'checkbox',
			),			
			array(	// since 1.3.3
				'field_id'		=> 'until',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),			
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_coordinate',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_radius',
				'type'			=> 'hidden',
				'hidden'		=>	true,
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
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'search',
				'hidden'		=>	true,
			),			
			array(	// non-used fields must be set as hidden since the callback function will assign a value.
				'field_id'		=> 'screen_name',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),				
			array(
				'field_id'		=> 'search_keyword',
				'title'			=> __( 'Search Keyword', 'fetch-tweets' ),
				'description'	=> sprintf( __( 'The keyword to search. For a complex combination of terms and operators, refer to the <strong>Search Operators</strong> section of <a href="%1$s" target="_blank">Using the Twitter Search API</a>.', 'fetch-tweets' ), 'https://dev.twitter.com/docs/using-search' ) 
					. ' e.g. <code>love OR hate</code>, <code>#wordpress</code>',
				'type'			=> 'text',
			),
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	100,
				),
			),				
			array(
				'field_id'		=> 'language',
				'title'			=> __( 'Language ', 'fetch-tweets' ),
				'type'			=> 'select',
				'label' => array( 
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
				'default' 			=> 'none',	
			),				
			array(
				'field_id'		=> 'result_type',
				'title'			=> __( 'Result Type', 'fetch-tweets' ),
				'type'			=> 'radio',
				'label' => array( 
					'mixed' => 'mixed' . ' - ' . __( 'includes both popular and real time results in the response.', 'fetch-tweets' ),
					'recent' => 'recent' . ' - ' . __( 'returns only the most recent results in the response.', 'fetch-tweets' ),
					'popular' => 'popular' . ' - ' . __( 'return only the most popular results in the response.', 'fetch-tweets' ),
				),
				'default' => 'mixed',
			),
			array(	// since 1.3.3
				'field_id'		=> 'until',
				'title'			=> __( 'Date Until', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'description'	=> __( 'Returns tweets generated before the given date. Set blank not to specify any date.', 'fetch-tweets' ),
				'type'			=> 'date',
				'date_format' 		=> 'yy-mm-dd',	// yy/mm/dd is the default format.
			),
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_coordinate',
				'title'			=> __( 'Geometric Coordinate', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'description'	=> __( 'Restricts tweets to users located within a given radius of the given latitude/longitude. Leave this empty not to set any.', 'fetch-tweets' ),
				'type'			=> 'text',
				'label'			=> array(
					'latitude' => __( 'Latitude', 'fetch-tweets' ),
					'longitude' => __( 'Longitude', 'fetch-tweets' ),
				),
			),
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_radius',
				'title'			=> __( 'Geometric Radius', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'type'			=> 'size',
				'default'		=> array( 'size' => '', 'unit' => 'mi' ),
				'units'			=> array(
					'mi'	=>	__( 'miles', 'fetch-tweets' ),
					'km' => __( 'kilometers', 'fetch-tweets' ),
				),
				'description'	=> __( 'Leave this empty not to set any. In order to perform the geometric search, this option and the above coordinate must be specified.', 'fetch-tweets' ),
			),			
			array(
				'field_id'		=> 'exclude_replies',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(	// since 1.2.0
				'field_id'		=> 'list_id',			
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),		
			array(
				'field_id'		=> 'include_retweets',
				'type'			=> 'hidden',
				'hidden'		=>	true,
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
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'list',
				'hidden'		=>	true,
			),			
			array(
				'field_id'		=> 'list_id',
				'title'			=> __( 'Lists', 'fetch-tweets' ),
				'type'			=> 'select',
				'label'			=> $arrLists,
			),
			array(	// non-used fields must be set as hidden since the callback function will assign a value.
				'field_id'		=> 'screen_name',
				'type'			=> 'hidden',
				'value'			=> $strScreenName,
				'hidden'		=>	true,
			),				
			array(
				'field_id'		=> 'search_keyword',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	100,
				),				
			),				
			array(
				'field_id'		=> 'language',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),				
			array(
				'field_id'		=> 'result_type',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),		
			array(
				'field_id'		=> 'exclude_replies',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(
				'field_id'		=> 'include_retweets',
				'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'type'			=> 'checkbox',
			),				
			array(	// since 1.3.3
				'field_id'		=> 'until',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),		
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_coordinate',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_radius',
				'type'			=> 'hidden',
				'hidden'		=>	true,
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
