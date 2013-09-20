<?php

if ( ! class_exists('TwitterOAuth') )
	require_once( dirname( __FILE__ ) . '/TwitterOAuth/twitteroauth.php' );

class FetchTweets_TwitterOAuth extends TwitterOAuth {
	
	public $host = "https://api.twitter.com/1.1/";
	/**
	* GET wrapper for oAuthRequest.
	*/
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);	// return as associative array
		}
		return $response;
	}
  
	/**
	* POST wrapper for oAuthRequest.
	*/
	function post($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);	// return as associative array
		}
		return $response;
	}

	/**
	* DELETE wrapper for oAuthReqeust.
	*/
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);	// return as associative array
		}
		return $response;
	}
}