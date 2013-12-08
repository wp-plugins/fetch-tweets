<?php
/**
 * Adds a setting tab in the Fetch Tweets admin pages. 
 * 
 * If you are modifying the template to create your own, modify this extended class.
 * The setting arrays follows the specifications of Admin Page Framework v2. 
 * 
 * @package		Fetch Tweets
 * @subpackage	Plain Template
 * @see			http://wordpress.org/plugins/admin-page-framework/
 */
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
			'strDescription'	=> sprintf( __( 'Options for the %1$s template.', 'fetch-tweets' ), $this->strTemplateName ) . ' ' 
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
				
		$arrFields[] = array(
			'strFieldID' => 'fetch_tweets_template_plain_avatar_size',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Profile Image Size', 'fetch-tweets' ),
			'strDescription' => __( 'The avatar size in pixel. Set 0 for no avatar.', 'fetch-tweets' ) . ' ' . __( 'Default', 'fetch-tweets' ) . ': 48',
			'strType' => 'number',
			'vSize' => 10,
			'vDefault' => 48, 
		);	
		$arrFields[] = array(
			'strFieldID' => 'fetch_tweets_template_plain_avatar_position',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Profile Image Position', 'fetch-tweets' ),
			'strType' => 'radio',
			'vLabel' => array(
				'left' => __( 'Left', 'fetch-tweets' ),
				'right' => __( 'Right', 'fetch-tweets' ),
			),
			'vDefault' => 'left', 
		);				
		$arrFields[] = array(	// width
			'strFieldID' => 'fetch_tweets_template_plain_width',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Width', 'fetch-tweets' ),
			'strDescription' => __( 'The width of the output.', 'fetch-tweets' ) . ' ' . __( 'Default', 'fetch-tweets' ) . ': 100%',
			'strType' => 'size',
			'vSizeUnits' => array(
				'%' => '%',
				'px' => 'px',
				'em' => 'em',
			),
			'vDefault' => array(
				'size'	=> 100,
				'unit'	=> '%',
			),
			'vDelimiter' => '<br />',
		);
		$arrFields[] = array(	// height 
			'strFieldID' => 'fetch_tweets_template_plain_height',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Height', 'fetch-tweets' ),
			'strDescription' => __( 'The height of the output.', 'fetch-tweets' ) . ' ' . __( 'Default', 'fetch-tweets' ) . ': 400px',
			'strType' => 'size',
			'vSizeUnits' => array(
				'%' => '%',
				'px' => 'px',
				'em' => 'em',
			),
			'vDefault' => array(
				'size'	=> 400,
				'unit'	=> 'px',
			),
			'vDelimiter' => '<br />',
		);		
		$arrFields[] = array(	// margins
			'strFieldID' => 'fetch_tweets_template_plain_margins',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Margins', 'fetch-tweets' ),
			'strDescription' => __( 'The margins of the output element. Leave them empty not to set any margin.', 'fetch-tweets' ),
			'strType' => 'size',
			'vLabel' => array(
				'top' => __( 'Top', 'fetch-tweets' ),
				'right' => __( 'Right', 'fetch-tweets' ),
				'bottom' => __( 'Bottom', 'fetch-tweets' ),
				'left' => __( 'Left', 'fetch-tweets' ),
			),
			'vSizeUnits' => array( '%' => '%', 'px' => 'px', 'em' => 'em', ),
			'vDelimiter' => '<br />',
		);		
		$arrFields[] = array(	// paddings
			'strFieldID' => 'fetch_tweets_template_plain_paddings',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Paddings', 'fetch-tweets' ),
			'strDescription' => __( 'The paddings of the output element. Leave them empty not to set any padding.', 'fetch-tweets' ),
			'strType' => 'size',
			'vLabel' => array(
				'top' => __( 'Top', 'fetch-tweets' ),
				'right' => __( 'Right', 'fetch-tweets' ),
				'bottom' => __( 'Bottom', 'fetch-tweets' ),
				'left' => __( 'Left', 'fetch-tweets' ),
			),
			'vSizeUnits' => array( '%' => '%', 'px' => 'px', 'em' => 'em', ),
			'vDelimiter' => '<br />',
		);		
						
		$arrFields[] = array(	// background color
			'strFieldID' => 'fetch_tweets_template_plain_background_color',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Background Color', 'fetch-tweets' ),
			'strType' => 'color',
			'vDefault' => 'transparent',
		);		
		$arrFields[] = array(
			'strFieldID' => 'fetch_tweets_template_plain_intent_buttons',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Intent Buttons', 'fetch-tweets' ),
			'strDescription' => __( 'These are for Favourite, Reply, and Retweet buttons.', 'fetch-tweets' ),
			'strType' => 'radio',
			'vLabel' => array(  
				1 => __( 'Both icons and text', 'fetch-tweets' ),
				2 => __( 'Only icons', 'fetch-tweets' ),
				3 => __( 'Only text', 'fetch-tweets' ),
			),
			'vDefault' => 2,
		);
		$arrFields[] = array(
			'strFieldID' => 'fetch_tweets_template_plain_intent_script',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Intent Button Script', 'fetch-tweets' ),
			'strType' => 'checkbox',
			'vLabel' => __( 'Insert the intent button script that enables a pop-up window for Favorite, Reply, and Retweet.', 'fetch-tweets' ),
			'vDefault' => 1,
		);
		$arrFields[] = array(	// visibilities
			'strFieldID' => 'fetch_tweets_template_plain_visibilities',
			'strSectionID' => 'fetch_tweets_template_plain',
			'strTitle' => __( 'Visibilities', 'fetch-tweets' ),
			'strType' => 'checkbox',
			'vLabel' => array(
				'avatar'			=> __( 'Profile Image', 'fetch-tweets' ),
				'user_name'			=> __( 'User Name', 'fetch-tweets' ),
				// 'follow_button' => __( 'Follow Button', 'fetch-tweets' ),
				// 'user_description' => __( 'User Description', 'fetch-tweets' ),
				'time'				=> __( 'Time', 'fetch-tweets' ),
				'intent_buttons'	=> __( 'Intent Buttons', 'fetch-tweets' ),
			),
			'vDefault' => array(
				'avatar'			=> true,
				'user_name'			=> true,
				// 'follow_button' => true,
				// 'user_description' => true,
				'time'				=> true,
				'intent_buttons'	=> true,
			),
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