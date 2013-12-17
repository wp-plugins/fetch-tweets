<?php
abstract class FetchTweets_AdminPage_ extends FetchTweets_AdminPageFramework {

	public function start_FetchTweets_AdminPage() {
		
		// Set the option property.
		$this->oOption = & $GLOBALS['oFetchTweets_Option'];
		
		// Disable object caching in the plugin pages and the options.php (the page that stores the settings)
		if ( 
			is_admin() 
			&& (
				$GLOBALS['pagenow'] == 'options.php'
				|| isset( $_GET['post_type'] ) && ( $_GET['post_type'] == FetchTweets_Commons::PostTypeSlug || $_GET['post_type'] == FetchTweets_Commons::PostTypeSlugAccounts ) )
			)
		{
			// wp_suspend_cache_addition( true );	//<-- this causes too many database queries so comment it out
			$GLOBALS['_wp_using_ext_object_cache'] = false;	// this helps some caching plugins not to prevent the settings not to be saved
		}		
		
		// For the list table bulk actions. The WP_List_Table class does not set the post type query string in the redirected page.
		// http://.../wp-admin/edit.php?page=fetch_tweets_templates&tab=&_wpnonce=ebed1d5343&_wp_http_referer=%2Fwp360%2Fwp-admin%2Fedit.php%3Fpost_type%3Dfetch_tweets%26page%3Dfetch_tweets_templates&action=activate&paged=1&action2=-1
		if ( 
			( isset( $_POST['post_type'] ) && $_POST['post_type'] == FetchTweets_Commons::PostTypeSlug )	// the form is submitted 
			&& ( ! isset( $_GET['post_type'] ) )	// and post_type query string is not in the url
			&& ( isset( $_GET['page'] ) && $_GET['page'] == 'fetch_tweets_templates' ) // and the page is the template listing table page,
		)
			die( wp_redirect( add_query_arg( array( 'post_type' => FetchTweets_Commons::PostTypeSlug ) + $_GET, admin_url() . '' . $GLOBALS['pagenow'] ) ) );
	
		// Prepare the template array for the template listing table
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'fetch_tweets_templates' ) 
			add_action( 'admin_menu', array( $this, 'processBulkActionForTemplateListTable' ) );			
		
	}
	public function processBulkActionForTemplateListTable() {

// FetchTweets_Debug::getArray( $GLOBALS['oFetchTweets_Templates']->getUploadedTemplates(), dirname( __FILE__ ) .'/uploaded_templates.txt' );
		$this->oTemplateListTable = new FetchTweets_ListTable(
			$GLOBALS['oFetchTweets_Templates']->getActiveTemplates() + $GLOBALS['oFetchTweets_Templates']->getUploadedTemplates(),
			$GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug()
		);
		$this->oTemplateListTable->process_bulk_action();
		
	}

    public function setUp() {
    	
		// Show the warning message if the authentication key is not set.
		$this->checkAPIKeys(); 
	
		if ( isset( $this->oProps->arrOptions['fetch_tweets_settings']['capabilities']['setting_page_capability'] ) 
			&& ! empty( $this->oProps->arrOptions['fetch_tweets_settings']['capabilities']['setting_page_capability'] )
		)	
			$this->setCapability( $this->oProps->arrOptions['fetch_tweets_settings']['capabilities']['setting_page_capability'] );
	
		$this->setRootMenuPageBySlug( 'edit.php?post_type=fetch_tweets' );
		$this->addSubMenuItems(
			array(
				'strMenuTitle' => __( 'Add Rule by User Name', 'fetch-tweets' ),
				'strType' => 'link',
				'strURL' => 'post-new.php?post_type=fetch_tweets&tweet_type=screen_name',
				'fShowPageHeadingTab' => false,
				'numOrder' => 1,
			),
			// array(
				// 'strMenuTitle' => __( 'Add Rule by Timeline', 'fetch-tweets' ),
				// 'strType' => 'link',
				// 'strURL' => 'post-new.php?post_type=fetch_tweets&tweet_type=timeline',
				// 'fShowPageHeadingTab' => false,
			// ),			
			array(
				'strMenuTitle' => __( 'Add Rule by Search', 'fetch-tweets' ),
				'strType' => 'link',				
				'strURL' => 'post-new.php?post_type=fetch_tweets&tweet_type=search',
				'fShowPageHeadingTab' => false,
			),			
			array(
				'strPageTitle'	=> __( 'Add Rule by List', 'fetch-tweets' ),
				'strPageSlug'	=> 'fetch_tweets_add_rule_by_list',
				'strScreenIcon'	=> FetchTweets_Commons::getPluginURL( "/image/screen_icon_32x32.png" ),
			),			
			// array(	// since 1.3.4
				// 'strPageTitle'	=> __( 'Add New Account', 'fetch-tweets' ),
				// 'strPageSlug'	=> 'fetch_tweets_add_new_account',
				// 'strScreenIcon'	=> FetchTweets_Commons::getPluginURL( "/image/screen_icon_32x32.png" ),
				// 'fShowInMenu' => false,
			// ),				
			// array(	// since 1.3.4
				// 'strMenuTitle'		=> __( 'Manage Accounts', 'amazon-auto-links' ),
				// 'strURL'			=> admin_url( 'edit.php?post_type=' . FetchTweets_Commons::PostTypeSlugAccounts ),
			// ),				
			array(
				'strPageTitle'	=> __( 'Settings', 'fetch-tweets' ),
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strScreenIcon'	=> FetchTweets_Commons::getPluginURL( "/image/screen_icon_32x32.png" ),
			),
			array(
				'strPageTitle' => __( 'Extensions', 'fetch-tweets' ),
				'strPageSlug' => 'fetch_tweets_extensions',
				'strScreenIcon'	=> FetchTweets_Commons::getPluginURL( "/image/screen_icon_32x32.png" ),
			),			
			array(
				'strPageTitle' => __( 'Templates', 'fetch-tweets' ),
				'strPageSlug' => 'fetch_tweets_templates',
				'strScreenIcon'	=> FetchTweets_Commons::getPluginURL( "/image/screen_icon_32x32.png" ),
			)
		);
		$this->addInPageTabs(
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'authentication',
				'strTitle'		=> __( 'Authentication', 'fetch-tweets' ),
				'strParentTabSlug' => 'twitter_connect',
				'fHide'			=> true,	
			),
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'twitter_clear_session',
				'strTitle'		=> 'Clear Session',
				'fHide'			=> true,
			),
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'twitter_connect',
				'strTitle'		=> __( 'Authentication', 'fetch-tweets' ),
				'numOrder'		=> 1,				
				// 'fHide'			=> true,
			),			
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'twitter_redirect',
				'strTitle'		=> 'Redirect',
				'fHide'			=> true,
			),					
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'twitter_callback',
				'strTitle'		=> 'Callback',
				'fHide'			=> true,
			),								
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'general',
				'strTitle'		=> __( 'General', 'fetch-tweets' ),
				'numOrder'		=> 2,				
			),				
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'misc',
				'strTitle'		=> __( 'Misc', 'fetch-tweets' ),
				'numOrder'		=> 3,				
			),			
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'reset',
				'strTitle'		=> __( 'Reset', 'fetch-tweets' ),
				'numOrder'		=> 4,				
			)					
		);
		$this->addInPageTabs(
			array(
				'strPageSlug'	=> 'fetch_tweets_extensions',
				'strTabSlug'	=> 'get_extensions',
				'strTitle'		=> __( 'Get Extensions', 'fetch-tweets' ),
				'numOrder'		=> 10,				
			)		
		);
		$this->addInPageTabs(
			array(
				'strPageSlug'	=> 'fetch_tweets_templates',
				'strTabSlug'	=> 'list_template_table',
				'strTitle'		=> __( 'Installed Templates', 'fetch-tweets' ),
				'numOrder'		=> 1,				
			),
			array(
				'strPageSlug'	=> 'fetch_tweets_templates',
				'strTabSlug'	=> 'get_templates',
				'strTitle'		=> __( 'Get Templates', 'fetch-tweets' ),
				'numOrder'		=> 10,				
			)			
			// array(
				// 'strPageSlug'	=> 'fetch_tweets_settings',
				// 'strTabSlug'	=> 'management',
				// 'strTitle'		=> __( 'Management', 'fetch-tweets' ),
			// )
		);		
		
		/*
		 * Page Styling
		 */
		$this->showPageHeadingTabs( false );		// disables the page heading tabs by passing false.
		$this->setInPageTabTag( 'h2' );				
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/css/admin.css' ) );
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/css/fetch_tweets_templates.css' ), 'fetch_tweets_templates' );
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/css/fetch_tweets_settings.css' ), 'fetch_tweets_settings' );
	 
		/*
		 * Form Elements
		 */
		$this->addSettingSections(
			array(
				'strSectionID'		=> 'twitter_connect',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'twitter_connect',
				'strTitle'			=> __( 'Authenticate', 'fetch-tweets' ),
			),		
			array(
				'strSectionID'		=> 'authentication_keys',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'authentication',
				'strTitle'			=> __( 'Authentication Keys', 'fetch-tweets' ),
				'strDescription'	=> __( 'These keys are required to process oAuth requests of the twitter API.', 'fetch-tweets' ),
			),
			array(
				'strSectionID'		=> 'default_values',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'general',
				'strTitle'			=> __( 'Default Values', 'fetch-tweets' ),
				'strHelp'			=> __( 'Set the default option values which will be applied when the argument values are not set.', 'fetch-tweets' )
										. __( 'These values will be overridden by the argument set directly to the widget options or shortcode.', 'fetch-tweets' ),
			),			
			array(
				'strSectionID'		=> 'cache_settings',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'general',
				'strTitle'			=> __( 'Cache Settings', 'fetch-tweets' ),
			),			
			array(
				'strSectionID'		=> 'capabilities',
				'strCapability'		=> 'manage_options',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'misc',
				'strTitle'			=> __( 'Access Rights', 'fetch-tweets' ),
				'strDescription'	=> __( 'Set the access levels to the plugin setting pages.', 'fetch-tweets' ),
			),			
			array(
				'strSectionID'		=> 'reset_settings',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strCapability'		=> 'manage_options',
				'strTabSlug'		=> 'reset',
				'strTitle'			=> __( 'Reset Settings', 'fetch-tweets' ),
				'strDescription'	=> __( 'If you get broken options, initialize them by performing reset.', 'fetch-tweets' ),
			),
			array(
				'strSectionID'		=> 'caches',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'reset',
				'strTitle'			=> __( 'Caches', 'fetch-tweets' ),
				'strDescription'	=> __( 'If you need to refresh the fetched tweets, clear the cashes.', 'fetch-tweets' ),
			)			
		);	
		$this->addSettingSections(
			array(
				'strSectionID'		=> 'add_rule_by_list',
				'strPageSlug'		=> 'fetch_tweets_add_rule_by_list',
				'strTitle'			=> __( 'Specify the Screen Name', 'fetch-tweets' ),
				'strDescription'	=> __( 'In order to select list, the user name(screen name) of the account that owns the list must be specified.', 'fetch-tweets' ),
			)		
		);
		
		// Add setting fields
 		$this->addSettingFields(
			array(	
				'strFieldID' => 'connect_to_twitter',
				'strSectionID' => 'twitter_connect',
				'strTitle' => __( 'Connect to Twitter', 'fetch-tweets' ),
				'vLabel' => __( 'Connect', 'fetch-tweets' ),
				'vLink' => add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_redirect' ), admin_url( $GLOBALS['pagenow'] ) ),
				'strType' => 'submit',
			),	
			// array(	
				// 'strFieldID' => 'disconnect_from_twitter',
				// 'strSectionID' => 'twitter_connect',
				// 'strTitle' => '',
				// 'fIf' => $this->oOption->isAuthKeysAutomaticallySet(),
				// 'vLabel' => array( 
					// 'disconnect' => __( 'Disconnect from Twitter', 'fetch-tweets' ),
				// ),
				// 'vClassAttribute' => array(
					// 'disconnect' => 'button button-secondary',
				// ),
				// 'vLink' => array(
					// 'disconnect' => add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_connect' ), admin_url( $GLOBALS['pagenow'] ) ),
				// ),
				// 'strType' => 'submit',
			// ),	
			
			array(	
				'strFieldID' => 'manual_authentication',
				'strSectionID' => 'twitter_connect',
				'strTitle' => __( 'Manual', 'fetch-tweets' ),
				'vClassAttribute' => 'button button-secondary',
				'vLabel' => __( 'Set Keys Manually', 'fetch-tweets' ),
				'vLink' => add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication', 'settings-updated' => false ) ),
				'strType' => 'submit',
			)			
		); 
		$this->addSettingFields(
			array(	
				'strFieldID' => 'consumer_key',
				'strSectionID' => 'authentication_keys',
				'strTitle' => __( 'Consumer Key', 'fetch-tweets' ),
				'strType' => 'text',
				'vSize' => 80,
			),
			array(	
				'strFieldID' => 'consumer_secret',
				'strSectionID' => 'authentication_keys',
				'strTitle' => __( 'Consumer Secret', 'fetch-tweets' ),
				'strType' => 'text',
				'vSize' => 80,
			),
			array(	
				'strFieldID' => 'access_token',
				'strSectionID' => 'authentication_keys',
				'strTitle' => __( 'Access Token', 'fetch-tweets' ),
				'strType' => 'text',
				'vSize' => 80,
			),
			array(	
				'strFieldID' => 'access_secret',
				'strSectionID' => 'authentication_keys',
				'strTitle' => __( 'Access Secret', 'fetch-tweets' ),
				'strType' => 'text',
				'vSize' => 80,
				'strAfterField' => '<p class="description">' 
					. sprintf( __( 'You can obtain those keys by logging in to <a href="%1$s" target="_blank">Twitter Developers</a>', 'fetch-tweets' ), 'https://dev.twitter.com/apps' )
					. '</p>',
			),
			array(  // single button
				'strFieldID' => 'submit_authentication_keys',
				'strSectionID' => 'authentication_keys',
				'strType' => 'submit',
				'strBeforeField' => "<div class='right-button'>",
				'strAfterField' => "</div>",
				'vLabelMinWidth' => 0,
				'vLabel' => __( 'Authenticate', 'fetch-tweets' ),
				'vClassAttribute' => 'button button-primary',
			)		
		);
		// default_values
		$this->addSettingFields(
			array(
				'strFieldID' => 'count',
				'strSectionID' => 'default_values',
				'strTitle' => __( 'Number of Items', 'fetch-tweets' ),
				'strHelp' => __( 'The number of tweets to display.', 'fetch-tweets' )
					. __( 'Default', 'fetch-tweets' ) . ': ' . $GLOBALS['oFetchTweets_Option']->arrStructure_DefaultParams['count']
					. __( 'This option corresponds to the <code>count</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ),
				'vDefault'	=> $GLOBALS['oFetchTweets_Option']->arrStructure_DefaultParams['count'],
				'strType' => 'number',
			),
			array(
				'strFieldID'		=> 'twitter_media',
				'strSectionID' => 'default_values',
				'strTitle'			=> __( 'Twitter Media', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
				'vLabel'			=> __( 'Display media images posted in the tweet that are recognized as media file by Twitter.' ),
				'strHelp'	=> __( 'This option corresponds to the <code>twitter_media</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ) . ' '
					. __( 'Currently only photos are supported by the Twitter API.' ),
				'vDefault'			=> $GLOBALS['oFetchTweets_Option']->arrStructure_DefaultParams['twitter_media'],
			),
			array(
				'strFieldID'		=> 'external_media',
				'strSectionID' => 'default_values',
				'strTitle'			=> __( 'External Media', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
				'vLabel'			=> __( 'Replace media links of external sources to an embedded element.', 'fetch-tweets' ),
				'strHelp'			=> __( 'This option corresponds to the <code>external_media</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ) . ' '
					. __( 'Unlike the above media images, there are media links that are not categorized as media by the Twitter API. Thus, enabling this option will attempt to replace them to the embedded elements.', 'fetch-tweets' ) . ' e.g. youtube, vimeo, dailymotion etc.',
				'vDefault'			=> $GLOBALS['oFetchTweets_Option']->arrStructure_DefaultParams['external_media'],
			),					
			array(  // single button
				'strFieldID' => 'submit_default_values',
				'strSectionID' => 'default_values',
				'strType' => 'submit',
				'strBeforeField' => "<div class='right-button'>",
				'strAfterField' => "</div>",
				'vLabelMinWidth' => 0,
				'vLabel' => __( 'Save Changes', 'fetch-tweets' ),
				'vClassAttribute' => 'button button-primary',
			)			
		);
		$this->addSettingFields(
			array(
				'strFieldID'		=> 'cache_for_errors',
				'strSectionID' 		=> 'cache_settings',
				'strTitle'			=> __( 'Cache for Errors', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
				'vLabel'			=> __( 'Cache fetched results even for an error. This reduces the chances to reach the Twitter API rate limit.', 'fetch-tweets' ),
			),
			array(  // single button
				'strFieldID' => 'submit_cache_settings',
				'strSectionID' => 'cache_settings',
				'strType' => 'submit',
				'strBeforeField' => "<div class='right-button'>",
				'strAfterField' => "</div>",
				'vLabelMinWidth' => 0,
				'vLabel' => __( 'Save Changes', 'fetch-tweets' ),
				'vClassAttribute' => 'button button-primary',
			)		
		);
		$this->addSettingFields(
			array(
				'strFieldID' => 'setting_page_capability',
				'strSectionID' => 'capabilities',
				'strTitle' => __( 'Capability', 'fetch-tweets' ),
				'strDescription' => __( 'Select the user role that is allowed to access the plugin setting pages.', 'fetch-tweets' )
					. __( 'Default', 'fetch-tweets' ) . ': ' . __( 'Administrator', 'fetch-tweets' ),
				'strType' => 'select',
				'strCapability' => 'manage_options',
				'vLabel' => array(						
					'manage_options' => __( 'Administrator', 'responsive-column-widgets' ),
					'edit_pages' => __( 'Editor', 'responsive-column-widgets' ),
					'publish_posts' => __( 'Author', 'responsive-column-widgets' ),
					'edit_posts' => __( 'Contributor', 'responsive-column-widgets' ),
					'read' => __( 'Subscriber', 'responsive-column-widgets' ),
				),
			),
			array(  // single button
				'strFieldID' => 'submit_misc',
				'strSectionID' => 'capabilities',
				'strType' => 'submit',
				'strBeforeField' => "<div class='right-button'>",
				'strAfterField' => "</div>",
				'vLabelMinWidth' => 0,
				'vLabel' => __( 'Save Changes', 'fetch-tweets' ),
				'vClassAttribute' => 'button button-primary',
			)			
		);
		$this->addSettingFields(
			array(	
				'strFieldID' => 'option_sections',
				'strSectionID' => 'reset_settings',
				'strTitle' => __( 'Options to Delete', 'fetch-tweets' ),
				'strType' => 'checkbox',
				'vDelimiter' => '<br />',
				'vLabel' => array(
					'all' => __( 'Reset', 'fetch-tweets' ), 
					// the followings are not supported yet
					// 'general' => __( 'General options', 'fetch-tweets' ), 	
					// 'template' => __( 'Template related options', 'fetch-tweets' ),
				),
			),
			// array(  // single button
				// 'strFieldID' => 'submit_reset_settings',
				// 'strSectionID' => 'reset_settings',
				// 'strType' => 'submit',
				// 'strBeforeField' => "<div class='right-button'>",
				// 'strAfterField' => "</div>",
				// 'vLabelMinWidth' => 0,
				// 'vLabel' => __( 'Perform', 'fetch-tweets' ),
				// 'vClassAttribute' => 'button button-primary',
			// ),
			array(	
				'strFieldID' => 'clear_caches',
				'strSectionID' => 'caches',
				'strTitle' => __( 'Clear Caches', 'fetch-tweets' ),
				'strType' => 'checkbox',
				'vLabel' => __( 'Clear tweet caches', 'fetch-tweets' ),
			),
			array(  // single button
				'strFieldID' => 'submit_reset_settings',
				'strSectionID' => 'caches',
				'strType' => 'submit',
				'strBeforeField' => "<div class='right-button'>",
				'strAfterField' => "</div>",
				'vLabelMinWidth' => 0,
				'vLabel' => __( 'Perform', 'fetch-tweets' ),
				'vClassAttribute' => 'button button-primary',
			)			

		);
		$this->addSettingFields(
			array(	
				'strFieldID' => 'list_owner_screen_name',
				'strSectionID' => 'add_rule_by_list',
				'strTitle' => __( 'Owner Screen Name', 'fetch-tweets' ),
				'strDescription' => __( 'The screen name(user name) that owns the list.', 'fetch-tweets' ) . '<br />'
					. 'e.g. miunosoft',
				'strType' => 'text',
				'vValue' => '',
				'vSize' => 40,
			),		
			array(  // single button
				'strFieldID' => 'list_proceed',
				'strSectionID' => 'add_rule_by_list',
				'strType' => 'submit',
				'strBeforeField' => "<div class='right-button'>",
				'strAfterField' => "</div>",
				'vLabelMinWidth' => 0,
				'vLabel' => __( 'Proceed', 'fetch-tweets' ),
				// 'vLink'	=> admin_url(),
				// 'vLink'	=> admin_url( 'post-new.php?post_type=fetch_tweets&tweet_type=list' ),
				// 'vRedirect'	=> admin_url(),
				// 'vRedirect'	=> admin_url( 'post-new.php?post_type=fetch_tweets&tweet_type=list' ),
				'vClassAttribute' => 'button button-primary',
			)		
		);
		$this->addLinkToPluginDescription(  
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4UJHETVAZX34">' . __( 'Donate', 'fetch-tweets' ) . '</a>',
			'<a href="http://en.michaeluno.jp/contact/custom-order/?lang=' . ( WPLANG ? WPLANG : 'en' ) . '">' . __( 'Order custom plugin', 'fetch-tweets' ) . '</a>'
		);						
		
	}
	protected function checkAPIKeys() {
		
		if ( $this->oOption->isAuthKeysManuallySet() || $this->oOption->isAuthKeysAutomaticallySet() ) return;

		add_action( 'admin_notices', array( $this, 'showAdminNotice' ) );
		
	}
	public function showAdminNotice() {
			
		if ( ! (
			( isset( $_GET['page'] ) && $this->oProps->isPageAdded( $_GET['page'] ) ) 
			|| ( isset( $_GET['post_type'] ) && $_GET['post_type'] == FetchTweets_Commons::PostTypeSlug )
		) ) return; 
		
		// http://.../wp-admin/edit.php?post_type=fetch_tweets&page=fetch_tweets_settings
		$strSettingPageURL = add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_redirect' ), admin_url( 'edit.php' ) ); 
		echo "<div class='error'>"
				. "<p>" 
					. "<strong>" . FetchTweets_Commons::PluginName . "</strong>: "
					. sprintf( __( '<a href="%1$s">The API authentication keys need to be set</a> in order to use this plugin.', 'fetch-tweets' ), $strSettingPageURL ) 
				. "</p>"
			. "</div>";		
			
	}
	
	/*
	 * Customize the Menu
	 */
	public function buildMenus() {
	
		parent::buildMenus();
		
		// Remove the default post type menu item.
		$strPageSlug = $this->oProps->arrRootMenu['strPageSlug'];
		foreach ( $GLOBALS['submenu'][ $strPageSlug ] as $intIndex => $arrSubMenu ) {
						
			if ( ! isset( $arrSubMenu[ 2 ] ) ) continue;
			
			// Remove the default Add New entry.
			if ( $arrSubMenu[ 2 ] == 'post-new.php?post_type=' . FetchTweets_Commons::PostTypeSlug ) {
				unset( $GLOBALS['submenu'][ $strPageSlug ][ $intIndex ] );
				continue;
			}
			
			// Copy and remove the Tag menu element to change the position. 
			if ( $arrSubMenu[ 2 ] == 'edit-tags.php?taxonomy=' . FetchTweets_Commons::TagSlug . '&amp;post_type=' . FetchTweets_Commons::PostTypeSlug ) {
				$arrMenuEntry_Tag = array( $GLOBALS['submenu'][ $strPageSlug ][ $intIndex ] );
				unset( $GLOBALS['submenu'][ $strPageSlug ][ $intIndex ] );
				continue;				
			}

		}
		
		// Second iteration.
		$intMenuPos_Setting = -1;
		foreach ( $GLOBALS['submenu'][ $strPageSlug ] as $intIndex => $arrSubMenu ) {
			
			$intMenuPos_Setting++;	
			if (  isset( $arrSubMenu[ 2 ] ) && $arrSubMenu[ 2 ] == 'fetch_tweets_settings' ) 
				break;	// the position variable will now contain the position of the Setting menu item.
	
		}
	
		// Insert the Tag menu item before the Setting menu item.
		array_splice( 
			$GLOBALS['submenu'][ $strPageSlug ], // original array
			$intMenuPos_Setting, 	// position
			0, 	// offset - should be 0
			$arrMenuEntry_Tag 	// replacement array
		);		

		// Unfortunately array_splice() will loose all the associated keys(index).
		
	}
	
	/*
	 * Layout the setting pages
	 * */
	public function head_FetchTweets_AdminPage( $strHead ) {

		$oUserAds = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		return $strHead 
			. '<div class="fetch-tweets-admin-body">'
			. '<table border="0" cellpadding="0" cellspacing="0" unselectable="on" width="100%">
			<tbody>
			<tr>
			<td valign="top">'
			. '<div style="margin-top: 10px;">'		
			. $oUserAds->getTextAd()
			. '</div>';
			
	}
	public function foot_FetchTweets_AdminPage( $strFoot ) {
		
		switch ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'authentication' ) {
			case 'authentication':
				$numItems = defined( 'WPLANG' ) && WPLANG == 'ja' ? 4 : 4;
				break;
			default:
				$numItems = 4;
				break;
		}	
		
		$oUserAds = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		return $strFoot 
			// . '<div style="float:left; margin-top: 10px" >' 
			// . $oUserAds->getTextAd() 
			// . '</div>'
			. '</td>
			<td valign="top" rowspan="2" style="padding-top:20px;">' 
			. ( rand( 0, 1 ) ? $oUserAds->get160xNTopRight() : $oUserAds->get160xN( $numItems ) )
			// . $this->oUserAds->GetSkyscraper( $numItems ) 
			. '</td>
			</tr>
			<tr>
				<td valign="bottom" align="center">'
			// . $oUserAds->getBottomBanner() 
			. '</td>
			</tr>
			</tbody>
			</table>'
			. '</div><!-- end fetch-tweets-admin-body -->';
			
	}	 
	
	/*
	 * Add Rule by List Page
	 */
	public function do_fetch_tweets_add_rule_by_list() {	// do_ + page slug
		
		// $oFetch = new FetchTweets_Fetch;
		// $arrTweets = $oFetch->getListsByScreenName( 'Otto42' );
// Debug
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";			
		// $arrTweets = $oFetch->getTweetsAsArray( array( 'list_id' => '33331244' ) );
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";					
		
	}
	public function validation_fetch_tweets_add_rule_by_list( $arrInput, $arrOriginal ) {
				
		// Check if the input has been properly sent.
		if ( ! isset( $arrInput['fetch_tweets_add_rule_by_list']['add_rule_by_list']['list_owner_screen_name'] ) ) {			
			$this->setSettingNotice( __( 'Something went wrong. Your input could not be received. Try again and if this happens again, contact the developer.', 'fetch-tweets' ) );
			return $arrOriginal;
		}
		
		// Variables
		$fVerified = true;	// flag
		$arrErrors = array();	// error array
		$strOwnerScreenName = $arrInput['fetch_tweets_add_rule_by_list']['add_rule_by_list']['list_owner_screen_name'];
		
		// The list owner screen name must be provided.
		if ( empty( $strOwnerScreenName ) ) {
			$arrErrors['add_rule_by_list']['list_owner_screen_name'] = __( 'The screen name of the list owner must be specified: ' ) . $strOwnerScreenName;
			$this->setFieldErrors( $arrErrors );		
			$this->setSettingNotice( __( 'There was an error in your input.', 'fetch-tweets' ) );
			return $arrOriginal;						
		}
		
		// Fetch the lists by the screen name.
		$oFetch = new FetchTweets_Fetch;
		$arrLists = $oFetch->getListNamesFromScreenName( $strOwnerScreenName );
		if ( empty( $arrLists ) ) {
			$this->setSettingNotice( __( 'No list found.', 'fetch-tweets' ) );
			return $arrOriginal;			
		}

		// Set the transient of the fetched IDs. This will be used right next page load.
		$strListCacheID = uniqid();
		set_transient( $strListCacheID, $arrLists, 60 );		
		$this->oUtil->goRedirect( admin_url( "post-new.php?post_type=fetch_tweets&tweet_type=list&list_cache={$strListCacheID}&screen_name={$strOwnerScreenName}" ) );
		
	}
	
	/*
	 * Authentication In-Page Tabs
	 */
	public function load_fetch_tweets_settings() { // load_ + page slug
		session_start();	// Start session to store access token.
	}
	public function load_fetch_tweets_settings_twitter_connect() {
				
		// Check if the session array to have the access token; otherwise, clear the session.
		if ( 
			empty( $_SESSION['access_token'] ) 
			|| empty( $_SESSION['access_token']['oauth_token'] ) 
			|| empty( $_SESSION['access_token']['oauth_token_secret'] ) 
		) 		
			session_destroy();
						
	}
	public function load_fetch_tweets_settings_twitter_clear_session() {

		/* Clear sessions */
		session_destroy();
		 
		/* Redirect to page with the connect to Twitter option. */
		wp_redirect( admin_url( $GLOBALS['pagenow'] . "?page=fetch_tweets_settings&tab=twitter_connect" ) );
	
	}
	
	/**
	 * Redirects to the twitter to get authenticated.
	 * 
	 * @since			1.3.0
	 * @remark			This is redirected from the "Connect to Twitter" button.
	 */
	public function load_fetch_tweets_settings_twitter_redirect() {	// load_ + page slug + tab
	
		/* Build TwitterOAuth object with client credentials. */
		$oConnect = new TwitterOAuth( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret );
		 
		/* Get temporary credentials. */
		// Requesting authentication tokens, the parameter is the URL we will be redirected to		
		$arrRequestToken = $oConnect->getRequestToken( add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_callback' ), admin_url( $GLOBALS['pagenow'] ) ) );

		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $strTemporaryToken = $arrRequestToken['oauth_token'];
		$_SESSION['oauth_token_secret'] = $arrRequestToken['oauth_token_secret'];
		
		/* If last connection failed don't display authorization link. */
		switch ( $oConnect->http_code ) {
		  case 200:	/* Build authorize URL and redirect user to Twitter. */
			wp_redirect( $oConnect->getAuthorizeURL( $strTemporaryToken ) );	// goes to twitter.com
			break;
		  default:	/* Show notification if something went wrong. */
			die( __( 'Could not connect to Twitter. Refresh the page or try again later.', 'fetch-tweets' ) );
		}
		exit;
	
	}	
	
	/**
	 * Receives the callback from Twitter authentication and saves the access token.
	 * 
	 * @remark			This method is triggered when the user get redirected back to the admin page
	 */
	public function load_fetch_tweets_settings_twitter_callback() {
				
		/* If the oauth_token is old redirect to the authentication page. */
		if (
			isset( $_REQUEST['oauth_token'] ) 
			&& $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']
		) {
			$_SESSION['oauth_status'] = 'oldtoken';
			wp_redirect( add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication' ), admin_url( $GLOBALS['pagenow'] ) ) );
			// wp_redirect( admin_url( $GLOBALS['pagenow'] . "?page=fetch_tweets_settings&tab=twitter_clear_session" ) );
		}
		
		$oOption = & $GLOBALS['oFetchTweets_Option'];

		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		$oConnect = new TwitterOAuth( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret'] );

		/* Request access tokens from twitter */
		$arrAccessTokens = $oConnect->getAccessToken( $_REQUEST['oauth_verifier'] );

		/* Save the access tokens. Normally these would be saved in a database for future use. */
		$_SESSION['access_token'] = $arrAccessTokens;
		$oOption->saveAccessToken( $arrAccessTokens['oauth_token'], $arrAccessTokens['oauth_token_secret'] );

		/* Remove no longer needed request tokens */
		unset( $_SESSION['oauth_token'] );
		unset( $_SESSION['oauth_token_secret'] );

		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		if ( 200 == $oConnect->http_code ) {
			
			/* The user has been verified and the access tokens can be saved for future use */
			$_SESSION['status'] = 'verified';		  
			wp_redirect( add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_connect' ), admin_url( $GLOBALS['pagenow'] ) ) );

		  
		  
		} else {
			
		  /* Save HTTP status for error dialogue on authentication page.*/
		  // Let the user set authentication keys manually		  
		  wp_redirect( add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication' ), admin_url( $GLOBALS['pagenow'] ) ) );
	  
		}
	
	}	
	
	// The connect page
	public function do_form_fetch_tweets_settings_twitter_connect() {	// do_form_ + page slug + _ + tab slug
		
		// Store the status array into the property. This property will be referred when the Connect to Twitter button is rendered.
		$this->arrStatus = $this->getVerificationStatus();

		$this->renderAuthenticationStatus( $this->arrStatus );
	
// echo $this->oDebug->getArray( $arrStatus );
// echo $this->oDebug->getArray( $this->oOption->arrOptions['fetch_tweets_settings'] );
				
	}

	/**
	 * Retrieves the verification status with the saved access keys.
	 * 
	 * This method first checks with the manually set authentication keys and if it fails, it checks with the automatically set authentication keys.
	 * 
	 * @since			1.3.0
	 * @return			array			The array which contains the verification status.
	 */
	protected function getVerificationStatus() {

		// If the access token and access secret keys have been manually set,
		$arrStatus = $this->oOption->isAuthKeysManuallySet()
			? $this->verifyCrediential( $this->oOption->getConsumerKey(), $this->oOption->getConsumerSecret(), $this->oOption->getAccessToken(), $this->oOption->getAccessTokenSecret() )
			: array();
			
		if ( ! empty( $arrStatus ) ) return $arrStatus;
			
		// If the access token and secret keys have been automatically set,
		if ( $this->oOption->isAuthKeysAutomaticallySet() )
			$arrStatus = $this->verifyCrediential( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $this->oOption->getAccessTokenAuto(), $this->oOption->getAccessTokenSecretAuto() );
			
		if ( $arrStatus ) return $arrStatus;
	
	}
	
	/**
	 * Checks the API credential is valid or not.
	 * 	 
	 * @since			1.3.0
	 * @return			array			the retrieved data.
	 * @remark			The returned data is a merged result of 'account/verify_credientials' and 'rate_limit_status'.
	 */
	protected function verifyCrediential( $strConsumerKey, $strConsumerSecret, $strAccessToken, $strAccessSecret ) {
		
		// Return the cached response if available.
		$strCachID = FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $strConsumerKey, $strConsumerSecret, $strAccessToken, $strAccessSecret ) ) );
		$vData = get_transient( $strCachID );
		if ( $vData !== false ) return $vData;
		
		// Perform the requests.
		$oTwitterOAuth =  new FetchTweets_TwitterOAuth( $strConsumerKey, $strConsumerSecret, $strAccessToken, $strAccessSecret );
		$arrUser = $oTwitterOAuth->get( 'account/verify_credentials' );
		
		// If the user id could not be retrieved, it means it failed.
		if ( ! isset( $arrUser['id'] ) || ! $arrUser['id'] ) return array();
			
		// Otherwise, it is okay. Retrieve the current status.
		$arrStatus = $oTwitterOAuth->get( 'https://api.twitter.com/1.1/application/rate_limit_status.json?resources=search,statuses,lists' );
		
		// Set the cache.
		$arrData = is_array( $arrStatus ) ? $arrUser + $arrStatus : $arrUser;
		set_transient( $strCachID, $arrData, 60 * 2 );	// stores the cache only for 120 seconds. It is allowed 15 requests in 15 minutes.
		return $arrData;

	}
	
	public function do_form_fetch_tweets_settings_authentication() {
		
		$this->arrStatus = $this->getVerificationStatus();
		$this->renderAuthenticationStatus( $this->arrStatus );
		
	}
	
	/**
	 * Renders the authentication status table.
	 * 
	 * @since			1.3.0
	 * @param			array			$arrStatus			This arrays should be the merged array of the results of 'account/verify_credientials' and 'rate_limit_status' requests.
	 * 
	 */
	protected function renderAuthenticationStatus( $arrStatus ) {
		
		$fIsValid = isset( $arrStatus['id'] ) && $arrStatus['id'] ? true : false;
		$strScreenName = isset( $arrStatus['screen_name'] ) ? $arrStatus['screen_name'] : "";
		
		$strUserTimelineLimit = isset( $arrStatus['resources']['statuses']['/statuses/user_timeline'] )
		? $arrStatus['resources']['statuses']['/statuses/user_timeline']['remaining'] . ' / ' . $arrStatus['resources']['statuses']['/statuses/user_timeline']['limit'] 
			. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $arrStatus['resources']['statuses']['/statuses/user_timeline']['reset'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . " )"
		: "";		
		$strSearchLimit = isset( $arrStatus['resources']['search']['/search/tweets'] ) 
			? $arrStatus['resources']['search']['/search/tweets']['remaining'] . ' / ' . $arrStatus['resources']['search']['/search/tweets']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $arrStatus['resources']['search']['/search/tweets']['reset'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . " )"
			: "";

		$strListLimit = isset( $arrStatus['resources']['lists']['/lists/statuses'] )
			? $arrStatus['resources']['lists']['/lists/statuses']['remaining'] . ' / ' . $arrStatus['resources']['lists']['/lists/statuses']['limit'] 
				. "&nbsp;&nbsp;&nbsp;( " . __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $arrStatus['resources']['lists']['/lists/statuses']['reset'] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) . " )"
			: "";
		
	?>		
		<h3><?php _e( 'Status', 'fetch-tweets' ); ?></h3>
		<table class="form-table auth-status">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Status', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $fIsValid ? '<span class="authenticated">' . __( 'Authenticated', 'fetch-tweets' ) . '</span>': '<span class="unauthenticated">' . __( 'Not authenticated', 'fetch-tweets' ) . '</span>'; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Screen Name', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strScreenName; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Timeline Request Limit', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strUserTimelineLimit; ?>
					</td>
				</tr>	
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Search Request Limit', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strSearchLimit; ?>
					</td>
				</tr>	
				<tr valign="top">
					<th scope="row">
						<?php _e( 'List Request Limit', 'fetch-tweets' ); ?>
					</th>
					<td>
						<?php echo $strListLimit; ?>
					</td>
				</tr>				
			</tbody>
		</table>
					
		<?php
// $this->oDebug->dumpArray( $arrStatus );		
	}
	/**
	 * Renders the authentication link buttons.
	 * @since			1.3.0
	 */
	protected function renderAuthenticationButtons( $arrStatus ) {
		
		$fIsValid = isset( $arrStatus['id'] ) && $arrStatus['id'] ? true : false;
	?>		
		<h3><?php _e( 'Authenticate', 'fetch-tweets' ); ?></h3>
		<table class="form-table auth-status">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Connect to Twitter', 'fetch-tweets' ); ?>
					</th>
					<td>
						<a href="http://www.google.com">
							<input type="submit" class="button button-primary" value="<?php echo $fIsValid ? __( 'Disconnect', 'fetch-tweets' ) : __( 'Connect', 'fetch-tweets' ) ; ?>" />
						</a>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Manual', 'fetch-tweets' ); ?>
					</th>
					<td>
						<a href="<?php echo add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication' ), admin_url( $GLOBALS['pagenow'] ) ); ?>">
							<input type="submit" class="button button-secondary" value="<?php _e( 'Set Keys Manually', 'fetch-tweets' ); ?>"/>
						</a>
					</td>
				</tr>		
			</tbody>
		</table>
					
		<?php		
	}
	
	
	/**
	 * Filters the output of the Connect To Twitter button.
	 * 
	 * If it's not authenticated yet, the label becomes "Connect"; otherwise, "Disconnect"
	 */
	public function FetchTweets_AdminPage_field_connect_to_twitter( $strField ) {	// extended class name + _ + section_ + section ID
		
		return ! ( isset( $this->arrStatus['id'] ) && $this->arrStatus['id'] )
			? $strField		// the connect button
			: '<span style="display: inline-block; min-width:120px;">'
					. '<input id="twitter_connect_connect_to_twitter_0" class="button button-primary" type="submit" name="disconnect_from_twitter" value="' . __( 'Disconnect', 'fetch-tweets' ) . '">&nbsp;&nbsp;'
				.'</span>'; // the disconnect button
				
	}
	
	public function validation_fetch_tweets_settings( $arrInput, $arrOriginal ) {
		
		// If the disconnect button is pressed, delete the authentication keys.
		if ( isset( $_POST['disconnect_from_twitter'] ) ) {
			
			$arrInput = is_array( $arrInput ) ? $arrInput : array();	// in WP v3.4.2, when the Disconnect button is pressed an $arrInput was passed as an empty string. Something went wrong.
			delete_transient( FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $this->oOption->getConsumerKey(), $this->oOption->getConsumerSecret(), $this->oOption->getAccessToken(), $this->oOption->getAccessTokenSecret() ) ) ) );
			delete_transient( FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $this->oOption->getAccessTokenAuto(), $this->oOption->getAccessTokenSecretAuto() ) ) ) );
			unset( $arrInput['fetch_tweets_settings']['authentication_keys'] );
			unset( $arrInput['fetch_tweets_settings']['twitter_connect'] );
			
		}

		return $arrInput;
		
	}
	
	/*
	 * Settings Page
	 */
	public function do_before_fetch_tweets_settings() {	// do_before_ + page slug
		$this->showPageTitle( false );
	}
		
	public function do_fetch_tweets_settings () {	// do_ + page slug
		
		// submit_button();
// echo "<h3>Variables</h3>";
// echo $this->oDebug->getArray( $GLOBALS['option_page'] );

// echo "<h3>Properties</h3>";
// echo $this->oDebug->getArray( $this->oProps ); 
// echo $this->oDebug->getArray( $this->oProps->arrOptions ); 

// echo "<h3>Options</h3>";
// $arrOptions = get_option( FetchTweets_Commons::AdminOptionKey );
// echo $this->oDebug->getArray( $arrOptions );


// echo "<h3>Registered Pages</h3>";
// echo $this->oDebug->getArray( $this->oProps->arrPages );
// echo "<h3>Registered Tabs</h3>";
// echo $this->oDebug->getArray( $this->oProps->arrInPageTabs[ 'fetch_tweets_settings' ] );

	}
	
	public function validation_fetch_tweets_settings_general( $arrInput, $arrOriginal ) {
		
		$arrInput['fetch_tweets_settings']['default_values']['count'] = $this->oUtil->fixNumber(
			$arrInput['fetch_tweets_settings']['default_values']['count'],
			$GLOBALS['oFetchTweets_Option']->arrStructure_DefaultParams['count'],
			1
		);
		
		return $arrInput;
		
	}
	public function validation_fetch_tweets_settings_reset( $arrInput, $arrOriginal ) {
				
		// Variables
		$fChanged = false;
				
		// Make it one dimensional.
		$arrSubmit = array();
		foreach ( $arrInput['fetch_tweets_settings'] as $strSection => $arrFields ) 
			$arrSubmit = $arrSubmit + $arrFields;				
			
		// If the Perform button is not set, return.
		if ( ! isset( $arrSubmit['submit_reset_settings'] ) ) {
			$this->setSettingNotice( __( 'Nothing changed.', 'fetch-tweets' ) );	
			return $arrOriginal;
		}

		if ( isset( $arrSubmit['clear_caches'] ) && $arrSubmit['clear_caches'] ) {
			$this->clearCaches();
			$fChanged = true;
			$this->setSettingNotice( __( 'The caches have been cleared.', 'fetch-tweets' ) );
		}
		
		// $this->oDebug->getArray( $arrSubmit, dirname( __FILE__ ) . '/submit.txt' );
		// $this->oDebug->getArray( $GLOBALS['oFetchTweets_Option']->arrOptions, dirname( __FILE__ ) . '/options.txt' );
		
		if ( isset( $arrSubmit['option_sections'] ) ) {
			if ( isset( $arrSubmit['option_sections']['all'] ) && $arrSubmit['option_sections']['all'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_All' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['genaral'] ) && $arrSubmit['option_sections']['general'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_General' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['template'] ) && $arrSubmit['option_sections']['template'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_Template' ), 999 );
			}		
		}
		
		if ( ! $fChanged )
			$this->setSettingNotice( __( 'Nothing changed.', 'fetch-tweets' ) );	
		return $arrOriginal;	// no need to update the options.
		
	}
	public function deleteOptions_All() {
		delete_option( FetchTweets_Commons::AdminOptionKey );
	}
	public function deleteOptions_General() {
		// Currently not working: Somehow the options get recovered.
		unset( $GLOBALS['oFetchTweets_Option']->arrOptions['fetch_tweets_settings'] );
		$GLOBALS['oFetchTweets_Option']->saveOptions();		
	}
	public function deleteOptions_Template() {		
		// Currently not working: Somehow the options get recovered.
// $this->oDebug->getArray( $GLOBALS['oFetchTweets_Option']->arrOptions, dirname( __FILE__ ) . '/options.txt' );	
		unset( $GLOBALS['oFetchTweets_Option']->arrOptions['arrTemplates'] );
		unset( $GLOBALS['oFetchTweets_Option']->arrOptions['arrDefaultTemplate'] );
		unset( $GLOBALS['oFetchTweets_Option']->arrOptions['fetch_tweets_templates'] );
		$GLOBALS['oFetchTweets_Option']->saveOptions();
// $this->oDebug->getArray( $GLOBALS['oFetchTweets_Option']->arrOptions, dirname( __FILE__ ) . '/options.txt' );	
	}
	
	/**
	 * Clears tweet caches
	 * 
	 * @since			1.2.0
	 */ 
	protected function clearCaches( $arrPrefixes=array( 'FTWS', 'FTWSFeedMs' ) ) {
		
		foreach( $arrPrefixes as $strPrefix ) {
			$GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS['table_prefix'] . "options` WHERE `option_name` LIKE ( '_transient_%{$strPrefix}%' )" );
			$GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS['table_prefix'] . "options` WHERE `option_name` LIKE ( '_transient_timeout_%{$strPrefix}%' )" );
		}
	
	}
	
	protected $arrColumnOption = array (
		'strClassAttr' 				=>	'fetch_tweets_multiple_columns',
		'strClassAttrGroup' 		=>	'fetch_tweets_multiple_columns_box',
		'strClassAttrRow' 			=>	'fetch_tweets_multiple_columns_row',
		'strClassAttrCol' 			=>	'fetch_tweets_multiple_columns_col',
		'strClassAttrFirstCol' 		=>	'fetch_tweets_multiple_columns_first_col',
	);	
	protected $arrColumnInfoDefault = array (	// this will be modified as the items get rendered
		'fIsRowTagClosed'	=>	False,
		'numCurrRowPos'		=>	0,
		'numCurrColPos'		=> 	0,
	);	
	
	/*
	 * Template Page
	 */ 
	public function do_before_fetch_tweets_templates() {
		$this->showPageTitle( false );
	}
	public function do_fetch_tweets_templates_list_template_table() {	// do_ + page slug + tab slug
			
		$this->oTemplateListTable->prepare_items();
		?>
        <form id="template-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : 'fetch_tweets_templates'; ?>" />
            <input type="hidden" name="tab" value="<?php echo isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'list_template_table'; ?>" />
            <input type="hidden" name="post_type" value="<?php echo isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : FetchTweets_Commons::PostTypeSlug; ?>" />
            <!-- Now we can render the completed list table -->
            <?php $this->oTemplateListTable->display() ?>
        </form>		
		<?php
	
// echo "<h3>Properties</h3>";			
// echo $this->oDebug->getArray( $this->oProps );
			
	}
	public function do_fetch_tweets_templates_get_templates() {
		
		echo "<p>" . sprintf( __( 'Want your template to be listed here? Send the file to %1$s.', 'fetch-tweets' ), 'wpplugins@michaeluno.jp' ) . "</p>";

		$oExtensionLoader = new FetchTweets_Extensions();
		$arrFeedItems = $oExtensionLoader->fetchFeed( 'http://feeds.feedburner.com/MiunosoftFetchTweetsTemplate' );
		if ( empty( $arrFeedItems ) ) {
			echo "<h3>" . __( 'No extension has been found.', 'fetch-tweets' ) . "</h3>";
			return;
		}
		
		$arrOutput = array();
		$intMaxCols = 4;
		$this->arrColumnInfo = $this->arrColumnInfoDefault;
		foreach( $arrFeedItems as $strTitle => $arrItem ) {
			
			// Increment the position
			$this->arrColumnInfo['numCurrColPos']++;
			
			// Enclose the item buffer into the item container
			$strItem = '<div class="' . $this->arrColumnOption['strClassAttrCol'] 
				. ' ftws_col_element_of_' . $intMaxCols . ' '
				. ' ftws_extension '
				. ( ( $this->arrColumnInfo['numCurrColPos'] == 1 ) ?  $this->arrColumnOption['strClassAttrFirstCol']  : '' )
				. '"'
				. '>' 
				. '<div class="ftws_extension_item">' 
					. "<h4>{$arrItem['strTitle']}</h4>"
					. $arrItem['strDescription'] 
					. "<div class='get-now'><a href='{$arrItem['strLink']}' target='_blank' rel='nofollow'>" 
						. "<input class='button button-secondary' type='submit' value='" . __( 'Get it Now', 'fetch-tweets' ) . "' />"
					. "</a></div>"
				. '</div>'
				. '</div>';	
				
			// If it's the first item in the row, add the class attribute. 
			// Be aware that at this point, the tag will be unclosed. Therefore, it must be closed somewhere. 
			if ( $this->arrColumnInfo['numCurrColPos'] == 1 ) 
				$strItem = '<div class="' . $this->arrColumnOption['strClassAttrRow']  . '">' . $strItem;
		
			// If the current column position reached the set max column, increment the current position of row
			if ( $this->arrColumnInfo['numCurrColPos'] % $intMaxCols == 0 ) {
				$this->arrColumnInfo['numCurrRowPos']++;		// increment the row number
				$this->arrColumnInfo['numCurrColPos'] = 0;		// reset the current column position
				$strItem .= '</div>';  // close the section(row) div tag
				$this->arrColumnInfo['fIsRowTagClosed'] = 	True;
			}		
			
			$arrOutput[] = $strItem;
		
		}
		
		// if the section(row) tag is not closed, close it
		if ( ! $this->arrColumnInfo['fIsRowTagClosed'] ) $arrOutput[] .= '</div>';	
		$this->arrColumnInfo['fIsRowTagClosed'] = true;
		
		// enclose the output in the group tag
		$strOut = '<div class="' . $this->arrColumnOption['strClassAttr'] . ' '
				.  $this->arrColumnOption['strClassAttrGroup'] . ' '
				. '"'
				// . ' style="min-width:' . 200 * $intMaxCols . 'px;"'
				. '>'
				. implode( '', $arrOutput )
				. '</div>';
		
		echo '<div class="ftws_extension_container">' . $strOut . '</div>';
		
	}
	protected function getTemplateArray( $strDefaultTemplateSlug ) {
				
		return $GLOBALS['oFetchTweets_Templates']->getActiveTemplates() + $GLOBALS['oFetchTweets_Templates']->getUploadedTemplates();
				
		$arrActiveTemplates = $GLOBALS['oFetchTweets_Templates']->getActiveTemplates();
		$arrUploadedTemplates = $GLOBALS['oFetchTweets_Templates']->getUploadedTemplates();
		$arrData = $arrActiveTemplates + $arrUploadedTemplates;
		// $arrData = $GLOBALS['oFetchTweets_Option']->arrOptions['arrTemplates'] + $GLOBALS['oFetchTweets_Templates']->getTemplates();
		foreach ( $arrData as $arrDatum ) 	// set all default flags to false.
			$arrDatum['fIsDefault'] = false;		
			
		$arrData[ $strDefaultTemplateSlug ]['fIsDefault'] = true;	// set the default template.
		$arrData[ $strDefaultTemplateSlug ]['fIsActive'] = true;	// set the default template to be activated.
		
		return $arrData;
			
	}	
		
	/*
	 * Extension page
	 */ 
	public function do_before_fetch_tweets_extensions() {	// do_before_ + page slug
		$this->showPageTitle( false );
	}
	public function do_fetch_tweets_extensions_get_extensions() {
				
		$oExtensionLoader = new FetchTweets_Extensions();
		$arrFeedItems = $oExtensionLoader->fetchFeed( 'http://feeds.feedburner.com/MiunosoftFetchTweetsExtension' );
		if ( empty( $arrFeedItems ) ) {
			echo "<h3>" . __( 'No extension has been found.', 'fetch-tweets' ) . "</h3>";
			return;
		}
		
		$arrOutput = array();
		$intMaxCols = 4;
		$this->arrColumnInfo = $this->arrColumnInfoDefault;
		foreach( $arrFeedItems as $strTitle => $arrItem ) {
			
			// Increment the position
			$this->arrColumnInfo['numCurrColPos']++;
			
			// Enclose the item buffer into the item container
			$strItem = '<div class="' . $this->arrColumnOption['strClassAttrCol'] 
				. ' ftws_col_element_of_' . $intMaxCols . ' '
				. ' ftws_extension '
				. ( ( $this->arrColumnInfo['numCurrColPos'] == 1 ) ?  $this->arrColumnOption['strClassAttrFirstCol']  : '' )
				. '"'
				. '>' 
				. '<div class="ftws_extension_item">' 
					. "<h4>{$arrItem['strTitle']}</h4>"
					. $arrItem['strDescription'] 
					. "<div class='get-now'><a href='{$arrItem['strLink']}' target='_blank' rel='nofollow'>" 
						. "<input class='button button-secondary' type='submit' value='" . __( 'Get it Now', 'fetch-tweets' ) . "' />"
					. "</a></div>"
				. '</div>'
				. '</div>';	
				
			// If it's the first item in the row, add the class attribute. 
			// Be aware that at this point, the tag will be unclosed. Therefore, it must be closed somewhere. 
			if ( $this->arrColumnInfo['numCurrColPos'] == 1 ) 
				$strItem = '<div class="' . $this->arrColumnOption['strClassAttrRow']  . '">' . $strItem;
		
			// If the current column position reached the set max column, increment the current position of row
			if ( $this->arrColumnInfo['numCurrColPos'] % $intMaxCols == 0 ) {
				$this->arrColumnInfo['numCurrRowPos']++;		// increment the row number
				$this->arrColumnInfo['numCurrColPos'] = 0;		// reset the current column position
				$strItem .= '</div>';  // close the section(row) div tag
				$this->arrColumnInfo['fIsRowTagClosed'] = 	True;
			}		
			
			$arrOutput[] = $strItem;
		
		}
		
		// if the section(row) tag is not closed, close it
		if ( ! $this->arrColumnInfo['fIsRowTagClosed'] ) $arrOutput[] .= '</div>';	
		$this->arrColumnInfo['fIsRowTagClosed'] = true;
		
		// enclose the output in the group tag
		$strOut = '<div class="' . $this->arrColumnOption['strClassAttr'] . ' '
				.  $this->arrColumnOption['strClassAttrGroup'] . ' '
				. '"'
				// . ' style="min-width:' . 200 * $intMaxCols . 'px;"'
				. '>'
				. implode( '', $arrOutput )
				. '</div>';
		
		echo '<div class="ftws_extension_container">' . $strOut . '</div>';
		
	}
			
}