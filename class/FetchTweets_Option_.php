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
		),
	);
	
	public $arrStructure_DefaultParams = array(
		'id'				=> null,
		'ids'				=> null,	// deprecated as of 1.0.0.4 - but extension plugins may use it
		'tag'				=> null,
		'tags'				=> null,	// deprecated as of 1.0.0.4 - but extension plugins may use it
		'count'				=> 20,
		'avatar_size'		=> 48,
		'operator'			=> 'AND',
		'tag_field_type'	=> 'slug',				// used internally. slug or id.
		'sort'				=> 'descending',		//  ascending, descending, or random 
		
		// for custom function calls
		'q'					=> null,	
		'screen_name'		=> null,	// 
		'include_rts'		=> 0,		// 
		'exclude_replies'	=> 0,		// 
		'cache'				=> 1200,	// Cache lifespan in seconds.
		'lang'				=> null,	// 
		'result_type'		=> 'mixed',	// 
	);
		 
	public function __construct( $strOptionKey ) {
		
		$this->arrOptions = $this->setOption( $strOptionKey );
	}	
	private function setOption( $strOptionKey ) {
		
		$vOption = get_option( $strOptionKey );
		
		// Avoid casting array because it causes a zero key when the subject is null.
		$vOption = ( $vOption === false ) ? array() : $vOption;		
		
		// Now $vOption is an array so merge with the default option to avoid undefined index warnings.
		return $this->uniteArraysRecursive( $vOption, self::$arrStructure_Options ); 
		
		
	}
	
	/*
	 * Front end methods
	 * */
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
				
				// if the both are arrays, do the recursive process.
				if ( is_array( $arrPrecedence[ $strKey ] ) && is_array( $v ) ) 
					$arrPrecedence[ $strKey ] = $this->uniteArraysRecursive( $arrPrecedence[ $strKey ], $v );			
			
			}
		}
		return $arrPrecedence;		
	}		
}