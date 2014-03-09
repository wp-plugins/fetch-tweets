<?php
abstract class FetchTweets_AdminPage_SetUp_Form extends FetchTweets_AdminPage_SetUp_Page {
			
	protected function _setUpForm() {
	
		/*
		 * Form Elements
		 */
		$this->addSettingSections(
			'fetch_tweets_settings',
			array(
				'section_id'		=> 'twitter_connect',
				// 'page_slug'		=> 'fetch_tweets_settings',
				'tab_slug'		=> 'twitter_connect',
				'title'			=> __( 'Authenticate', 'fetch-tweets' ),
			),		
			array(
				'section_id'		=> 'authentication_keys',
				// 'page_slug'		=> 'fetch_tweets_settings',
				'tab_slug'		=> 'authentication',
				'title'			=> __( 'Authentication Keys', 'fetch-tweets' ),
				'description'	=> __( 'These keys are required to process oAuth requests of the twitter API.', 'fetch-tweets' ),
			),
			array(
				'section_id'		=> 'default_values',
				// 'page_slug'		=> 'fetch_tweets_settings',
				'tab_slug'		=> 'general',
				'title'			=> __( 'Default Values', 'fetch-tweets' ),
				'help'			=> __( 'Set the default option values which will be applied when the argument values are not set.', 'fetch-tweets' )
										. __( 'These values will be overridden by the argument set directly to the widget options or shortcode.', 'fetch-tweets' ),
			),			
			array(
				'section_id'		=> 'cache_settings',
				// 'page_slug'		=> 'fetch_tweets_settings',
				'tab_slug'		=> 'general',
				'title'			=> __( 'Cache Settings', 'fetch-tweets' ),
			),			
			array(
				'section_id'		=> 'capabilities',
				'capability'		=> 'manage_options',
				// 'page_slug'		=> 'fetch_tweets_settings',
				'tab_slug'		=> 'misc',
				'title'			=> __( 'Access Rights', 'fetch-tweets' ),
				'description'	=> __( 'Set the access levels to the plugin setting pages.', 'fetch-tweets' ),
			),			
			array(
				'section_id'		=> 'reset_settings',
				// 'page_slug'		=> 'fetch_tweets_settings',
				'capability'		=> 'manage_options',
				'tab_slug'		=> 'reset',
				'title'			=> __( 'Reset Settings', 'fetch-tweets' ),
				'description'	=> __( 'If you get broken options, initialize them by performing reset.', 'fetch-tweets' ),
			),
			array(
				'section_id'		=> 'caches',
				// 'page_slug'		=> 'fetch_tweets_settings',
				'tab_slug'		=> 'reset',
				'title'			=> __( 'Caches', 'fetch-tweets' ),
				'description'	=> __( 'If you need to refresh the fetched tweets, clear the cashes.', 'fetch-tweets' ),
			)			
		);	
		$this->addSettingSections(
			'fetch_tweets_add_rule_by_list',
			array(
				'section_id'	=> 'add_rule_by_list',
				'title'			=> __( 'Specify the Screen Name', 'fetch-tweets' ),
				'description'	=> __( 'In order to select list, the user name(screen name) of the account that owns the list must be specified.', 'fetch-tweets' ),
			)		
		);
		
		$this->addSettingFields(
			'add_rule_by_list',
			array(	
				'field_id' => 'list_owner_screen_name',
				'section_id' => 'add_rule_by_list',
				'title' => __( 'Owner Screen Name', 'fetch-tweets' ),
				'description' => __( 'The screen name(user name) that owns the list.', 'fetch-tweets' ) . '<br />'
					. 'e.g. miunosoft',
				'type' => 'text',
				'value' => '',
				'attributes'	=>	array(
					'size'	=>	40,
				),				
			),		
			array(  // single button
				'field_id' => 'list_proceed',
				'section_id' => 'add_rule_by_list',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
// 'vLabelMinWidth' => 0,
				'label' => __( 'Proceed', 'fetch-tweets' ),
				// 'href'	=> admin_url(),
				// 'href'	=> admin_url( 'post-new.php?post_type=fetch_tweets&tweet_type=list' ),
				// 'vRedirect'	=> admin_url(),
				// 'vRedirect'	=> admin_url( 'post-new.php?post_type=fetch_tweets&tweet_type=list' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),					
			)		
		);		
		
		// Add setting fields
 		$this->addSettingFields(
			array(	
				'field_id' => 'connect_to_twitter',
				'section_id' => 'twitter_connect',
				'title' => __( 'Connect to Twitter', 'fetch-tweets' ),
				'label' => __( 'Connect', 'fetch-tweets' ),
				'href' => add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_redirect' ), admin_url( $GLOBALS['pagenow'] ) ),
				'type' => 'submit',
			),	
			array(	
				'field_id' => 'manual_authentication',
				'section_id' => 'twitter_connect',
				'title' => __( 'Manual', 'fetch-tweets' ),
				'label' => __( 'Set Keys Manually', 'fetch-tweets' ),
				'href' => add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication', 'settings-updated' => false ) ),
				'type' => 'submit',
				'attributes'	=>	array(
					'class'	=>	'button button-secondary',
				),
			)			
		); 
		$this->addSettingFields(
			array(	
				'field_id' => 'consumer_key',
				'section_id' => 'authentication_keys',
				'title' => __( 'Consumer Key', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),				
			),
			array(	
				'field_id' => 'consumer_secret',
				'section_id' => 'authentication_keys',
				'title' => __( 'Consumer Secret', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),				
			),
			array(	
				'field_id' => 'access_token',
				'section_id' => 'authentication_keys',
				'title' => __( 'Access Token', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),
			),
			array(	
				'field_id' => 'access_secret',
				'section_id' => 'authentication_keys',
				'title' => __( 'Access Secret', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),
				'description' => '<p class="description">' 
					. sprintf( __( 'You can obtain those keys by logging in to <a href="%1$s" target="_blank">Twitter Developers</a>', 'fetch-tweets' ), 'https://dev.twitter.com/apps' )
					. '</p>',
			),
			array(  // single button
				'field_id' => 'submit_authentication_keys',
				'section_id' => 'authentication_keys',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
				'label' => __( 'Authenticate', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)		
		);
		// default_values
		$this->addSettingFields(
			array(
				'field_id' => 'count',
				'section_id' => 'default_values',
				'title' => __( 'Number of Items', 'fetch-tweets' ),
				'help' => __( 'The number of tweets to display.', 'fetch-tweets' )
					. __( 'Default', 'fetch-tweets' ) . ': ' . $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['count']
					. __( 'This option corresponds to the <code>count</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ),
				'default'	=> $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['count'],
				'type' => 'number',
			),
			array(
				'field_id'		=> 'twitter_media',
				'section_id' => 'default_values',
				'title'			=> __( 'Twitter Media', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Display media images posted in the tweet that are recognized as media file by Twitter.' ),
				'help'	=> __( 'This option corresponds to the <code>twitter_media</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ) . ' '
					. __( 'Currently only photos are supported by the Twitter API.' ),
				'default'			=> $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['twitter_media'],
			),
			array(
				'field_id'		=> 'external_media',
				'section_id' => 'default_values',
				'title'			=> __( 'External Media', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Replace media links of external sources to an embedded element.', 'fetch-tweets' ),
				'help'			=> __( 'This option corresponds to the <code>external_media</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ) . ' '
					. __( 'Unlike the above media images, there are media links that are not categorized as media by the Twitter API. Thus, enabling this option will attempt to replace them to the embedded elements.', 'fetch-tweets' ) . ' e.g. youtube, vimeo, dailymotion etc.',
				'default'			=> $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['external_media'],
			),
			array()
		);
		$this->addSettingFields(
			array(
				'field_id'		=> 'cache_for_errors',
				'section_id' 		=> 'cache_settings',
				'title'			=> __( 'Cache for Errors', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Cache fetched results even for an error.', 'fetch-tweets' ),
				'description'	=> __( 'This reduces the chances to reach the Twitter API rate limit.', 'fetch-tweets' ),
			),
			array(  // single button
				'field_id' => 'submit_cache_settings',
				'section_id' => 'cache_settings',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
'vLabelMinWidth' => 0,
				'label' => __( 'Save Changes', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)		
		);
		$this->addSettingFields(
			array(
				'field_id' => 'setting_page_capability',
				'section_id' => 'capabilities',
				'title' => __( 'Capability', 'fetch-tweets' ),
				'description' => __( 'Select the user role that is allowed to access the plugin setting pages.', 'fetch-tweets' )
					. __( 'Default', 'fetch-tweets' ) . ': ' . __( 'Administrator', 'fetch-tweets' ),
				'type' => 'select',
				'capability' => 'manage_options',
				'label' => array(						
					'manage_options' => __( 'Administrator', 'responsive-column-widgets' ),
					'edit_pages' => __( 'Editor', 'responsive-column-widgets' ),
					'publish_posts' => __( 'Author', 'responsive-column-widgets' ),
					'edit_posts' => __( 'Contributor', 'responsive-column-widgets' ),
					'read' => __( 'Subscriber', 'responsive-column-widgets' ),
				),
			),
			array(  // single button
				'field_id' => 'submit_misc',
				'section_id' => 'capabilities',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
'vLabelMinWidth' => 0,
				'label' => __( 'Save Changes', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)			
		);
		$this->addSettingFields(
			array(	
				'field_id' => 'option_sections',
				'section_id' => 'reset_settings',
				'title' => __( 'Options to Delete', 'fetch-tweets' ),
				'type' => 'checkbox',
				'delimiter' => '<br />',
				'label' => array(
					'all' => __( 'Reset', 'fetch-tweets' ), 
					// the followings are not supported yet
					// 'general' => __( 'General options', 'fetch-tweets' ), 	
					// 'template' => __( 'Template related options', 'fetch-tweets' ),
				),
			),
			// array(  // single button
				// 'field_id' => 'submit_reset_settings',
				// 'section_id' => 'reset_settings',
				// 'type' => 'submit',
				// 'before_field' => "<div class='right-button'>",
				// 'after_field' => "</div>",
				// 'vLabelMinWidth' => 0,
				// 'label' => __( 'Perform', 'fetch-tweets' ),
				// 'vClassAttribute' => 'button button-primary',
			// ),
			array(	
				'field_id' => 'clear_caches',
				'section_id' => 'caches',
				'title' => __( 'Clear Caches', 'fetch-tweets' ),
				'type' => 'checkbox',
				'label' => __( 'Clear tweet caches', 'fetch-tweets' ),
			),
			array(  // single button
				'field_id' => 'submit_reset_settings',
				'section_id' => 'caches',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
'vLabelMinWidth' => 0,
				'label' => __( 'Perform', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)			

		);

	}	
				
}