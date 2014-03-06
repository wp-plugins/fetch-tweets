<?php
abstract class FetchTweets_AdminPage_SetUp_Form extends FetchTweets_AdminPage_SetUp_Page {
			
	protected function _setUpForm() {
	
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
	}	
				
}