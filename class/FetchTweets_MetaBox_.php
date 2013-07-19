<?php
class FetchTweets_MetaBox_ extends AdminPageFramework_MetaBox {
	
	public function setUp() {
		
		switch ( $this->getTweetType() ) {
			case 'search':
				$this->addFieldsForTweetsBySearch();
				break;
			case 'screen_name':
			default:	
				$this->addFieldsForTweetsByScreenName();
				break;				
		}			

		$this->addSettingFields(			
			array(
				'strFieldID'		=> 'include_retweets',
				'strTitle'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'vLabel'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
			),							
			array(
				'strFieldID'		=> 'cache_duration',
				'strTitle'			=> __( 'Cache Duration', 'fetch-tweets' ),
				'strDescription'	=> __( 'The cache lifespan in seconds. For no cache, set 0.', 'fetch-tweets' ) . ' ' . __( 'Default:', 'fetch-tweets' ) . ': 1200',
				'strType'			=> 'number',
				'vDefault'			=> 60 * 20,	// 20 minutes
			),			
			// array(
				// 'strFieldID'		=> 'template_path',
				// 'strTitle'			=> __( 'Template', 'fetch-tweets' ),
				// 'strDescription'	=> __( 'Set the template path to layout the output of the fetched tweet ocntents. In other words, you can modify how they are displayed with a template file.', 'fetch-tweets' ),
				// 'vDefault'			=> dirname( FetchTweets_Commons::getPluginFilePath() ) . '/template/fetch-tweets-by-user-name.php',
				// 'vSize'				=> 120,
				// 'strType'			=> 'text',
			// ),
			array()
		);
		
		// Remove the default tag meta box and add a custom meta box.
		add_action( 'admin_menu', array( $this, 'removeDefaultMetaBoxes' ) );
		add_action( 'add_meta_boxes', array( $this, 'addCustomMetaBoxes' ) );
		
	}
	
	public function addCustomMetaBoxes() {
		
		// The wider tag meta box.
		add_meta_box( 
			'tagsdiv-' . FetchTweets_Commons::Tag . '-2', 		// id
			__( 'Tags', 'fetch-tweets' ), 	// title
			array( $this, 'drawTagBox' ), 	// callback
			FetchTweets_Commons::PostTypeSlug,		// post type
			'advanced', 	// context ('normal', 'advanced', or 'side'). 
			'low',	// priority ('high', 'core', 'default' or 'low') 
			null // argument
		);
		
		// Sponsors' box.
		add_meta_box( 
			'miunosoft-sponsors', 		// id
			__( 'Information', 'fetch-tweets' ), 	// title
			array( $this, 'callSponsors' ), 	// callback
			FetchTweets_Commons::PostTypeSlug,		// post type
			'side', 	// context ('normal', 'advanced', or 'side'). 
			'low',	// priority ('high', 'core', 'default' or 'low') 
			null // argument
		);	
	}
	public function callSponsors() {
		
		$oUserAds = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		echo rand ( 0 , 1 )
			? $oUserAds->get250xNTopRight() 
			: $oUserAds->get250xN( 2 );
			
	}
	public function drawTagBox() {
		?>
		<div class="keywords inside">
		<?php
			post_tags_meta_box( 
				get_post( $GLOBALS['post_ID'] ), 
				array(
					'args' => array(
						'taxonomy' => FetchTweets_Commons::Tag,
					)
				)
			);
		?>
		</div>
		<?php
	}
	
	public function removeDefaultMetaBoxes() {
		
		// Remove 'Keywords' (like tags) metabox
		$strTaxonomySlug = FetchTweets_Commons::Tag;
		remove_meta_box( "tagsdiv-{$strTaxonomySlug}", FetchTweets_Commons::PostTypeSlug, 'side' );
		
		// Remove 'Groups' (like categories) metabox
		// remove_meta_box( 'groupdiv', 'my-custom-post-type-slug', 'side' );
	}	
	
	private function getTweetType() {

		// If the 'action' query value is edit, search for the meta field value which previously set when it is saved.
		if ( isset( $_GET['action'], $_GET['post'] ) && $_GET['action'] == 'edit' ) 
			return get_post_meta( $_GET['post'], 'tweet_type', true );
	
		// If the GET 'tweet_type' query value is set, use it.
		if ( isset( $_GET['tweet_type'] ) && $_GET['tweet_type'] ) return $_GET['tweet_type'];
		
		// return the default type
		return 'screen_name';
		
	}	
	protected function addFieldsForTweetsByScreenName() {
		$this->addSettingFields(
			array(
				'strFieldID'		=> 'tweet_type',
				'strType'			=> 'hidden',
				'vValue'			=> 'screen_name',
			),						
			array(
				'strFieldID'		=> 'screen_name',
				'strTitle'			=> __( 'User Name', 'fetch-tweets' ),
				'strDescription'	=> __( 'The user name (screen name) that is used after the @ mark or the end of the twitter url.', 'fetch-tweets' ) . '',
				'strType'			=> 'text',
			),	
			array(
				'strFieldID'		=> 'item_count',
				'strTitle'			=> __( 'Item Count', 'fetch-tweets' ),
				'strDescription'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 200 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'strType'			=> 'number',
				'vDefault'			=> 20,
				'vMax'				=> 200,				
			),				
			array(
				'strFieldID'		=> 'search_keyword',
				'strType'			=> 'hidden',
			),		
			array(
				'strFieldID'		=> 'language',
				'strType'			=> 'hidden',
			),				
			array (
				'strFieldID'		=> 'result_type',
				'strType'			=> 'hidden',
			),			
			array(
				'strFieldID'		=> 'exclude_replies',
				'strTitle'			=> 'Exclude Replies',
				'strType'			=> 'checkbox',
				'vLabel'			=> __( 'This prevents replies from appearing in the returned timeline.', 'fetch-tweets' ),
			),		
			// array (
				// 'strFieldID'		=> 'checkbox_group_field',
				// 'strTitle'		=> 'Checkbox Group',
				// 'strDescription'	=> 'The description for the field.',
				// 'strType'		=> 'checkbox',
				// 'vLabel' => array( 
					// 'one' => __( 'Option One', 'demo' ),
					// 'two' => __( 'Option Two', 'demo' ),
					// 'three' => __( 'Option Three', 'demo' ),
				// ),
				// 'vDefault' => array(
					// 'one' => true,
					// 'two' => false,
					// 'three' => false,
				// ),
			// ),			
			// array (
				// 'strFieldID'		=> 'image_field',
				// 'strTitle'			=> 'Image',
				// 'strDescription'	=> 'The description for the field.',
				// 'strType'			=> 'image',
			// ),			
			array()
		);	
	
	}
	protected function addFieldsForTweetsBySearch() {
		
		$this->addSettingFields(		
			array(
				'strFieldID'		=> 'tweet_type',
				'strType'			=> 'hidden',
				'vValue'			=> 'search',
			),			
			array(	// non-used fields must be set as hidden since the callback function will assign a value.
				'strFieldID'		=> 'screen_name',
				'strType'			=> 'hidden',
			),				
			array(
				'strFieldID'		=> 'search_keyword',
				'strTitle'			=> __( 'Search Keyword', 'fetch-tweets' ),
				'strDescription'	=> sprintf( __( 'The keyword to search. For a complex combination of terms and operators, refer to the <strong>Search Operators</strong> section of <a href="%1$s" target="_blank">Using the Twitter Search API</a>.', 'fetch-tweets' ), 'https://dev.twitter.com/docs/using-search' ) 
					. ' e.g. <code>love OR hate</code>, <code>#wordpress</code>',
				'strType'			=> 'text',
			),
			array(
				'strFieldID'		=> 'item_count',
				'strTitle'			=> __( 'Item Count', 'fetch-tweets' ),
				'strDescription'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'strType'			=> 'number',
				'vDefault'			=> 20,
				'vMax'				=> 100,				
			),				
			array(
				'strFieldID'		=> 'language',
				'strTitle'			=> 'Language ',
				'strType'			=> 'select',
				'vLabel' => array( 
					'none' => __( 'None', 'fetch-tweets' ),
					'pt' => __( 'Portuguese', 'fetch-tweets' ),
					'it' => __( 'Italian', 'fetch-tweets' ),
					'es' => __( 'Spanish', 'fetch-tweets' ),
					'tr' => __( 'Turkish', 'fetch-tweets' ),
					'en' => __( 'English', 'fetch-tweets' ),
					'ko' => __( 'Korean', 'fetch-tweets' ),
					'fr' => __( 'French', 'fetch-tweets' ),
					'ru' => __( 'Russian', 'fetch-tweets' ),
					'de' => __( 'German', 'fetch-tweets' ),
					'ja' => __( 'Japanese', 'fetch-tweets' ),
				),
				'vDefault' 			=> 'none',	// 0 means the first item
			),				
			array(
				'strFieldID'		=> 'result_type',
				'strTitle'			=> 'Result Type',
				'strType'			=> 'radio',
				'vLabel' => array( 
					'mixed' => 'mixed' . ' - ' . __( 'includes both popular and real time results in the response.', 'fetch-tweets' ),
					'recent' => 'recent' . ' - ' . __( 'returns only the most recent results in the response.', 'fetch-tweets' ),
					'popular' => 'popular' . ' - ' . __( 'return only the most popular results in the response.', 'fetch-tweets' ),
				),
				'vDefault' => 'mixed',
			),		
			array(
				'strFieldID'		=> 'exclude_replies',
				'strType'			=> 'hidden',
			),
			// result_type
			array()
		);
		
	}	

	public function validation_FetchTweets_MetaBox( $arrInput ) {	// validation_ + extended class name
			
		$arrInput['item_count'] = $this->oUtil->fixNumber( 
			$arrInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			$arrInput['tweet_type'] == 'search' ? 100 : 200 	// max
		);
		
		$arrInput['item_count'] = $this->oUtil->fixNumber(
			$arrInput['item_count'], // number to sanitize
			300,	// default
			0	// min
		);
		
		return $arrInput;
		
	}
	
}
