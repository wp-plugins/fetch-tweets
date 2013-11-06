<?php
abstract class FetchTweets_Option_ {
	
	protected static $arrStructure_Options = array(
		'fetch_tweets_settings' => array(
			'authentication_keys' => array(
				'consumer_key' => '',
				'consumer_secret' => '',
				'access_token' => '',
				'access_secret' => '',
			),
			'twitter_connect' => array(
				'access_token' => '',
				'access_secret' => '',
			),
			'default_values' => array(),
			'capabilities' => array(),
			'cache_settings' => array(
				'cache_for_errors' => false,
			),
		),
		'arrTemplates' => array(),	// stores template info arrays.
		'arrDefaultTemplate' => array(),	// stores the default template info.
	);
	
	public $arrStructure_DefaultParams = array(
		'tweet_type'		=> null,	// this will be set in the add/edit rule page
		
		'id'				=> null,
		'ids'				=> null,	// deprecated as of 1.0.0.4 - but extension plugins may use it
		'tag'				=> null,
		'tags'				=> null,	// deprecated as of 1.0.0.4 - but extension plugins may use it
		'count'				=> 20,
		// 'avatar_size'		=> 48,
		'operator'			=> 'AND',
		'tag_field_type'	=> 'slug',				// used internally. slug or id.
		'sort'				=> 'descending',		//  ascending, descending, or random 
		// 'template'			=> null,	// the template slug
		
		// for custom function calls
		'q'					=> null,	
		'screen_name'		=> null,	// 
		'include_rts'		=> 0,		// 
		'exclude_replies'	=> 0,		// 
		'cache'				=> 1200,	// Cache lifespan in seconds.
		'lang'				=> null,	// 
		'result_type'		=> 'mixed',	// 
		'until'				=> '',		// since 1.3.3
		'geocode'			=> '',		// since 1.3.3 - this is for shortcode parametrs while geocentric_coordinate and geocentric_radius are for the meta box options.
		'geocentric_coordinate'	=> array(	// since 1.3.3
			'latitude' => '',
			'longitude' => '',
		),
		'geocentric_radius' => array(	// since 1.3.3
			'size' => '',
			'unit' => 'mi',
		),
		
		// since 1.2.0
		'list_id'			=> null,	
		'twitter_media'		=> true,
		'external_media'	=> true,
	);
	public $arrStructure_DefaultTemplateOptions = array(
		// leave them null and let each template define default values.
		'template'			=> null,	// the template slug
		'avatar_size'		=> null,	// 48, 
		'width'				=> null,	// 100,	
		'width_unit'		=> null,	// '%',	
		'height'			=> null,	// 800,
		'height_unit'		=> null,	// 'px',
	);		 
	public $arrOptions = array();	// stores the option values.
		 
	protected $strOptionKey = '';	// stores the option key for this plugin. 
		 
	public function __construct( $strOptionKey ) {
		
		$this->strOptionKey = $strOptionKey;
		$this->arrOptions = $this->setOption( $strOptionKey );
// FetchTweets_Debug::getArray( $this->arrOptions, dirname( __FILE__ ) . '/options.txt' );
	}	
	
	/*
	 * 
	 * Back end methods
	 * */
	private function setOption( $strOptionKey ) {
		
		$vOption = get_option( $strOptionKey );
		
		// Avoid casting array because it causes a zero key when the subject is null.
		$vOption = ( $vOption === false ) ? array() : $vOption;		
		
		// Now $vOption is an array so merge with the default option to avoid undefined index warnings.
		$arrOptions = $this->uniteArraysRecursive( $vOption, self::$arrStructure_Options ); 

		// If the template option array is empty, retrieve the active template arrays.
		if ( empty( $arrOptions['arrTemplates'] ) ) {
			
			$oTemplate = new FetchTweets_Templates;
			$arrDefaultTemplate = $oTemplate->findDefaultTemplateDetails();
			$arrOptions['arrTemplates'][ $arrDefaultTemplate['strSlug'] ] = $arrDefaultTemplate;
			$arrOptions['arrDefaultTemplate'] = $arrDefaultTemplate;
			
			// Schedule updating the option at the end of the script.
			add_action( 'shutdown', array( $this, 'saveOptions' ) );
			
		}
		
		return $arrOptions;
				
	}
	private function uniteArraysRecursive( $arrPrecedence, $arrDefault ) {
		
		// Merges two multi-dimensional arrays recursively. The first parameter array takes its precedence.
		// This is useful to merge default option values.
		
		if ( is_null( $arrPrecedence ) ) $arrPrecedence = array();
		
		if ( ! is_array( $arrDefault ) || ! is_array( $arrPrecedence ) ) return $arrPrecedence;
			
		foreach( $arrDefault as $strKey => $v ) {
			
			// If the precedence does not have the key, assign the default's value.
			if ( ! array_key_exists( $strKey, $arrPrecedence ) || is_null( $arrPrecedence[ $strKey ] ) )
				$arrPrecedence[ $strKey ] = $v;
			else {
				
				// if the both are arrays, do it the recursively.
				if ( is_array( $arrPrecedence[ $strKey ] ) && is_array( $v ) ) 
					$arrPrecedence[ $strKey ] = $this->uniteArraysRecursive( $arrPrecedence[ $strKey ], $v );			
			
			}
		}
		return $arrPrecedence;		
	}		
	
	/*
	 * Front end methods
	 * */
	 
	 
	public function getAccessTokenAuto() {
		return $this->arrOptions['fetch_tweets_settings']['twitter_connect']['access_token'];
	}	
	public function getAccessTokenSecretAuto() {
		return $this->arrOptions['fetch_tweets_settings']['twitter_connect']['access_secret'];
	}		 
	/**
	 * Saves the given access token and the access secret key in the option table.
	 * 
	 * @remark			This uses different keys than the ones for v1.2.0 or below because these are for the automatic authentication.
	 * @since			1.3.0
	 * @return			void
	 */	 
	public function saveAccessToken( $strAccessToken, $strAccessSecret ) {
		
		$this->arrOptions['fetch_tweets_settings']['twitter_connect']['access_token'] = $strAccessToken;
		$this->arrOptions['fetch_tweets_settings']['twitter_connect']['access_secret'] = $strAccessSecret;
		$this->saveOptions();
		
	}
	/**
	 * Returns whether the plugin has set the API authentication keys automatically.
	 * 
	 * since			1.3.0
	 */
	public function isAuthKeysAutomaticallySet() {
		return ( $this->getAccessTokenAuto() && $this->getAccessTokenSecretAuto() )
			? true
			: false;
	}
	/**
	 * Returns whether the user has set the API authentication keys manually.
	 * 
	 * As of v1.3.0, automatic authentication is supported. If the user already sets the keys by themselves already, no need to re-authorize. 
	 * Also if the consumer key and consumer secret are provided by miunosoft, if they become invalid for some reasons, the user can set them by themselves.
	 * 
	 * since			1.3.0
	 * return			boolean
	 */
	public function isAuthKeysManuallySet() {
		return ( $this->getConsumerKey() && $this->getConsumerSecret() && $this->getAccessToken() && $this->getAccessTokenSecret() )
			? true 
			: false;
	}
	
	public function getConsumerKey() {
		return $this->arrOptions['fetch_tweets_settings']['authentication_keys']['consumer_key'];
	}
	public function getConsumerSecret() {
		return $this->arrOptions['fetch_tweets_settings']['authentication_keys']['consumer_secret'];
	}
	public function getAccessToken() {
		return $this->arrOptions['fetch_tweets_settings']['authentication_keys']['access_token'];
	}	
	public function getAccessTokenSecret() {
		return $this->arrOptions['fetch_tweets_settings']['authentication_keys']['access_secret'];
	}	
	
	public function saveOptions() {
		
		update_option( $this->strOptionKey, $this->arrOptions );
		
	}
	
}