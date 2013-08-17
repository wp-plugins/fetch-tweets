<?php
/*
 * Modify this extended class.
 * */
class FetchTweets_Template_Settings_Plain extends FetchTweets_Template_Settings_Plain_ {

	/*
	 * Modify these properties.
	 * */
	protected $strParentPageSlug = 'fetch_tweets_templates';	// in the url, the ... part of ?page=... 
	protected $strParentTabSlug = 'plain';	// in the url, the ... part of &tab=...
	protected $strTemplateName = 'Plain';	// the template name
	
	/*
	 * Modify these methods. 
	 * This defines form sections. Set the section ID and the description here.
	 * The array structure follows the rule of Admin Page Framework. ( https://github.com/michaeluno/admin-page-framework )
	 * */
	public function addSettingSections( $arrSections ) {
			
		$arrSections[] = array(
			'strSectionID'		=> 'fetch_tweets_template_plain',
			'strPageSlug'		=> $this->strParentPageSlug,
			'strTabSlug'		=> $this->strParentTabSlug,
			'strTitle'			=> $this->strTemplateName,
			'strDescription'	=> sprintf( 'Options for the %1$s template.', $this->strTemplateName ) . ' ' 
				. __( 'These will be the default values and be overridden by the arguments passed directly by the widgets, the shortcode, or the PHP function.', 'fetch-tweets' ),
		);
		return $arrSections;
	
	}
	/*
	 * This defines form fields. Return the field arrays. 
	 * The array structure follows the rule of Admin Page Framework. ( https://github.com/michaeluno/admin-page-framework )
	 * */
	public function addSettingFields( $arrFields ) {
		
		if ( ! class_exists( 'FetchTweets_Commons' ) ) return $arrFields;	// if the main class does not exist, do nothing.
		
		$arrOptions = get_option( FetchTweets_Commons::AdminOptionKey );

		$intWidth = isset( $arrOptions[ $this->strParentPageSlug ]['fetch_tweets_template_plain']['fetch_tweets_template_plain_width'] )
			? $arrOptions[ $this->strParentPageSlug ]['fetch_tweets_template_plain']['fetch_tweets_template_plain_width']
			: 100;	// default
		$intHeight = isset( $arrOptions[ $this->strParentPageSlug ]['fetch_tweets_template_plain']['fetch_tweets_template_plain_height'] )
			? $arrOptions[ $this->strParentPageSlug ]['fetch_tweets_template_plain']['fetch_tweets_template_plain_height']
			: 400;	// default
		
		$arrFields[] = array(
			'strFieldID' => 'fetch_tweets_template_plain_avatar_size',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Profile Image Size', 'fetch-tweets' ),
			'strDescription' => __( 'The avatar size in pixel.', 'fetch-tweets' ) . ' ' . __( 'Default', 'fetch-tweets' ) . ': 48',
			'strType' => 'number',
			'vSize' => 10,
			'vDefault' => 48, 
		);				
		$arrFields[] = array(
			'strFieldID' => 'fetch_tweets_template_plain_width_unit',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Width', 'fetch-tweets' ),
			'strDescription' => __( 'The width of the output.', 'fetch-tweets' ) . ' ' . __( 'Default', 'fetch-tweets' ) . ': 100%',
			'strType' => 'select',
			'vLabel' => array(
				'%' => '%',
				'px' => 'px',
				'em' => 'em',
			),
			'vDefault' => '%',
			'vBeforeInputTag' => '<span id="fetch_tweets_template_plain_fetch_tweets_template_plain_width"><input id="fetch_tweets_template_plain_fetch_tweets_template_plain_width_0" class="" size="30" type="number" name="fetch_tweets_admin[fetch_tweets_templates][fetch_tweets_template_plain][fetch_tweets_template_plain_width]" value="' .  $intWidth . '" min="" max="" step="" maxlength=""></span>',
		);
		$arrFields[] = array(
			'strFieldID' => 'fetch_tweets_template_plain_height_unit',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Height', 'fetch-tweets' ),
			'strDescription' => __( 'The height of the output.', 'fetch-tweets' ) . ' ' . __( 'Default', 'fetch-tweets' ) . ': 400px',
			'strType' => 'select',
			'vLabel' => array(
				'%' => '%',
				'px' => 'px',
				'em' => 'em',
			),
			'vDefault' => 'px',
			'vBeforeInputTag' => '<span id="fetch_tweets_template_plain_fetch_tweets_template_plain_height"><input id="fetch_tweets_template_plain_fetch_tweets_template_plain_height_0" class="" size="30" type="number" name="fetch_tweets_admin[fetch_tweets_templates][fetch_tweets_template_plain][fetch_tweets_template_plain_height]" value="' .  $intHeight . '" min="" max="" step="" maxlength=""></span>',
		);
		$arrFields[] = array(  // single button
			'strFieldID' => 'fetch_tweets_template_plain_submit',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strType' => 'submit',
			'strBeforeField' => "<div class='right-button'>",
			'strAfterField' => "</div>",
			'vLabelMinWidth' => 0,
			'vLabel' => __( 'Save Changes', 'fetch-tweets' ),
			'vClassAttribute' => 'button button-primary',
		);
		return $arrFields;		
	}
	
	public function validateSettings( $arrInput, $arrOriginal ) {
		
		return $arrInput;
		
	}
	
}
new FetchTweets_Template_Settings_Plain;


/*
 * No need to modify the following class.
 * */
abstract class FetchTweets_Template_Settings_Plain_ {
	
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