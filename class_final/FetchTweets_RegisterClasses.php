<?php
/**
	
 * @package     FetchTweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 
*/

class FetchTweets_RegisterClasses {
	
	function __construct( $strClassDirPath, & $arrClassPaths=array() ) {
		
		$this->arrClassPaths = $arrClassPaths;	// the link to the array storing registered classes outside this object.
		$this->strClassDirPath = trailingslashit( $strClassDirPath );
		$this->arrClassNames = array_map( array( $this, 'getNameWOExtFromPath' ), glob( $this->strClassDirPath . '*.php' ) );
		$this->setUpClassArray();
				
	}
	function setUpClassArray() {
				
		foreach( $this->arrClassNames as $strClassName ) {
			
			// if it's set, do not register ( add it to the array ).
			if ( isset( $this->arrClassPaths[ $strClassName ] ) ) continue;
			
			$this->arrClassPaths[ $strClassName ] = $this->strClassDirPath . $strClassName;	
		
		}
	}
	public function registerClasses() {
		
		spl_autoload_register( array( $this, 'callBackFromAutoLoader' ) );
		
	}
	public function getNameWOExtFromPath( $str ) {
		
		return basename( $str, '.php' );	// returns the file name without the extension
		
	}
	public function callBackFromAutoLoader( $strClassName ) {
		
		if ( ! in_array( $strClassName, $this->arrClassNames ) ) return;
		
		if ( file_exists( $this->arrClassPaths[ $strClassName ] . '.php' ) ) 
			include_once( $this->arrClassPaths[ $strClassName ] . '.php' );
		
	}
	
}