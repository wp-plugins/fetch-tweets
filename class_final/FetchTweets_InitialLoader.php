<?php
/**
	
	Handles the initial set-up for the plugin.
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 
*/

final class FetchTweets_InitialLoader {
	
	function __construct( $strPluginFilePath ) {
	
		$this->strFilePath = $strPluginFilePath;	//FetchTweets_Commons::getPluginPath();
		
		// 1. Define constants.
		// $this->defineConstants();
		
		// 2. Set global variables if there are.
		$this->setGlobals();
		
		// 3. Set up auto-load classes.
		$this->loadClasses( $this->strFilePath );
		
		// 4. Set up activation hook.
		$this->doWhenPluginActivates();
		
		// 5. Set up deactivation hook.
		$this->doWhenPluginDeactivates();
		
		// 6. Set up localization.
		$this->localize();
		
		// 7. Schedule to call start up functions when all the plugins get loaded.
		add_action( 'plugins_loaded', array( $this, 'loadPlugin' ), 999, 1 );
			
	}	
	
	// private function defineConstants() {}
	
	private function setGlobals() {
		
		global $oFetchTweets_Option;
				
	}
	
	private function loadClasses( $strFilePath ) {
		
		// Auto-loads classes placed in the finals folder.
		if ( ! class_exists( 'FetchTweets_RegisterClasses' ) ) 
			include_once( dirname( $strFilePath ) . '/class_final/FetchTweets_RegisterClasses.php' );		
		
		// Register finalized classes right away.
		$oRC = new FetchTweets_RegisterClasses( dirname( $strFilePath ) . '/class_final' );
		$oRC->registerClasses();
		
		// Register regular classes when all the plugins are loaded. This allows other scripts to modify the loading class files.
		add_action( 'plugins_loaded', array( new FetchTweets_RegisterClasses( dirname( $strFilePath ) . '/class' ), 'registerClasses' ) );
		
	}

	private function doWhenPluginActivates() {
		
		register_activation_hook( 
			$this->strFilePath,
			array( 
				new FetchTweets_Requirements( 
					$this->strFilePath,
					array(
						'php' => array(
							'version' => '5.2.4',
							'error' => 'The plugin requires the PHP version %1$s or higher.',
						),
						'wordpress' => array(
							'version' => '3.2',
							'error' => 'The plugin requires the WordPress version %1$s or higher.',
						),
						'functions' => array(
							'curl_version' => sprintf( __( 'The plugin requires the %1$s to be installed.', 'fetch-tweets' ), 'the cURL library' ),
						),
						// 'classes' => array(
							// 'DOMDocument' => sprintf( __( 'The plugin requires the <a href="%1$s">libxml</a> extension to be activated.', 'pseudo-image' ), 'http://www.php.net/manual/en/book.libxml.php' ),
						// ),
						'constants'	=> array(),
					),
					True, 			// if it fails it will deactivate the plugin
					null			// do not hook
				),
				'checkRequirements'
			)
		);		
		
		register_activation_hook( $this->strFilePath, array( $this, 'scheduleSetupTransients' ) );
		
	}
	public function scheduleSetupTransients() {	// for the activation hook.
		wp_schedule_single_event( time(), 'FTWS_action_setup_transients' );		
	}
	
	private function doWhenPluginDeactivates() {
		
		register_deactivation_hook( 
			$this->strFilePath,
			array( $this, 'cleanTransients' )
		);
		
	}	
	public function cleanTransients( $arrPrefixes=array( 'FTWS' ) ) {	// for the deactivation hook.

		// Delete transients
		global $wpdb, $table_prefix;
		
		// This method also serves for the deactivation callback and in that case, an empty value is passed to the first parameter.
		$arrPrefixes = empty( $arrPrefixes ) ? array( 'FTWS', 'FTWSFeedMs' ) : $arrPrefixes;		// 'FTWSAds'
		
		foreach( $arrPrefixes as $strPrefix ) {
			$wpdb->query( "DELETE FROM `" . $table_prefix . "options` WHERE `option_name` LIKE ( '_transient_%{$strPrefix}%' )" );
			$wpdb->query( "DELETE FROM `" . $table_prefix . "options` WHERE `option_name` LIKE ( '_transient_timeout_%{$strPrefix}%' )" );
		}
	
	}
	
	private function localize() {
		
		load_plugin_textdomain( 
			FetchTweets_Commons::TextDomain, 
			false, 
			dirname( plugin_basename( $this->strFilePath ) ) . '/language/'
		);
		
		if ( is_admin() ) 
			load_plugin_textdomain( 
				'admin-page-framework', 
				false, 
				dirname( plugin_basename( $this->strFilePath ) ) . '/language/'
			);		
		
	}		
	
	public function loadPlugin() {
		
		// All the necessary classes have been already loaded.
		
		// 1. Load Necessary libraries
		include_once( dirname( FetchTweets_Commons::getPluginFilePath() ) . '/library/WP_TwitterOAuth.php' );
		include_once( dirname( FetchTweets_Commons::getPluginFilePath() ) . '/library/admin-page-framework.php' );

		// 2. Option Object
		$GLOBALS['oFetchTweets_Option'] = new FetchTweets_Option( FetchTweets_Commons::$strAdminKey );
			
		// 3. Admin pages
		if ( is_admin() ) 
			new FetchTweets_AdminPage( FetchTweets_Commons::$strAdminKey, $this->strFilePath );		
		
		// 4. Post Type
		// Should not use "if ( is_admin() )" for the this class because posts of custom post type can be accessed from the regular pages.
		new FetchTweets_PostType( FetchTweets_Commons::PostTypeSlug, null, $this->strFilePath ); 	// post type slug
		if ( is_admin() )
			new FetchTweets_MetaBox(
				'fetch_tweets_options_meta_box',	// meta box ID
				__( 'Options', 'fetch-tweets' ),		// meta box title
				array( FetchTweets_Commons::PostTypeSlug ),	// post, page, etc.
				'normal',
				'default'
			);			
			
		// 5. Plugin CSS
		if ( ! is_admin() )
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueueStyle' ) );
			
		// 6. Shortcode
		new FetchTweets_Shortcode( 'fetch_tweets' );	// e.g. [fetch_tweets id="143"]
	
		// 7. Widget
		add_action( 'widgets_init', 'FetchTweets_WidgetByID::registerWidget' );
		add_action( 'widgets_init', 'FetchTweets_WidgetByTag::registerWidget' );
		
		// 8. Events
		new FetchTweets_Event;	
		
		// 9. MISC
		if ( is_admin() )
			$GLOBALS['oFetchTweetsUserAds'] = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		
	}

	public function enqueueStyle() {
		
        // Respects SSL, style.css is relative to the current file
        wp_register_style( 'fetch-tweets', plugins_url( '/css/style.css', FetchTweets_Commons::getPluginFilePath() ) );
        wp_enqueue_style( 'fetch-tweets' );
		
    }		

	
}