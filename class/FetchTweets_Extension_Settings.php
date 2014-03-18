<?php
/*
 * An abstract class to create extension setting pages.
 * */
abstract class FetchTweets_Extension_Settings {
	
	// Do not modify these properties.
	protected $sParentAdminPaggeClassName = 'FetchTweets_AdminPage';
	
	/*
	 * These must be overridden in the extended class.
	 * */
	protected $sParentPageSlug = '';	// in the url, the ... part in ?page=... 
	protected $sParentTabSlug = '';	// in the url, the ... part in &tab=...
	protected $sExtensionName = '';	// the extension name
	protected $sSectionID = '';	
	
	/*
	 * No need to modify the constructor.
	 * */
	public function __construct() {
		
		// tabs_{class name}_{page slug}
		add_filter( "tabs_" . $this->sParentAdminPaggeClassName . "_" . $this->sParentPageSlug, array( $this, '_replyToAddInPageTab' ) );
		
		// section_{class name}
		add_filter( "sections_" . $this->sParentAdminPaggeClassName, array( $this, 'addSettingSections' ) );
		
		// fields_{class name}
		add_filter( "fields_" . $this->sParentAdminPaggeClassName, array( $this, 'addSettingFields' ) );
		
		// validation_{page slug}_{tab slug}
		add_filter( "validation_{$this->sParentPageSlug}_{$this->sParentTabSlug}", array( $this, 'validateSettings' ), 10, 2 );
			
	}
	
	/*
	 * 	No need to modify these method.
	 * */
	public function _replyToAddInPageTab( $aTabs ) {
	
		return array(
			$this->sParentTabSlug => array(
				'page_slug'	=> $this->sParentPageSlug,
				'title'		=> $this->sExtensionName,
				'tab_slug'	=> $this->sParentTabSlug,
				'order'		=> 20
			)
		) + $aTabs;
		
	}
	
	/*
	 * The following methods should be overridden in the extended class.
	 */
	public function addSettingSections( $aSections ) { return $aSections; }
	public function addSettingFields( $aFields ) { return $aFields; }
	public function validateSettings( $aInput, $aOldInput ) { return $aInput; }
	
}