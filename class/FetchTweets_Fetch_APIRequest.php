<?php
/**
 * Handles Twitter API requests.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			1.3.4
 */
abstract class FetchTweets_Fetch_APIRequest extends FetchTweets_Fetch_Cache {
	
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
			if ( ( $arrTransient['mod'] + ( ( int ) $intCacheDuration ) ) < time() ) { 	// expired

				$this->arrExpiredTransientsRequestURIs[] = array( 
					// these keys will be checked in the cache renewal events.
					'URI'	=> $strRequestURI, 	
					'key'	=> $strArrayKey,
				);
			}

			return ( array ) $this->oBase64->decode( $arrTransient['data'] );
			
		}

		return $this->setAPIGETRequestCache( $strRequestURI, $strArrayKey );
		
	}	
	
}