<?php
abstract class FetchTweets_AdminPage_SetUp_Page extends FetchTweets_AdminPage_Start {
			
	protected function _setUpPages() {
		
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
	}		
	
	protected function _setUpStyles() {
		
		/*
		 * Page Styling
		 */
		$this->showPageHeadingTabs( false );		// disables the page heading tabs by passing false.
		$this->setInPageTabTag( 'h2' );				
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/css/admin.css' ) );
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/css/fetch_tweets_templates.css' ), 'fetch_tweets_templates' );
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/css/fetch_tweets_settings.css' ), 'fetch_tweets_settings' );
	 
	}	
				
}