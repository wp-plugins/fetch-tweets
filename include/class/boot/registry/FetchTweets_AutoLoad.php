<?php
/**
 * Registers PHP classes to be auto-loaded.
 * 
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @since		2.1
 */

final class FetchTweets_AutoLoad {
	
	/**
	 * Stores the registered classes with the key of the class name and the value of the file path.
	 */
	public $_aClasses = array();
	
	/**
	 * Represents the structure of the recursive option array.
	 * 
	 */
	static protected $_aStructure_RecursiveOptions = array(
		'is_recursive'	=>	true,
		'exclude_dirs'	=>	array(),	
	);
	
	/**
	 * param			array			$sClassDirPath		the target directory path to scan
	 * param			array			$aClasses			the link to the array storing registered classes outside this object.
	 * The structure of %aClasses must be consist of elements of a key-value pair of a file path and the key of the class name.
	 * array(
	 * 	'MyClassName' => 'MyClassName.php',
	 * 	'MyClassName2' => 'MyClassName2.php',
	 * )
	 * 
	 * param			array			The recursive settings
	 * 		array(
	 * 			'is_recursive'	=> true,		// determines whether the scan should be performed recursively.
	 * 			'exclude_dirs' => array(),		// set excluding dir paths without ending slash with numeric keys.
	 * 		)
	 * @remark			The directory paths set for the 'exclude_dirs' option should use the system directory separator.
	 */
	function __construct( $sClassDirPath, $aClasses=array(), $aRecursiveOptions=array( 'is_recursive' => true, 'exclude_dirs' => array() ), $aAllowedExtensions=array( 'php', 'inc' ) ) {
			
		$aRecursiveOptions = $aRecursiveOptions + self::$_aStructure_RecursiveOptions;
		$this->_aClasses = $aClasses + $this->composeClassArray( $sClassDirPath, $aAllowedExtensions, $aRecursiveOptions );
		$this->registerClasses();
		
	}
	
	/**
	 * Sets up the array consisting of class paths with the key of file name w/o extension.
	 */
	protected function composeClassArray( $sClassDirPath, $aAllowedExtensions, $aRecursiveOptions ) {
		
		$sClassDirPath = rtrim( $sClassDirPath, '\\/' ) . DIRECTORY_SEPARATOR;	// ensures the trailing (back/)slash exists.
		$_aFilePaths = $aRecursiveOptions['is_recursive']
			? $this->doRecursiveGlob( $sClassDirPath . '*.' . $this->getGlobPatternExtensionPart( $aAllowedExtensions ), GLOB_BRACE, $aRecursiveOptions['exclude_dirs'] )
			: glob( $sClassDirPath . '*.' . $this->getGlobPatternExtensionPart( $aAllowedExtensions ), GLOB_BRACE );

		/*
		 * Now the structure of $_aFilePaths looks like:
			array
			  0 => string '.../class/MyClass.php'
			  1 => string '.../class/MyClass2.php'
			  2 => string '.../class/MyClass3.php'
			  ...
		 * 
		 */		 
		$_aClasses = array();
		foreach( $_aFilePaths as $_sFilePath ) {
			$_aClasses[ pathinfo( $_sFilePath, PATHINFO_FILENAME ) ] = $_sFilePath;	// the file name without extension will be assigned to the key
		}

		return $_aClasses;
			
	}
		/**
		 * Composes the file pattern of the file extension part used for the glob() function with the given file extensions.
		 */
		protected function getGlobPatternExtensionPart( $aExtensions=array( 'php', 'inc' ) ) {
			return empty( $aExtensions ) 
				? '*'
				: '{' . implode( ',', $aExtensions ) . '}';
		}
		
		/**
		 * The recursive version of the glob() function.
		 */
		protected function doRecursiveGlob( $sPathPatten, $iFlags=0, $asExcludeDirs=array() ) {

			$_aFiles = glob( $sPathPatten, $iFlags );
			foreach ( glob( dirname( $sPathPatten ) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR|GLOB_NOSORT ) as $_sDirPath ) {

				if ( in_array( $_sDirPath, ( array ) $asExcludeDirs ) ) continue;
				
				$_aFiles = array_merge( $_aFiles, $this->doRecursiveGlob( $_sDirPath . DIRECTORY_SEPARATOR . basename( $sPathPatten ), $iFlags, $asExcludeDirs ) );
				
			}
		
			return $_aFiles;
			
		}
		
	
	/**
	 * Performs registration of the callback.
	 * 
	 * This registers the method to be triggered when an unknown class is instantiated. 
	 * 
	 */
	protected function registerClasses() {
		spl_autoload_register( array( $this, 'replyToAutoLoader' ) );
	}	
		/**
		 * Responds to the PHP auto-loader and includes the passed class based on the previously stored path associated with the class name in the constructor.
		 */
		public function replyToAutoLoader( $sCalledUnknownClassName ) {			
			if ( array_key_exists( $sCalledUnknownClassName, $this->_aClasses ) &&  file_exists( $this->_aClasses[ $sCalledUnknownClassName ] ) ) 
				include_once( $this->_aClasses[ $sCalledUnknownClassName ] );
		}
	
}