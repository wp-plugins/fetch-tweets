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
		
		submit_button();
		
	}
	
	public function buildMenus() {
	
		parent::buildMenus();
		
		// Remove the default post type menu item.
		foreach ( $GLOBALS['submenu'][ $this->oProps->arrRootMenu['strPageSlug'] ] as $intIndex => $arrSubMenu ) 
			if ( $arrSubMenu[ 2 ] == 'post-new.php?post_type=fetch_tweets' )
				unset( $GLOBALS['submenu'][ $this->oProps->arrRootMenu['strPageSlug'] ][ $intIndex ] );
					
	}

	public function do_fetch_tweets_extensions() {
		echo "<h3>Coming Soon...</h3>";
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