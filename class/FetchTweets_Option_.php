<?php
abstract class FetchTweets_Option_ {
	
	protected static $aStructure_Options = array(		
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
		'arrTemplates' => array(),	// stores template info arrays.
		'arrDefaultTemplate' => array(),	// stores the default template info.
	);
	
	public $aStructure_DefaultParams = array(
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
	public $aStructure_DefaultTemplateOptions = array(
		// leave them null and let each template define default values.
		'template'			=> null,	// the template slug
		'avatar_size'		=> null,	// 48, 
		'width'				=> null,	// 100,	
		'width_unit'		=> null,	// '%',	
		'height'			=> null,	// 800,
		'height_unit'		=> null,	// 'px',
	);		 
	public $aOptions = array();	// stores the option values.
		 
	protected $sOptionKey = '';	// stores the option key for this plugin. 
		 
	public function __construct( $sOptionKey ) {
		
		$this->sOptionKey = $sOptionKey;
		$this->aOptions = $this->setOption( $sOptionKey );
// FetchTweets_Debug::logArray( $this->aOptions, dirname( __FILE__ ) . '/options.txt' );
	}	
	
	/*
	 * 
	 * Back end methods
	 * */
	private function setOption( $sOptionKey ) {
		
		// Set up the options array.
		$vOption = get_option( $sOptionKey );
		$vOption = ( $vOption === false ) ? array() : $vOption;		// Avoid casting array because it causes a zero key when the subject is null.
		$aOptions = FetchTweets_Utilities::uniteArrays( $vOption, self::$aStructure_Options ); 	// Now $vOption is an array so merge with the default option to avoid undefined index warnings.
		
		// format the options for backward compatibility
		// If it's the v1 option array,
		if ( isset( $aOptions['fetch_tweets_settings'] ) || isset( $aOptions['fetch_tweets_templates'] ) ) {
			
			$aOptions = $this->_convertV1OptionsToV2( $aOptions );
			add_action( 'shutdown', array( $this, 'saveOptions' ) );
			
		}
		
		// If the template option array is empty, retrieve the active template arrays.
		if ( empty( $aOptions['arrTemplates'] ) ) {
			
			$oTemplate = new FetchTweets_Templates;
			$arrDefaultTemplate = $oTemplate->findDefaultTemplateDetails();
			$aOptions['arrTemplates'][ $arrDefaultTemplate['strSlug'] ] = $arrDefaultTemplate;
			$aOptions['arrDefaultTemplate'] = $arrDefaultTemplate;
			
			// Schedule updating the option at the end of the script.
			add_action( 'shutdown', array( $this, 'saveOptions' ) );
			
		}
		
		return $aOptions;
				
	}
		protected function _convertV1OptionsToV2( $aOptions ) {

			// Drop the page slug dimension.
			$_aOptions = FetchTweets_Utilities::uniteArrays(
				isset( $aOptions['fetch_tweets_settings'] ) ? $aOptions['fetch_tweets_settings'] : array(),
				isset( $aOptions['fetch_tweets_templates'] ) ? $aOptions['fetch_tweets_templates'] : array()
			);
			unset( $aOptions['fetch_tweets_settings'], $aOptions['fetch_tweets_settings'] );

			// For template options
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['top'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 0 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['top'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['top'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['right'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 1 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['right'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['right'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['bottom'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 2 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['bottom'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['bottom'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['left'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 3 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['left'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['left'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['top'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 0 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['top'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['top'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['right'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 1 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['right'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['right'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['bottom'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 2 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['bottom'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['bottom'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['left'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 3 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['left'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['left'] );
			}		

			return $_aOptions + $aOptions;
			
		}
	/*
	 * Front end methods
	 * */
	 
	 
	public function getAccessTokenAuto() {
		return $this->aOptions['twitter_connect']['access_token'];
	}	
	public function getAccessTokenSecretAuto() {
		return $this->aOptions['twitter_connect']['access_secret'];
	}		 
	/**
	 * Saves the given access token and the access secret key in the option table.
	 * 
	 * @remark			This uses different keys than the ones for v1.2.0 or below because these are for the automatic authentication.
	 * @since			1.3.0
	 * @return			void
	 */	 
	public function saveAccessToken( $sAccessToken, $sAccessSecret ) {
		
		$this->aOptions['twitter_connect']['access_token'] = $sAccessToken;
		$this->aOptions['twitter_connect']['access_secret'] = $sAccessSecret;
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
		return $this->aOptions['authentication_keys']['consumer_key'];
	}
	public function getConsumerSecret() {
		return $this->aOptions['authentication_keys']['consumer_secret'];
	}
	public function getAccessToken() {
		return $this->aOptions['authentication_keys']['access_token'];
	}	
	public function getAccessTokenSecret() {
		return $this->aOptions['authentication_keys']['access_secret'];
	}	
	
	public function saveOptions() {
		
		update_option( $this->sOptionKey, $this->aOptions );
		
	}
	
}