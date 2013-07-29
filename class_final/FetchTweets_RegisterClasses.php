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
		// $this->arrClassNames = array_map( array( $this, 'getNameWOExtFromPath' ), glob( $this->strClassDirPath . '*.php' ) );
		$this->arrClassFileNames = array_map( array( $this, 'getBaseName' ), glob( $this->strClassDirPath . '*.php' ) );
		// $this->arrClassFileNames = glob( $this->strClassDirPath . '*.php' );
		$this->setUpClassArray();
				
	}
	function setUpClassArray() {
				
		foreach( $this->arrClassFileNames as $strClassFileName ) {
			
			// if it's set, do not register ( add it to the array ).
			if ( isset( $this->arrClassPaths[ $strClassFileName ] ) ) continue;
			
			$this->arrClassPaths[ $strClassFileName ] = $this->strClassDirPath . $strClassFileName;	
		
		}
	}
	public function registerClasses() {
		
		spl_autoload_register( array( $this, 'callBackFromAutoLoader' ) );
		
	}
	public function getBaseName( $strPath ) {
		return basename( $strPath );
	}
	public function getNameWOExtFromPath( $strPath ) {
		
		return basename( $strPath, '.php' );	// returns the file name without the extension
		
	}
	public function callBackFromAutoLoader( $strClassName ) {
		
		$strBaseName = $strClassName . '.php';
		
		if ( ! in_array( $strBaseName, $this->arrClassFileNames ) ) return;
		
		if ( file_exists( $this->arrClassPaths[ $strBaseName ] ) ) 
			include_once( $this->arrClassPaths[ $strBaseName ] );
		
	}
	
}