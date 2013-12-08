<?php
/**
	
	Handles templates that display fetched tweets.
	
	@package     Fetch Tweets
	@copyright   Copyright (c) 2013, Michael Uno
	@authorurl	http://michaeluno.jp
	@license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
	@since		1.1.0
	@filters 
	- fetch_tweets_filter_template_container_directories	: applies to the loading template container directories
	- fetch_tweets_filter_template_directories				: applies to the loading template directories
*/

abstract class FetchTweets_Templates_ {

	// public $arrTemplateDirs = array();	// stores the template directory paths where the plugin loads templates.
	// public $arrTemplates = array(); // stores each template information.
	
	public static $arrStructure_Template = array(
		'strCSSPath'		=> null,
		'strDirPath'		=> null,
		'strFunctionPath'	=> null,
		'strTemplatePath'	=> null,
		'strSettingsPath'	=> null,
		'strThumbnailPath'	=> null,
		'strName'			=> null,
		'strSlug'			=> null,
		'strDescription'	=> null,
		'strTextDomain'		=> null,
		'strDomainPath'		=> null,
		'strVersion'		=> null,
		'strAuthor'			=> null,
		'strAuthorURI'		=> null,
		'fIsActive'			=> null,
		'fIsDefault'		=> null,
		'intIndex'			=> null,
	);
	
	public function getActiveTemplates() {
			
		// Returns an array that holds arrays of activated template information.
		
		// The default template
		$arrDefaultTemplate = empty( $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate'] ) || ! file_exists( $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate']['strCSSPath'] )
			? $this->findDefaultTemplateDetails()
			: $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate'] + self::$arrStructure_Template;
		
		// The saved active templates.
		$arrActiveTemplates = isset( $GLOBALS['oFetchTweets_Option']->arrOptions['arrTemplates'] )
			? $GLOBALS['oFetchTweets_Option']->arrOptions['arrTemplates']
			: array();
				
		// Add the default template.
		$strDefaultTemplateSlug = $arrDefaultTemplate['strSlug'];
		$arrActiveTemplates[ $strDefaultTemplateSlug ] = $arrDefaultTemplate;
		
		// Format the template array.
		unset(  $arrActiveTemplates[''] );	// just in case 
		foreach( $arrActiveTemplates as $strDirSlug => &$arrActiveTemplate ) {		
		
			if ( ! is_array( $arrActiveTemplate ) ) {
				unset( $arrActiveTemplates[ $strDirSlug ] );
				continue;
			}
			
			$arrActiveTemplate = $arrActiveTemplate + self::$arrStructure_Template;
			$arrActiveTemplate['strDirPath'] = $arrActiveTemplate['strDirPath']	// check if it's not missing
				? $arrActiveTemplate['strDirPath']
				: dirname( $arrActiveTemplate['strCSSPath'] );
			$arrActiveTemplate['strTemplatePath'] = $arrActiveTemplate['strTemplatePath']	// check if it's not missing
				? $arrActiveTemplate['strTemplatePath']
				: dirname( $arrActiveTemplate['strCSSPath'] ) . DIRECTORY_SEPARATOR . 'template.php';
				
			// Check mandatory files. Consider that the user may directly delete the template files/folders.
			if ( 
				! file_exists( $arrActiveTemplate['strDirPath'] . DIRECTORY_SEPARATOR . 'style.css' ) 
				|| ! file_exists( $arrActiveTemplate['strDirPath'] . DIRECTORY_SEPARATOR . 'template.php' ) 
			) 
				unset( $arrActiveTemplates[ $strDirSlug ] );
						
		}
		
		return $arrActiveTemplates;
		
	}
	public function getTemplateArrayForSelectLabel( $arrTemplates=null ) {
		
		if ( ! $arrTemplates )
			$arrTemplates = $this->getActiveTemplates();
			
		$arrLabels = array();
		foreach ( $arrTemplates as $strDirSlug => $arrTemplate ) {
			if ( ! isset( $arrTemplate['strName'] ) ) continue;	// it may be broken.
			$arrLabels[ $strDirSlug ] = $arrTemplate['strName'];
		}
		
		return $arrLabels;		
		
	}
	
	public function getUploadedTemplates() {

		// Read templates and returns stores template information as array.
	
		// Set up the template array.
		$arrTemplateContainerDirs = array();
		$arrTemplateContainerDirs[] = FetchTweets_Commons::getPluginDirPath() . DIRECTORY_SEPARATOR . 'template';
		$arrTemplateContainerDirs[] = get_template_directory() . DIRECTORY_SEPARATOR . 'fetch-tweets';
		$arrTemplateContainerDirs = apply_filters( 'fetch_tweets_filter_template_container_directories', $arrTemplateContainerDirs );
		$arrTemplateContainerDirs = array_unique( $arrTemplateContainerDirs );

		// Load templates.
		$arrTemplateDirs = array();
		foreach( ( array ) $arrTemplateContainerDirs as $strTemplateDirPath ) {
				
			if ( ! file_exists( $strTemplateDirPath  ) ) continue;
			$arrFoundDirs = glob( $strTemplateDirPath . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR );
			if ( is_array( $arrFoundDirs ) )
				$arrTemplateDirs = array_merge( $arrFoundDirs, $arrTemplateDirs );
							
		}
		$arrTemplateDirs = array_unique( $arrTemplateDirs );
		$arrTemplateDirs = apply_filters( 'fetch_tweets_filter_template_directories', $arrTemplateDirs );
		
		$arrTemplates = array();
		$intIndex = 0;		
		foreach ( $arrTemplateDirs as $strDirPath ) {
			
			// Check mandatory files.
			if ( ! file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'style.css' ) ) continue;
			if ( ! file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'template.php' ) ) continue;

			$arrTemplates[ md5( $strDirPath ) ] = array(
					'strCSSPath' => $strDirPath . '/style.css',
					'strDirPath' => $strDirPath,
					'strFunctionPath' => file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'functions.php' ) ? $strDirPath . DIRECTORY_SEPARATOR . 'functions.php' : null,					
					'strTemplatePath' => file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'template.php' ) ? $strDirPath . DIRECTORY_SEPARATOR . 'template.php' : null,					
					'strSettingsPath' => file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'settings.php' ) ? $strDirPath . DIRECTORY_SEPARATOR . 'settings.php' : null,	// this is optional.
					'strThumbnailPath' => $this->getScreenshotPath( $strDirPath ),	// it's not a url.
					'strSlug' => md5( $strDirPath ),			
					'intIndex' => $intIndex++,
				) 
				+ $this->getTemplateData( $strDirPath . DIRECTORY_SEPARATOR . 'style.css' ) 
				+ self::$arrStructure_Template;
					
		}
		
		return $arrTemplates;
		
	}
	protected function getScreenshotPath( $strDirPath ) {
		
		foreach( array( 'jpg', 'jpeg', 'png', 'gif' ) as $strExt ) 
			if ( file_exists( $strDirPath . '/screenshot.' . $strExt ) )
				return $strDirPath . '/screenshot.' . $strExt;
			
	}
	
	/*
	 * Event methods 
	 * */
	/**
	 * Outputs the stylesheet of the given template slug and then exits.
	 * 
	 * @remark			This is called from the event class. 
	 */
	public function loadStyle( $strTemplateSlug ) {

		$strTemplateSlug = trim( $strTemplateSlug );
		$arrTemplate = isset( $GLOBALS['oFetchTweets_Option']->arrOptions['arrTemplates'][ $strTemplateSlug ] )
			? $GLOBALS['oFetchTweets_Option']->arrOptions['arrTemplates'][ $strTemplateSlug ]
			: $this->findDefaultTemplateDetails();
			
		if ( ! file_exists( $arrTemplate['strCSSPath'] ) )
			die( __( '/* The CSS file does not exist. */', 'fetch-tweets' ) );	// the file must exist.

// $strCSS = "/*testign*/";			
$strCSS = $this->getFileContents( $arrTemplate['strCSSPath'] );

		ob_start( "ob_gzhandler" ); 
		header( "Content-Type: text/css" ); 
		header( "X-Content-Type-Options: nosniff" );	// for IE 8 or greater.
		
        // header( 'Cache-control: max-age=' . (60*60*24*30) );
		// header( "Cache-Control: public", false ); 
		// header( "Cache-Control: private", false ); // required for certain browsers 
		// header( "Content-Transfer-Encoding: binary" ); 
        // header( 'Expires: ' . gmdate ( DATE_RFC1123, time()+60*60*24*30 ) );

		
$intCacheDuration = 60000000; // Far far away in time

// Expires
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $intCacheDuration ) . ' GMT' );

// Cache-Control
header( 'Cache-Control: must-revalidate, max-age=' . ( time() + $intCacheDuration ) );

header( 'Last-Modified: ' . $this->get_http_mdate() );

// generate unique ID, using the modification date and the absolute path to the file
$strHash = md5 ( filemtime( $_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF'] ) . $_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF'] );
header( 'Etag: "' . $strHash . '"' );
		
		
		
		// $ctx = stream_context_create( array(
			// 'http' => array(
				// 'timeout' => 0.1
				// )
			// )
		// );
		
		// die( file_get_contents( $arrTemplate['strCSSPath'], 0, $ctx) );
		
		die( $strCSS );		// echo the contents and exit.		
		// die( file_get_contents( $arrTemplate['strCSSPath'] ) );		// echo the contents and exit.		
		
	}
	
		private function getFileContents( $arrFiles ) {
			
			$strCSSData = '';
			foreach ( ( array ) $arrFiles as $strFilePath ) {
				$hFile = fopen( $strFilePath, 'r' );
				$strCSSData .= "\n" . fread( $hFile, filesize( $strFilePath ));
				fclose( $hFile );
			}
			return trim( $strCSSData );
			
		}
		private function get_http_mdate() {
			return gmdate( 'D, d M Y H:i:s', filemtime( $_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF'] ) ) . ' GMT';
		}	
	
	/**
	 * Includes activated templates' functions.php files.
	 * 
	 * @remark			This is called from the initial loader class.
	 * 
	 */ 	
	public function loadFunctionsOfActiveTemplates() {
		
		foreach( $this->getActiveTemplates() as $arrTemplate ) {
			
			if ( ! isset( $arrTemplate['strFunctionPath'], $arrTemplate['strTemplatePath'], $arrTemplate['strCSSPath'] ) ) continue;
			if ( ! $arrTemplate['strCSSPath'] ) continue;
			if ( ! $arrTemplate['fIsActive'] ) continue;
			
			$strFunctionsPath = file_exists( $arrTemplate['strFunctionPath'] )
				? $arrTemplate['strFunctionPath']
				: ( file_exists( dirname( $arrTemplate['strCSSPath'] ) . '/functions.php' )
					? dirname( $arrTemplate['strCSSPath'] ) . '/functions.php'
					: null
				);
			if ( $strFunctionsPath )
				include_once( $strFunctionsPath );
						
		}
		
	}
	
	/**
	 * Includes activated templates' settings.php files.
	 * 
	 * @remark			This is called from the initial loader class.
	 * 
	 */ 
	public function loadSettingsOfActiveTemplates() {
		
		if ( ! is_admin() ) return;
		
		foreach( $this->getActiveTemplates() as $arrTemplate ) {
			
			if ( ! file_exists( $arrTemplate['strCSSPath'] ) ) continue;
			if ( ! file_exists( $arrTemplate['strTemplatePath'] ) ) continue;
			if ( ! $arrTemplate['fIsActive'] ) continue;
			
			$strSettingsPath = $arrTemplate['strSettingsPath'] 
				? $arrTemplate['strSettingsPath']
				: ( file_exists( dirname( $arrTemplate['strCSSPath'] ) . '/settings.php' )
					? dirname( $arrTemplate['strCSSPath'] ) . '/settings.php'
					: null
				);
			if ( $strSettingsPath )
				include_once( $strSettingsPath );
						
		}
	}
	
	public function enqueueActiveTemplateStyles() {
// FetchTweets_Debug::getArray( __METHOD__, dirname( __FILE__ ) . '/loaded.txt' );				

		// This must be called after the option object has been established.
		foreach( $this->getActiveTemplates() as $arrTemplate ) {
			
			if ( ! file_exists( $arrTemplate['strCSSPath'] ) ) continue;
			if ( ! file_exists( $arrTemplate['strTemplatePath'] ) ) continue;
			if ( ! $arrTemplate['fIsActive'] ) continue;
			
			wp_register_style( "fetch-tweets-{$arrTemplate['strSlug']}", FetchTweets_WPUtilities::getSRCFromPath( $arrTemplate['strCSSPath'] ) );		// relative path the WordPress installed path.
			// wp_register_style( "fetch-tweets-{$arrTemplate['strSlug']}", '/' . FetchTweets_Utilities::getRelativePath( ABSPATH, $arrTemplate['strCSSPath'] ) );		// relative path the WordPress installed path.
			// wp_register_style( "fetch-tweets-{$arrTemplate['strSlug']}", site_url() . "?fetch_tweets_style={$arrTemplate['strSlug']}" );
			wp_enqueue_style( "fetch-tweets-{$arrTemplate['strSlug']}" );		
			
		}
		
	}
	
	public function findDefaultTemplateDetails( $strDirPath=null ) {	
		
		// Finds the default template and retrieves the detail information of the template.
		// This is used when no default template is set.

		$strDirPath = isset( $strDirPath ) && $strDirPath
			? $strDirPath
			: FetchTweets_Commons::getPluginDirPath() . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'plain';

		$arrDefaultTemplate = array(
				'fIsActive' => true,	// a default template must be active.
				'fIsDefault' => true,
				'strCSSPath' => $strDirPath . DIRECTORY_SEPARATOR . 'style.css',
				'strDirPath' => $strDirPath,
				'strFunctionPath' => file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'functions.php' ) ? $strDirPath . DIRECTORY_SEPARATOR . 'functions.php' : null,					
				'strTemplatePath' => file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'template.php' ) ? $strDirPath . DIRECTORY_SEPARATOR . 'template.php' : null,					
				'strSettingsPath' => file_exists( $strDirPath . DIRECTORY_SEPARATOR . 'settings.php' ) ? $strDirPath . DIRECTORY_SEPARATOR . 'settings.php' : null,	// this is optional.
				'strThumbnailPath' => $this->getScreenshotPath( $strDirPath ),	// it's not a url.
				'strSlug' => md5( $strDirPath ),			
			) 
			+ $this->getTemplateData( $strDirPath . '/style.css' )
			+ self::$arrStructure_Template;		

// FetchTweets_Debug::getArray( $arrDefaultTemplate, dirname( __FILE__ ) . '/default_template.txt' );		
		return $arrDefaultTemplate;
			
	}
	
	public function getDefaultTemplateSlug() {
		
		$arrDefaultTemplate = empty( $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate'] ) || ! file_exists( $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate']['strCSSPath'] )
			? $this->findDefaultTemplateDetails()
			: $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate'] + self::$arrStructure_Template;
		
		return $arrDefaultTemplate['strSlug'];		
		
	}
	
	public function getDefaultTemplatePath() {
			
		$arrDefaultTemplate = empty( $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate'] ) || ! file_exists( $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate']['strCSSPath'] )
			? $this->findDefaultTemplateDetails()
			: $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate'] + self::$arrStructure_Template;
		
		return $arrDefaultTemplate['strTemplatePath'];		
			
	}
	
	/*
	 * 
	 * */
	protected function getTemplateData( $strPath, $strType='theme' )	{
	
		// Returns an array of template detail information from the given file path.	
		// An alternative to get_plugin_data() as some users change the location of the wp-admin directory.
		$arrData = get_file_data( 
			$strPath, 
			array(
				'strName' => 'Template Name',
				'strTemplateURI' => 'Template URI',
				'strVersion' => 'Version',
				'strDescription' => 'Description',
				'strAuthor' => 'Author',
				'strAuthorURI' => 'Author URI',
				'strTextDomain' => 'Text Domain',
				'strDomainPath' => 'Domain Path',
				'strNetwork' => 'Network',
				// Site Wide Only is deprecated in favour of Network.
				'_sitewide' => 'Site Wide Only',
			),
			$strType	// 'plugin' or 'theme'
		);				
		return $arrData;
		
	}		

}