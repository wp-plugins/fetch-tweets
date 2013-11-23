<?php
/**
	
	Handles the initial set-up for the plugin.
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 * @since		1.3.4			Renamed to FetchTweets_Bootstrap from FetchTweets_InitialLoader
 * 
 
*/

final class FetchTweets_Bootstrap {
	
	function __construct( $strPluginFilePath ) {
	
		$this->strFilePath = $strPluginFilePath;	//FetchTweets_Commons::getPluginPath();
		
		// 1. Define constants.
		// $this->defineConstants();
		
		// 2. Set global variables.
		$this->setGlobals();
		
		// 3. Set up auto-load classes.
		$this->loadClasses( $this->strFilePath );
		
		// 4. Set up activation hook.
		register_activation_hook( $this->strFilePath, array( $this, 'doWhenPluginActivates' ) );
		
		// 5. Set up deactivation hook.
		register_deactivation_hook( $this->strFilePath, array( $this, 'doWhenPluginDeactivates' ) );
		
		// 6. Set up localization.
		$this->localize();
		
		// 7. Schedule to call start up functions when all the plugins get loaded.
		add_action( 'plugins_loaded', array( $this, 'loadPlugin' ), 999, 1 );
			
	}	
	
	// private function defineConstants() {}
	
	private function setGlobals() {
		
		$GLOBALS['oFetchTweets_Option'] = null;	// stores the option object
		$GLOBALS['oFetchTweets_Templates'] = null;	// stores the template object
		
		// Stores custom registering class paths
		$GLOBALS['arrFetchTweets_FinalClasses'] = isset( $GLOBALS['arrFetchTweets_FinalClasses'] ) && is_array( $GLOBALS['arrFetchTweets_FinalClasses'] ) ? $GLOBALS['arrFetchTweets_FinalClasses'] : array();
		$GLOBALS['arrFetchTweets_Classes'] = isset( $GLOBALS['arrFetchTweets_Classes'] ) && is_array( $GLOBALS['arrFetchTweets_Classes'] ) ? $GLOBALS['arrFetchTweets_Classes'] : array();
				
		$GLOBALS['arrFetchTweets_oEmbed'] = array();		
				
	}
	
	private function loadClasses( $strFilePath ) {
		
		$strPluginDir =  dirname( $strFilePath );
		
		// Auto-loads classes placed in the finals folder.
		if ( ! class_exists( 'FetchTweets_RegisterClasses' ) ) 
			include_once( $strPluginDir . '/class_final/FetchTweets_RegisterClasses.php' );		
		
		// Register finalized classes right away.
		$oRC = new FetchTweets_RegisterClasses( $strPluginDir . '/class_final', $GLOBALS['arrFetchTweets_FinalClasses'] );
		$oRC->registerClasses();
		
		// Schedule to register regular classes when all the plugins are loaded. This allows other scripts to modify the loading class files.
		add_action( 'plugins_loaded', array( new FetchTweets_RegisterClasses( $strPluginDir . '/class', $GLOBALS['arrFetchTweets_Classes'] ), 'registerClasses' ) );
		
	}

	public function doWhenPluginActivates() {
		
		// Requirement Check
		$oRequirement = new FetchTweets_Requirements( 
			$this->strFilePath,
			array(
				'php' => array(
					'version' => '5.2.4',
					'error' => 'The plugin requires the PHP version %1$s or higher.',
				),
				'wordpress' => array(
					'version' => '3.3',
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
		);
		$oRequirement->checkRequirements();
	
		// Schedule transient set-ups
		wp_schedule_single_event( time(), 'fetch_tweets_action_setup_transients' );		
		
	}
	
	public function doWhenPluginDeactivates() {
		
		$this->cleanTransients();
		
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
		include_once( dirname( FetchTweets_Commons::getPluginFilePath() ) . '/library/FetchTweets_TwitterOAuth.php' );
		include_once( dirname( FetchTweets_Commons::getPluginFilePath() ) . '/library/admin-page-framework-for-fetch-tweets.php' );

		// 2. Option Object
		$GLOBALS['oFetchTweets_Option'] = new FetchTweets_Option( FetchTweets_Commons::$strAdminKey );

		// 3. Templates
		$GLOBALS['oFetchTweets_Templates'] = new FetchTweets_Templates;		
		$GLOBALS['oFetchTweets_Templates']->loadFunctionsOfActiveTemplates();
		add_action( 'wp_enqueue_scripts', array( $GLOBALS['oFetchTweets_Templates'], 'enqueueActiveTemplateStyles' ) );
		if ( is_admin() )
			$GLOBALS['oFetchTweets_Templates']->loadSettingsOfActiveTemplates();
		
		// 4. Admin pages
		if ( is_admin() ) 
			new FetchTweets_AdminPage( FetchTweets_Commons::$strAdminKey, $this->strFilePath );		
		
		// 5. Post Type
		// Should not use "if ( is_admin() )" for the this class because posts of custom post type can be accessed from the regular pages.
		new FetchTweets_PostType( FetchTweets_Commons::PostTypeSlug, null, $this->strFilePath ); 	// post type slug
		// new FetchTweets_PostType_Accounts( FetchTweets_Commons::PostTypeSlugAccounts, null, $this->strFilePath ); 	// post type slug
		if ( is_admin() ) {
			new FetchTweets_MetaBox_Options(
				'fetch_tweets_options_meta_box',	// meta box ID
				__( 'Options', 'fetch-tweets' ),		// meta box title
				array( FetchTweets_Commons::PostTypeSlug ),	// post, page, etc.
				'normal',
				'default'
			);			
			new FetchTweets_MetaBox_Template(
				'fetch_tweets_template_meta_box',	// meta box ID
				__( 'Template', 'fetch-tweets' ),		// meta box title
				array( FetchTweets_Commons::PostTypeSlug ),	// post, page, etc.
				'normal',
				'default'
			);
			new FetchTweets_MetaBox_Tag;
			new FetchTweets_MetaBox_Misc;
		}
						
		// 6. Shortcode
		new FetchTweets_Shortcode( 'fetch_tweets' );	// e.g. [fetch_tweets id="143"]
			
		// 7. Widgets
		add_action( 'widgets_init', 'FetchTweets_WidgetByID::registerWidget' );
		add_action( 'widgets_init', 'FetchTweets_WidgetByTag::registerWidget' );
				
		// 8. Events
		new FetchTweets_Event;	
		
		// 9. MISC
		if ( is_admin() )
			$GLOBALS['oFetchTweetsUserAds'] = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		
		// 10. WordPress version backward compatibility.
		$this->defineConstantesForBackwardCompatibility();
		
	}

	public function enqueueStyle() {
		
        // Respects SSL, style.css is relative to the current file
        wp_register_style( 'fetch-tweets', plugins_url( '/css/style.css', FetchTweets_Commons::getPluginFilePath() ) );
        wp_enqueue_style( 'fetch-tweets' );
		
    }		

	/**
	 * Defines constants that are not defined in WordPress v3.4.x or below.
	 * 
	 * @since			1.3.0
	 */
	protected function defineConstantesForBackwardCompatibility() {
		
		if ( ! defined( 'MINUTE_IN_SECONDS' ) ) define( 'MINUTE_IN_SECONDS', 60 );
		if ( ! defined( 'HOUR_IN_SECONDS' ) ) define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
		if ( ! defined( 'DAY_IN_SECONDS' ) ) define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
		if ( ! defined( 'WEEK_IN_SECONDS' ) ) define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
		if ( ! defined( 'YEAR_IN_SECONDS' ) ) define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );	

	}
		
	
}