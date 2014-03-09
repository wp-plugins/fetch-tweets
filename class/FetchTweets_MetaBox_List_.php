<?php
class FetchTweets_MetaBox_List_ extends FetchTweets_AdminPageFramework_MetaBox {
	
	/**
	 * Adds form fields for the options to fetch tweets by list to the meta box.
	 * 
	 * @since			1.2.0
	 */ 
	public function setUp() {
		
		$strScreenName = $this->getScreenName();
		$arrLists = $this->getLists( $strScreenName );
		
		$this->addSettingFields(		
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'list',
				'hidden'		=>	true,
			),			
			array(
				'field_id'		=> 'list_id',
				'title'			=> __( 'Lists', 'fetch-tweets' ),
				'type'			=> 'select',
				'label'			=> $arrLists,
			),
			array(	// non-used fields must be set as hidden since the callback function will assign a value.
				'field_id'		=> 'screen_name',
				'type'			=> 'hidden',
				'value'			=> $strScreenName,
				'hidden'		=>	true,
			),				
			array(
				'field_id'		=> 'search_keyword',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	100,
				),				
			),				
			array(
				'field_id'		=> 'language',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),				
			array(
				'field_id'		=> 'result_type',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),		
			array(
				'field_id'		=> 'exclude_replies',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(
				'field_id'		=> 'include_retweets',
				'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'type'			=> 'checkbox',
			),				
			array(	// since 1.3.3
				'field_id'		=> 'until',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),		
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_coordinate',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_radius',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),					
			array()
		);
				
		
	}
		/**
		 * Returns an array of lists received from the previous page; otherwise, fetches lists from the set screen name.
		 * 
		 */	 
		protected function getLists( $strScreenName='' ) {
			
			// If the cache is set from the previous page, use that.
			$strListTransient = isset( $_GET['list_cache'] ) ? $_GET['list_cache'] : '';
			if ( ! empty( $strListTransient ) ) {
				$arrLists = ( array ) get_transient( $strListTransient );
				delete_transient( $strListTransient );
				return $arrLists;
			}
			
			if ( empty( $strScreenName ) ) return array();	
			
			// Fetch lists from the given screen name.
			$oFetch = new FetchTweets_Fetch;
			$arrLists = $oFetch->getListNamesFromScreenName( $strScreenName );
			return $arrLists;
			
		}
		/**
		 * Returns the associated screen name (twitter user name) of the post.
		 * 
		 * @return			string				The screen name associated with the post.
		 * @since			1.2.0
		 */
		protected function getScreenName() {
			
			// If the 'action' query value is edit, search for the meta field value which previously set when it is saved.
			if ( isset( $_GET['action'], $_GET['post'] ) && $_GET['action'] == 'edit' ) 
				return get_post_meta( $_GET['post'], 'screen_name', true );
		
			// If the GET 'tweet_type' query value is set, use it.
			if ( isset( $_GET['screen_name'] ) && $_GET['screen_name'] ) return $_GET['screen_name'];
			
			return '';
			
		}
	
	public function validation_FetchTweets_MetaBox_Options( $arrInput ) {	// validation_ + extended class name
			
		$arrInput['item_count'] = $this->oUtil->fixNumber( 
			$arrInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			200
		);

		return $arrInput;
		
	}
	
}
