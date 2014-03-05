<?php
/*
 * No need to modify the following class.
 * */
abstract class FetchTweets_Template_Settings {
	
	// Do not modify these properties.
	protected $strParentAdminPaggeClassName = 'FetchTweets_AdminPage';
	protected $strTemplateID = '';	// assigned in the constructor.
	
	/*
	 * These must be overridden in the extended class.
	 * */
	protected $strParentPageSlug = '';	// in the url, the ... part in ?page=... 
	protected $strParentTabSlug = '';	// in the url, the ... part in &tab=...
	protected $strTemplateName = '';	// the template name
	
	/*
	 * No need to modify the constructor.
	 * */
	public function __construct() {
		
		$this->strTemplateID = md5( dirname( __FILE__ ) );
		
		// "{$this->oProps->strClassName}_{$strPageSlug}_tabs",
		add_filter( $this->strParentAdminPaggeClassName . "_" . $this->strParentPageSlug . "_tabs", array( $this, 'addInPageTab' ) );
		
		// "{$this->oProps->strClassName}_setting_sections",
		add_filter( $this->strParentAdminPaggeClassName . "_setting_sections", array( $this, 'addSettingSections' ) );
		
		// "{$this->oProps->strClassName}_setting_fields",
		add_filter( $this->strParentAdminPaggeClassName . "_setting_fields", array( $this, 'addSettingFields' ) );
		
		// validation_{page slug}_{tab slug}
		add_filter( "validation_{$this->strParentPageSlug}_{$this->strParentTabSlug}", array( $this, 'validateSettings' ), 10, 2 );
			
		// Adds the Settings link in the template listing table.
		add_filter( 'fetch_tweets_filter_template_listing_table_action_links', array( $this, 'addSettingsLink' ), 10, 2 );
		
	}
	
	/*
	 * 	No need to modify these method.
	 * */
	public function addInPageTab( $arrTabs ) {
	
		return array(
			$this->strParentTabSlug => array(
				'strPageSlug'	=> $this->strParentPageSlug,
				'strTitle'		=> $this->strTemplateName,
				'strTabSlug'	=> $this->strParentTabSlug,
				'numOrder'		=> 20
			)
		) + $arrTabs;
		
	}
	public function addSettingsLink( $arrLinks, $strTemplateID ) {
				
		if ( $strTemplateID != $this->strTemplateID ) return $arrLinks;

		array_unshift(	
			$arrLinks,
			"<a href='?post_type=" . FetchTweets_Commons::PostTypeSlug . "&page={$this->strParentPageSlug}&tab={$this->strParentTabSlug}'>" . __( 'Settings', 'fetch-tweets' ) . "</a>" 
		); 
		return $arrLinks;			
		
	}
	
}