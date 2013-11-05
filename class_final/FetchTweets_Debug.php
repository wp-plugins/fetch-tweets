<?php
/**
	Methods used for debugging
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 * 
	
*/

final class FetchTweets_Debug {

	static public function getArray( $arr, $strFilePath=null ) {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		if ( $strFilePath ) {
			
			file_put_contents( 
				$strFilePath , 
				date( "Y/m/d H:i:s" ) . PHP_EOL
				. print_r( $arr, true ) . PHP_EOL . PHP_EOL
				, FILE_APPEND 
			);					
			
		}
		return '<pre class="dump-array">' . esc_html( print_r( $arr, true ) ) . '</pre>';
		
	}
	
	static public function echoMemoryUsage() {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
				   
		echo self::getMemoryUsage() . "<br/>";
		
	} 		

    static public function getMemoryUsage( $intType=1 ) {
       
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
	   
		$intMemoryUsage = $intType == 1 ? memory_get_usage( true ) : memory_get_peak_usage( true );
       
        if ( $intMemoryUsage < 1024 ) return $intMemoryUsage . " bytes";
        
		if ( $intMemoryUsage < 1048576 ) return round( $intMemoryUsage/1024,2 ) . " kilobytes";
        
        return round( $intMemoryUsage / 1048576,2 ) . " megabytes";
           
    } 		
	
	static public function getOption( $strKey ) {

		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		$oOption = & $GLOBALS['oFetchTweets_Option'];		
		if ( ! isset( $oOption->arrOptions[ $strKey ] ) ) return;
		
		die( self::gumpArray( $oOption->arrOptions[ $strKey ] ) );
		
	}
}