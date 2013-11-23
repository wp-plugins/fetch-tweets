<?php
/**
 * Creates Fetch Tweets Accounts post type
 * 
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.3.4
 * 
 */
abstract class FetchTweets_PostType_Accounts_ extends FetchTweets_AdminPageFramework_PostType {

	public function start_FetchTweets_PostType_Accounts() {

		$this->setPostTypeArgs(
			array(			// argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
				'labels' => array(
					'name' => __( 'Accounts', 'fetch-tweets' ),
					'singular_name' => __( 'Accounts', 'fetch-tweets' ),
					'menu_name' => __( 'Manage Accounts', 'fetch-tweets' ),	// this changes the root menu name 
					'add_new' => __( 'Add New Accounts', 'fetch-tweets' ),
					'add_new_item' => __( 'Add New Accounts', 'fetch-tweets' ),
					'edit' => __( 'Edit', 'fetch-tweets' ),
					'edit_item' => __( 'Edit Accounts', 'fetch-tweets' ),
					'new_item' => __( 'New Accounts', 'fetch-tweets' ),
					'view' => __( 'View', 'fetch-tweets' ),
					'view_item' => __( 'View Accounts', 'fetch-tweets' ),
					'search_items' => __( 'Search Accounts Definitions', 'fetch-tweets' ),
					'not_found' => __( 'No definitions found for Accounts', 'fetch-tweets' ),
					'not_found_in_trash' => __( 'No definitions Found for Accounts in Trash', 'fetch-tweets' ),
					'parent' => __( 'Parent Accounts', 'fetch-tweets' ),
				),
				'public' => false,
				// 'menu_position' => 120,
				'supports' => array( 'title' ),	// 'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),	// 'custom-fields'
				'taxonomies' => array( '' ),
				'menu_icon' => FetchTweets_Commons::getPluginURL( '/image/menu_icon_16x16.png' ),
				'screen_icon' => FetchTweets_Commons::getPluginURL( "/image/screen_icon_32x32.png" ),				
				'has_archive' => false,
				'hierarchical' => false,
				'show_admin_column' => true,
				'exclude_from_search' => true,	// Whether to exclude posts with this post type from front end search results.
				'publicly_queryable' => false,	// Whether queries can be performed on the front end as part of parse_request(). 
				'show_ui' => false,
				'show_in_nav_menus' => false,
				'show_in_menu' => false,
			)		
		);
		
		
		if ( is_admin() ) {
			
			// Check custom actions
			if ( $GLOBALS['pagenow'] == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == FetchTweets_Commons::PostTypeSlugAccounts ) {

					// add_filter( 'post_row_actions', array( $this, 'modifyRowActions' ), 10, 2 );
				// add_filter( 'bulk_actions-edit-' . $this->oProps->strPostType, array( $this, 'modifyBulkActionsDropDownList' ) );
			
				// $this->setAutoSave( false );
				$this->setAuthorTableFilter( false );
						
				// $this->handleCustomActions();
								
			}
			
			// Modify the link text in the plugin listing page
			if ( isset( $this->oLink ) )	// if not in admin or the post type slug is not set, the oLink object won't be set.
				$this->oLink->strSettingPageLinkTitle = __( 'Accounts', 'fetch-tweets' );
						
			// For the "Add New Accounts" link, disable the default one and redirect to the plugin's setting page.
			if ( $GLOBALS['pagenow'] == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == FetchTweets_Commons::PostTypeSlugAccounts  ) 
				die( 
					wp_redirect( 
						add_query_arg( 
							array( 
								'post_type' => FetchTweets_Commons::PostTypeSlug, 	// the plugin admin pages are registered under the main plugin post type slug.
								'page' => 'fetch_tweets_add_new_account', 
							),
							admin_url( 'edit.php' ) 	// post type pages use edit.php
						) 
					) 
				);

		}
		
	}
	
	
	/*
	 * Extensible methods
	 */
	public function setColumnHeader( $arrColumnHeader ) {
		// Set the table header.
		return array(
			'cb'				=> '<input type="checkbox" />',	// Checkbox for bulk actions. 
			// 'title'				=> __( 'Auto Insert Name', 'fetch-tweets' ),		// Post title. Includes "edit", "quick edit", "trash" and "view" links. If $mode (set from $_REQUEST['mode']) is 'excerpt', a post excerpt is included between the title and links.
			
			// 'status'		=> __( 'Status', 'fetch-tweets' ),
			// 'unit_type'			=> __( 'Unit Type', 'fetch-tweets' ),
			// 'author'			=> __( 'Author', 'fetch-tweets' ),		// Post author.
			// 'code'				=> __( 'Shortcode / PHP Code', 'fetch-tweets' ),
			// 'date'			=> __( 'Date', 'fetch-tweets' ), 	// The date and publish status of the post. 
		);		
		// return array_merge( $arrColumnHeader, $this->arrColumnHeaders );
	}
	public function setSortableColumns( $arrColumns ) {
		return array_merge( $arrColumns, $this->oProps->arrColumnSortable );		
	}		

}