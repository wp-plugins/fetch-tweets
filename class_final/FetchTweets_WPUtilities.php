<?php
/**
 *	Provides utility methods which use WordPress functions.
 *
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.3.3.2
 * 
 */

final class FetchTweets_WPUtilities {


	/**
	 * Calculates the URL from the given path.
	 * 
	 * 
	 * 
	 * @since			1.3.3.2
	 * @static
	 * @access			public
	 * @return			string			The source url
	 */
	static public function getSRCFromPath( $strFilePath ) {
				
		// It doesn't matter whether the file is a style or not. Just use the built-in WordPress class to calculate the SRC URL.
		$oWPStyles = new WP_Styles();	
		$strRelativePath = '/' . FetchTweets_Utilities::getRelativePath( ABSPATH, $strFilePath );
		$strHref = $oWPStyles->_css_href( $strRelativePath, '', '' );
		return $strHref;
		
	}

}