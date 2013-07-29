<?php
abstract class FetchTweets_AdminPage_ extends AdminPageFramework {

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
				'strTitle'			=> 'Authentication Keys',
				'strDescription'	=> 'These keys are required to process oAuth requests of the twitter API.',
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
	public function do_fetch_tweets_extensions() {
				
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
	public function style_fetch_tweets_extensions( $strStyle ) {
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
				width: 100%;
				height: 100%;
				max-width: 200px;
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
	
	public function do_fetch_tweets_test() {
		

return;
// echo 'screen_name: ' . get_post_meta( 142, 'screen_name', true ) . '<br />';
// echo 'tweet_type: ' . get_post_meta( 142, 'tweet_type', true ) . '<br />';
// echo 'item_count: ' . get_post_meta( 142, 'item_count', true ) . '<br />';
// return;	
		$oOption = & $GLOBALS['oFetchTweets_Option'];
		
		$strUser = "miunosoft";
		$intCount = 30;	
		$strConsumerKey = $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['consumer_key'];
		$strConsumerSecret = $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['consumer_secret'];
		$strAccessToken = $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['access_token'];
		$strAccessTokenSecret = $oOption->arrOptions['fetch_tweets_settings']['authentication_keys']['access_secret'];
		$strLang = 'en';
		
		echo '<h3>Tweets API</h3>';

		// session_start();
		// require_once("twitteroauth/twitteroauth/twitteroauth.php"); //Path to twitteroauth library
		include_once( dirname( FetchTweets_Commons::getPluginFilePath() ) . '/library/WP_TwitterOAuth.php' );			
		$oTweeterOAuth =  new WP_TwitterOAuth( $strConsumerKey, $strConsumerSecret, $strAccessToken, $strAccessTokenSecret );

		// $arrTweets = $oTweeterOAuth->get( "https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name={$strUser}&count={$intCount}" );
		$arrTweets = ( array ) $oTweeterOAuth->get( "https://api.twitter.com/1.1/search/tweets.json?q=wordpress&result_type=mixed&count=4&lang={$strLang}" );
// ttp://search.twitter.com/search.json?q=%23baseball&result_type=recent
		echo $this->oDebug->getArray( $arrTweets );
		
	}
	
	
}