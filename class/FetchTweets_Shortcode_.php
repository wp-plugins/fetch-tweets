<?php
abstract class FetchTweets_Shortcode_ {

	public function __construct( $strShortCode ) {
				
		$this->oOption = & $GLOBALS['oFetchTweets_Option'];
		
		// Add the shortcode.
		add_shortcode( $strShortCode, array( $this, 'getOutput' ) );
		
	}
	
	public function getOutput( $arrArgs ) {
	
		$arrArgs = ( array ) $arrArgs + $this->oOption->arrStructure_DefaultParams;
		
		$this->oFetch = isset( $this->oFetch ) ? $this->oFetch : new FetchTweets_Fetch();
		
		if ( isset( $arrArgs['id'] ) ) 
			return $this->oFetch->getTweets( 
				$arrArgs['id'],
				isset( $arrArgs['count'] ) ? $arrArgs['count'] : null, 
				isset( $arrArgs['sort'] ) ? $arrArgs['sort'] : null
			);	
		else if ( isset( $arrArgs['ids'] ) )	
			return $this->oFetch->getTweets( 
				is_array( $arrArgs['ids'] ) ? $arrArgs['ids'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['ids'] ), 0, PREG_SPLIT_NO_EMPTY ),
				isset( $arrArgs['count'] ) ? $arrArgs['count'] : null, 
				isset( $arrArgs['sort'] ) ? $arrArgs['sort'] : null
			);
		else if ( isset( $arrArgs['tag'] ) ) 
			return $this->oFetch->getTweetsByTag( 
				$arrArgs['tag'], 
				isset( $arrArgs['count'] ) ? $arrArgs['count'] : null, 
				isset( $arrArgs['sort'] ) ? $arrArgs['sort'] : null
			);
				
	}	

}