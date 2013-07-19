<?php

abstract class FetchTweets_WidgetByID_ extends WP_Widget {

	public static function registerWidget() {
		return register_widget( 'FetchTweets_WidgetByID' );	// the class name - get_class( self ) does not work.
	}	
	
	public function __construct() {
						
		parent::__construct(
	 		'fetch_tweets_widget_by_id', // base ID
			'Fetch Tweets by Rule Set', 	// widget name
			array( 'description' => __( 'A widget that fetches tweets by rule set.', 'fetch-tweets' ), ) 
		);
		
	}

	public function widget( $arrWidgetInfo, $arrInstance ) {	// must be public, the protected scope will cause fatal error.
		
		echo $arrWidgetInfo['before_widget']; 
		
		// Aboid undefined index warnings.
		$arrInstance = $arrInstance + array(
			'selected_ids' => null,
			'count'	=> null,
		);
		
		echo fetchTweets( 
			array( 
				'ids'	=> $arrInstance['selected_ids'],
				'count' => $arrInstance['count'],
			) 
		);
		// echo "<p>Coming Soon!</p>";
		// echo $this->getArray( $arrWidgetInfo );
		// echo $this->getArray( $arrInstance );
		
		echo $arrWidgetInfo['after_widget'];
		
	}	
	
	public function form( $arrInstance ) {	
					
		// Aboid undefined index warnings.
		$arrInstance = $arrInstance + array(
			'selected_ids' => null,
			'count'	=> 20,	// default
		);
		
		$arrIDs = array();
		$arrNames = array();
		$arrIDs['selected_ids'] = $this->get_field_id( 'selected_ids' );	
		$arrNames['selected_ids'] = $this->get_field_name( 'selected_ids' );	
		$arrIDs['count'] = $this->get_field_id( 'count' );	
		$arrNames['count'] = $this->get_field_name( 'count' );	
		
		?>
		<label for="<?php echo $arrIDs['selected_ids']; ?>">
			<?php _e( 'Select Rules', 'fetch-tweets' ); ?>:
		</label>
		<br />
		<select name="<?php echo $arrNames['selected_ids']; ?>[]" id="<?php echo $arrIDs['selected_ids']; ?>"  multiple style="min-width: 220px;">
			<?php 
			$oQuery = new WP_Query(
				array(
					'post_status' => 'publish', 	// optional
					'post_type' => FetchTweets_Commons::PostTypeSlug,// 'fetch_tweets', //  post_type
					'posts_per_page' => -1, // ALL posts
				)
			);			
			foreach( $oQuery->posts as $oPost ) 
				echo "<option value='{$oPost->ID}' "				
					. ( in_array( $oPost->ID, $arrInstance['selected_ids'] ) ? 'selected="Selected"' : '' )
					. ">"
					. $oPost->post_title
					. "</option>";
			?>
		</select>
		<p class="description" style="margin-top: 10px;">
			<?php _e( 'Hold down the Ctrl (windows) / Command (Mac) key to select multiple items.', 'fetch-tweets' ); ?>
		</p>	 
		<label for="<?php echo $arrIDs['count']; ?>">
			<?php _e( 'The maximum number of tweets to show', 'fetch-tweets' ); ?>:
		</label>
		<br />
		<input type="number" id="<?php echo $arrIDs['count']; ?>" name="<?php echo $arrNames['count']; ?>" min="1" value="<?php echo $arrInstance['count']?>"/>
		<?php
		
	}
	
	public function update( $arrNewInstance, $arrOldInstance ) {
// $oDebug = new FetchTweets_Debug;
// $oDebug->getArray( $arrNewInstance, dirname( __FILE__ ) . '/widget_form_submitted_array.txt' );
// $oDebug->getArray( $_POST, dirname( __FILE__ ) . '/post.txt' );
        return $arrNewInstance;
	
    }
	
	/*
	 * Methods for Debug
	 * */
	function getArray( $arr ) {
		
		return '<pre>' . htmlspecialchars( print_r( $arr, true ) ) . '</pre>';
		
	}		
}