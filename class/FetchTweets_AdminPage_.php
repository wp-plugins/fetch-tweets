<?php
abstract class FetchTweets_AdminPage_ extends FetchTweets_AdminPageFramework {

	public function start_FetchTweets_AdminPage() {
		
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
    	
		$this->checkAPIKeys();
	
		$this->setRootMenuPageBySlug( 'edit.php?post_type=fetch_tweets' );
		$this->addSubMenuItems(
			/* 	e.g.
			 * 	'strPageTitle' => 'Your Page Title',
				'strPageSlug'] => 'your_page_slug',		// avoid hyphen(dash), dots, and white spaces
				'strScreenIcon' => 'edit',
				'strCapability' => 'manage-options',
				'numOrder' => 10,
			*/
			array(
				'strMenuTitle' => __( 'Add Rule by User Name', 'fetch-tweets' ),
				'strType' => 'link',
				'strURL' => 'post-new.php?post_type=fetch_tweets&tweet_type=screen_name',
				'fPageHeadingTab' => false,
				'numOrder' => 1,
			),		
			array(
				'strMenuTitle' => __( 'Add Rule by Keyword Search', 'fetch-tweets' ),
				'strType' => 'link',				
				'strURL' => 'post-new.php?post_type=fetch_tweets&tweet_type=search',
				'fPageHeadingTab' => false,
			),			
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
			// array(
				// 'strPageTitle' => __( 'Test Results', 'fetch-tweets' ),
				// 'strPageSlug' => 'fetch_tweets_test',
			// )
			/*	Screen Types:
				'edit', 'post', 'index', 'media', 'upload', 'link-manager', 'link', 'link-category', 
				'edit-pages', 'page', 'edit-comments', 'themes', 'plugins', 'users', 'profile', 
				'user-edit', 'tools', 'admin', 'options-general', 'ms-admin', 'generic',		 
			*/				

		);
		$this->addInPageTabs(
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'authentication',
				'strTitle'		=> __( 'Authentication', 'fetch-tweets' ),
				'numOrder'		=> 1,				
			),
			array(
				'strPageSlug'	=> 'fetch_tweets_settings',
				'strTabSlug'	=> 'reset',
				'strTitle'		=> __( 'Reset', 'fetch-tweets' ),
				'numOrder'		=> 2,				
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
		
		$this->showPageHeadingTabs( false );		// disables the page heading tabs by passing false.
		$this->setInPageTabTag( 'h2' );				
	
		$this->addSettingSections(
			array(
				'strSectionID'		=> 'authentication_keys',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'authentication',
				'strTitle'			=> __( 'Authentication Keys', 'fetch-tweets' ),
				'strDescription'	=> __( 'These keys are required to process oAuth requests of the twitter API.', 'fetch-tweets' ),
			),
			array(
				'strSectionID'		=> 'reset_settings',
				'strPageSlug'		=> 'fetch_tweets_settings',
				'strTabSlug'		=> 'reset',
				'strTitle'			=> __( 'Reset Settings', 'fetch-tweets' ),
				'strDescription'	=> __( 'If you get broken options, initialize them by performing reset.', 'fetch-tweets' ),
			)			
		);					
		
		// Add setting fields
		$this->addSettingFields(
			array(	
				'strFieldID' => 'consumer_key',
				'strSectionID' => 'authentication_keys',
				'strTitle' => 'Consumer Key',
				'strType' => 'text',
				'vSize' => 80,
			),
			array(	
				'strFieldID' => 'consumer_secret',
				'strSectionID' => 'authentication_keys',
				'strTitle' => 'Consumer Secret',
				'strType' => 'text',
				'vSize' => 80,
			),
			array(	
				'strFieldID' => 'access_token',
				'strSectionID' => 'authentication_keys',
				'strTitle' => 'Access Token',
				'strType' => 'text',
				'vSize' => 80,
			),
			array(	
				'strFieldID' => 'access_secret',
				'strSectionID' => 'authentication_keys',
				'strTitle' => 'Access Secret',
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
			array(  // single button
				'strFieldID' => 'submit_reset_settings',
				'strSectionID' => 'reset_settings',
				'strType' => 'submit',
				'strBeforeField' => "<div class='right-button'>",
				'strAfterField' => "</div>",
				'vLabelMinWidth' => 0,
				'vLabel' => __( 'Perform', 'fetch-tweets' ),
				'vClassAttribute' => 'button button-primary',
			)			
		);
		$this->addLinkToPluginDescription(  
			array(
				'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4UJHETVAZX34">' . __( 'Donate', 'fetch-tweets' ) . '</a>',
				'<a href="http://en.michaeluno.jp/contact/custom-order/?lang=' . ( WPLANG ? WPLANG : 'en' ) . '">' . __( 'Order custom plugin', 'fetch-tweets' ) . '</a>',
			) 
		);						
		
	}
	protected function checkAPIKeys() {
		
		$oOption = & $GLOBALS['oFetchTweets_Option'];
		if ( 
			empty( $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['consumer_key'] )
			|| empty( $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['consumer_secret'] )
			|| empty( $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['access_token'] )
			|| empty( $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['access_secret'] )
		)
		add_action( 'admin_notices', array( $this, 'showAdminNotice' ) );
	}
	public function showAdminNotice() {
			
		if ( ! (
			( isset( $_GET['page'] ) && $this->oProps->isPageAdded( $_GET['page'] ) ) 
			|| ( isset( $_GET['post_type'] ) && $_GET['post_type'] == FetchTweets_Commons::PostTypeSlug )
		) ) return; 
		
		// http://.../wp-admin/edit.php?post_type=fetch_tweets&page=fetch_tweets_settings
		$strSettingPageURL = admin_url( 'edit.php?post_type=fetch_tweets&page=fetch_tweets_settings&tab=authentication#authentication_keys' ); 
		echo "<div class='error'>"
			. "<p>" 
			. "<strong>" . FetchTweets_Commons::PluginName . "</strong>: "
			. sprintf( __( '<a href="%1$s">The API authentication keys need to be set</a> in order to use this plugin.', 'fetch-tweets' ), $strSettingPageURL ) 
			. "</p>"
			. "</div>";		
			
	}
	
	/*
	 * Layout the setting pages
	 * */
	function head_FetchTweets_AdminPage( $strHead ) {

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
	function foot_FetchTweets_AdminPage( $strFoot ) {
		
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
	
	
	public function do_before_fetch_tweets_settings() {	// do_before_ + page slug
		$this->showPageTitle( false );
	}
	
	public function do_fetch_tweets_settings () {	// do_ + page slug
		
		// submit_button();
// $arrOptions = get_option( FetchTweets_Commons::AdminOptionKey );
// echo "<h3>Options</h3>";
// echo $this->oDebug->getArray( $arrOptions );

// echo "<h3>Registered Pages</h3>";
// echo $this->oDebug->getArray( $this->oProps->arrPages );
// echo "<h3>Registered Tabs</h3>";
// echo $this->oDebug->getArray( $this->oProps->arrInPageTabs[ 'fetch_tweets_settings' ] );


	}
	
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
			
			// Edit the first item
			if ( $arrSubMenu[ 2 ] == 'edit.php?post_type=' . FetchTweets_Commons::PostTypeSlug ) {
				$GLOBALS['submenu'][ $strPageSlug ][ $intIndex ][ 0 ] = __( 'Manage Rules', 'fetch-tweets' );
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

		// Unfortunately array_splica() will loose all the associated keys(index).
		
	}
	
	public function validation_fetch_tweets_settings_reset( $arrInput, $arrOriginal ) {
				
		// Make it one dimensional.
		$arrSubmit = array();
		foreach ( $arrInput['fetch_tweets_settings'] as $strSection => $arrFields ) 
			$arrSubmit = $arrSubmit + $arrFields;				
			
		// If the Perform button is not set, return.
		if ( ! isset( $arrSubmit['submit_reset_settings'] ) ) return $arrOriginal;

		// $this->oDebug->getArray( $arrSubmit, dirname( __FILE__ ) . '/submit.txt' );
		// $this->oDebug->getArray( $GLOBALS['oFetchTweets_Option']->arrOptions, dirname( __FILE__ ) . '/options.txt' );
		
		if ( isset( $arrSubmit['option_sections'] ) ) {
			if ( isset( $arrSubmit['option_sections']['all'] ) && $arrSubmit['option_sections']['all'] ) {
				add_action( 'shutdown', array( $this, 'deleteOptions_All' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['genaral'] ) && $arrSubmit['option_sections']['general'] ) {
				add_action( 'shutdown', array( $this, 'deleteOptions_General' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['template'] ) && $arrSubmit['option_sections']['template'] ) {
				add_action( 'shutdown', array( $this, 'deleteOptions_Template' ), 999 );
			}		
		}
		
		return $arrOriginal;
		
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
	public function style_fetch_tweets_settings( $strStyle ) {
		
		return $strStyle . PHP_EOL
			. " .right-button {
					float: right;
				}
				input.read-only {
					background-color: #F6F6F6;
				}	
			";
		
	}
	public function style_fetch_tweets_templates( $strStyle ) {
		
		return $strStyle . PHP_EOL
			. " .widefat td	{
					vertical-align: middle;
				}
				.column-thumbnail {
					width: 20%;
				}
				.disabled {
					color: #C5C5C5;
				}
				.right-button {
					float: right;
				}

				.template-thumbnail{
					position: relative;
					z-index: 0;
				}
				.template-thumbnail:hover{
					background-color: transparent;
					z-index: 50;
				}
				.template-thumbnail span{ /*CSS for enlarged image*/
					position: fixed;
					background-color: #FCFCFC;
					padding: 5px;		
					border: 1px dashed gray;
					visibility: hidden;
					color: black;
					text-decoration: none;
				}
				.template-thumbnail span img{ /*CSS for enlarged image*/
					border-width: 0;
					padding: 2px;
					margin: 0 auto;
				}
				.template-thumbnail:hover span{ /*CSS for enlarged image on hover*/
					visibility: visible;
					top: 50px;				
				}				
			";
		
	}

	public function style_FetchTweets_AdminPage( $strStyle ) {
		return $strStyle . PHP_EOL	
			. ' .ftws_extension_container{
				padding-right: 30px;
				padding-left: 10px;
				margin-top: 10px;
				text-align: center;
			}
			.ftws_extension { 
				
			}
			.get-now {
				margin-bottom: 10px;
			}
			.ftws_extension_item {
				margin-right: 10px;
				padding: 20px 20px 20px 20px;
				background-color: #FAFAFA;
				border: 1px solid;
				border-color: #DDD;
				
			}
			.ftws_extension_item h4 {
				margin: 0.6em 0;	
			}
			.ftws_extension_item img {
				max-width: 150px;
				max-height: 200px;
			}
			'
			. '.fetch_tweets_multiple_columns {
				padding: 4px;
				line-height: 1.5em;
			}
			.fetch_tweets_multiple_columns_first_col {
				margin-left: 0px;
				clear: left;
			}
			/*  SECTIONS  ============================================================================= */
			.fetch_tweets_multiple_columns_row {
				clear: both;
				padding: 0px;
				margin: 0px;
			}
			/*  GROUPING  ============================================================================= */
			.fetch_tweets_multiple_columns_box:before,
			.fetch_tweets_multiple_columns_box:after {
				content:"";
				display:table;
			}
			.fetch_tweets_multiple_columns_box:after {
				clear:both;
			}
			.fetch_tweets_multiple_columns_box {
				float: none;
				width: 100%;		
				zoom:1; /* For IE 6/7 (trigger hasLayout) */
			}
			/*  GRID COLUMN SETUP   ==================================================================== */
			.fetch_tweets_multiple_columns_col {
				display: block;
				float:left;
				margin: 1% 0 1% 1.6%;
			}
			.fetch_tweets_multiple_columns_col:first-child { margin-left: 0; } /* all browsers except IE6 and lower */
			/*  REMOVE MARGINS AS ALL GO FULL WIDTH AT 800 PIXELS */
			@media only screen and (max-width: 800px) {
				.fetch_tweets_multiple_columns_col { 
					margin: 1% 0 1% 0%;
				}
			}
			/*  GRID OF TWO   ============================================================================= */
			.ftws_col_element_of_1 {
				width: 100%;
			}
			.ftws_col_element_of_2 {
				width: 49.2%;
			}
			.ftws_col_element_of_3 {
				width: 32.2%; 
			}
			.ftws_col_element_of_4 {
				width: 23.8%;
			}
			.ftws_col_element_of_5 {
				width: 18.72%;
			}
			.ftws_col_element_of_6 {
				width: 15.33%;
			}
			.ftws_col_element_of_7 {
				width: 12.91%;
			}
			.ftws_col_element_of_8 {
				width: 11.1%; 
			}
			.ftws_col_element_of_9 {
				width: 9.68%; 
			}
			.ftws_col_element_of_10 {
				width: 8.56%; 
			}
			.ftws_col_element_of_11 {
				width: 7.63%; 
			}
			.ftws_col_element_of_12 {
				width: 6.86%; 
			}

			/*  GO FULL WIDTH AT LESS THAN 800 PIXELS */
			@media only screen and (max-width: 800px) {
				.ftws_col_element_of_2,
				.ftws_col_element_of_3,
				.ftws_col_element_of_4,
				.ftws_col_element_of_5,
				.ftws_col_element_of_6,
				.ftws_col_element_of_7,
				.ftws_col_element_of_8,
				.ftws_col_element_of_9,
				.ftws_col_element_of_10,
				.ftws_col_element_of_11,
				.ftws_col_element_of_12
				{	width: 49.2%;  }			
			}
		';		
	}
		
}