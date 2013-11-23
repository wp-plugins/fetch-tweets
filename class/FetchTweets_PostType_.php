<?php

abstract class FetchTweets_PostType_ extends FetchTweets_AdminPageFramework_PostType {
	
	// public function setUp() {
	public function start_FetchTweets_PostType() {

		$this->setPostTypeArgs(
			array(			// argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
				'labels' => array(
					'name' => __( 'Fetch Tweets', 'fetch-tweets' ),
					'all_items' => __( 'Manage Rules', 'fetch-tweets' ),	// sub menu label
					'singular_name' => __( 'Fetch Tweets Rule', 'fetch-tweets' ),
					'menu_name' => __( 'Fetch Tweets', 'fetch-tweets' ),	// this changes the root menu name 
					'add_new' => __( 'Fetch Tweets by Screen Name', 'fetch-tweets' ),
					'add_new_item' => __( 'Add New Rule', 'fetch-tweets' ),
					'edit' => __( 'Edit', 'fetch-tweets' ),
					'edit_item' => __( 'Edit Rule', 'fetch-tweets' ),
					'new_item' => __( 'New Rule', 'fetch-tweets' ),
					'view' => __( 'View', 'fetch-tweets' ),
					'view_item' => __( 'View Fetched Tweets', 'fetch-tweets' ),
					'search_items' => __( 'Search Rules', 'fetch-tweets' ),
					'not_found' => __( 'No rule found for fetching tweets', 'fetch-tweets' ),
					'not_found_in_trash' => __( 'No Rule Found for Fetching Tweets in Trash', 'fetch-tweets' ),
					'parent' => 'Parent Rule'
				),
				'public' => true,
				'menu_position' => 110,
				// 'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),	// 'custom-fields'
				'supports' => array( 'title' ),
				'taxonomies' => array( '' ),
				'menu_icon' => FetchTweets_Commons::getPluginURL( '/image/menu_icon_16x16.png' ),
				'has_archive' => true,
				'hierarchical' => false,
				'show_admin_column' => true,
				'screen_icon' => FetchTweets_Commons::getPluginURL( "/image/screen_icon_32x32.png" ),
			)		
		);

		$this->addTaxonomy( 
			'fetch_tweets_tag', 
			array(
				'labels' => array(
					'name' => __( 'Tags', 'fetch-tweets' ),
					'add_new_item' => __( 'Add New Tag', 'fetch-tweets' ),
					'new_item_name' => __( 'New Tag', 'fetch-tweets' ),
				),
				'show_ui' => true,
				'show_tagcloud' => false,
				'hierarchical' => false,
				'show_admin_column' => true,
				'show_in_nav_menus' => false,
				'show_table_filter' => true,		// framework specific key
				'show_in_sidebar_menus' => true,	// framework specific key
			)
		);
		
		$strCurrentPostTypeInAdmin = isset( $GLOBALS['post_type'] ) ? $GLOBALS['post_type']
			: isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
		
		// For admin
		if ( $strCurrentPostTypeInAdmin == $this->oProps->strPostType && is_admin() ) {
			
			$this->setAutoSave( false );
			$this->setAuthorTableFilter( true );			
			add_filter( 'enter_title_here', array( $this, 'changeTitleMetaBoxFieldLabel' ) );	// add_filter( 'gettext', array( $this, 'changeTitleMetaBoxFieldLabel' ) );
			add_action( 'edit_form_after_title', array( $this, 'addTextAfterTitle' ) );	
			// add_action( 'admin_menu', array( $this, 'editSidebarSubMenuLabel' ) );
		}
		
		add_filter( 'the_content', array( $this, 'previewTweets' ) );	
				
	}
	
	public function changeTitleMetaBoxFieldLabel( $strText ) {
		return __( 'Set the rule name here.', 'fetch-tweets' );		
	}
	public function addTextAfterTitle() {
		
		$oUserAds = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		echo $oUserAds->getTextAd();
		
		// Text links will be inserted here.
	}
	
	public function editSidebarSubMenuLabel() {
		
		// Changes the menu label of the first item.
		// foreach () {
			
			
		// }
	}
	
	/*
	 * Methods to print out the fetched tweets.
	 * */
	public function previewTweets( $strContent ) {

		global $post;
		// Used for the post type single page that functions as preview the result.
// FetchTweets_Debug::logArray( 
	// array( 
		// 'Preview Called: ' . $GLOBALS['pagenow'],
		// 'Post ID: ' . isset( $post, $post->ID ) ? $post->ID : '',
	// )
// );	

		if ( ! isset( $post->post_type ) || $post->post_type != $this->oProps->strPostType ) return $strContent;
// FetchTweets_Debug::logArray( 'Start previewing' );
		$intPostID = $post->ID;
		$intCount = get_post_meta( $intPostID, 'item_count', true );
		fetchTweets( array( 'id' => $intPostID, 'count' => $intCount ) );	// this draws the result.
// FetchTweets_Debug::logArray( 'Returning the preview output' );		
		return $strContent;	// should be an empty string.
	
	}

	/*
	 * Extensible methods
	 */
	public function setColumnHeader( $arrColumnHeader ) {
		// Set the table header.
		return array(
			'cb'				=> '<input type="checkbox" />',	// Checkbox for bulk actions. 
			'title'				=> __( 'Rule Name', 'fetch-tweets' ),		// Post title. Includes "edit", "quick edit", "trash" and "view" links. If $mode (set from $_REQUEST['mode']) is 'excerpt', a post excerpt is included between the title and links.
			'tweettype'			=> __( 'Tweet Type', 'fetch-tweets' ),
			// 'author'			=> __( 'Author', 'fetch-tweets' ),		// Post author.
			'fetch_tweets_tag'	=> __( 'Tags', 'fetch-tweets' ),	// Tags for the post. 
			'code'				=> __( 'Shortcode / PHP Code', 'fetch-tweets' ),
			// 'date'			=> __( 'Date', 'fetch-tweets' ), 	// The date and publish status of the post. 
		);		
		// return array_merge( $arrColumnHeader, $this->arrColumnHeaders );
	}
	public function setSortableColumns( $arrColumns ) {
		return array_merge( $arrColumns, $this->oProps->arrColumnSortable );		
	}	
	
	/*
	 * Callback methods
	 */
	public function cell_fetch_tweets_fetch_tweets_tag( $strCell, $intPostID ) {
		
		// Get the genres for the post.
		$arrTerms = get_the_terms( $intPostID, FetchTweets_Commons::TagSlug );
	
		// If no tag is assigned to the post,
		if ( empty( $arrTerms ) ) return '—';
		
		// Variables
		global $post;
		$arrOutput = array();
	
		// Loop through each term, linking to the 'edit posts' page for the specific term. 
		foreach ( $arrTerms as $oTerm ) {
			$arrOutput[] = sprintf( '<a href="%s">%s</a>',
				esc_url( add_query_arg( array( 'post_type' => $post->post_type, FetchTweets_Commons::TagSlug => $oTerm->slug ), 'edit.php' ) ),
				esc_html( sanitize_term_field( 'name', $oTerm->name, $oTerm->term_id, FetchTweets_Commons::TagSlug, 'display' ) )
			);
		}

		// Join the terms, separating them with a comma.
		return join( ', ', $arrOutput );
		
	}
	public function cell_fetch_tweets_tweettype( $strCell, $intPostID ) {
		
		switch ( get_post_meta( $intPostID, 'tweet_type', true ) ) {
			case 'search':
				return __( 'Search', 'fetch-tweets' );
			case 'screen_name':
				return __( 'User Name', 'fetch-tweets' );
			case 'list':
				return __( 'List', 'fetch-tweets' );
		}
		
	}
	public function cell_fetch_tweets_code( $strCell, $intPostID ) {
		return '<p>'
			. '<span>[fetch_tweets id="' . $intPostID . '"]</span>' . '<br />'
			. '<span>&lt;?php fetchTweets( array( ‘id’ =&gt; ' . $intPostID . ' ) ); ?&gt;</span>'
			// . '<span>FetchTweets( ‘id’ =&gt; ' . $intPostID . ' );</span>'
		
			. '</p>';
	}
	

}

