<?php

abstract class FetchTweets_WidgetByID_ extends FetchTweets_Widget_ {


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

	protected function echoTweets( $arrInstance ) {
		
		fetchTweets( 
			array( 	// $arrArgs
				'ids'	=> $arrInstance['selected_ids'],
				'count' => $arrInstance['count'],
				// Template Options
				'template' => $arrInstance['template'],
				'avatar_size' => $arrInstance['avatar_size'],
				'height' => $arrInstance['height'],
				'height_unit' => $arrInstance['height_unit'],
				'width' => $arrInstance['width'],
				'width_unit' => $arrInstance['width_unit'],			
			)
		);
		
	}
	
	protected function echoFormElements( $arrInstance, $arrIDs, $arrNames ) {
	?>
		<label for="<?php echo $arrIDs['title']; ?>">
			<?php _e( 'Title', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="text" name="<?php echo $arrNames['title']; ?>" id="<?php echo $arrIDs['title']; ?>" value="<?php echo $arrInstance['title']?>"/>
		</p>
		
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
		<p>
			<input type="number" id="<?php echo $arrIDs['count']; ?>" name="<?php echo $arrNames['count']; ?>" min="1" value="<?php echo $arrInstance['count']?>"/>
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Default', 'fetch-tweets' ); ?>: 20
		</p>
		
		<p>
			<label for="<?php echo $arrIDs['template']; ?>">
				<?php _e( 'Select a Template', 'fetch-tweets' ); ?>:
			</label>
			<br />
			<select name="<?php echo $arrNames['template']; ?>" id="<?php echo $arrIDs['template']; ?>" >
				<?php 
				foreach( $GLOBALS['oFetchTweets_Templates']->getTemplateArrayForSelectLabel() as $strTemplateSlug => $strTemplateName ) 
					echo "<option value='{$strTemplateSlug}' "				
						. ( $arrInstance['template'] == $strTemplateSlug ? 'selected="Selected"' : '' )
						. ">"
						. $strTemplateName
						. "</option>";
				?>
			</select>
		</p>
				
		<label for="<?php echo $arrIDs['avatar_size']; ?>">
			<?php _e( 'The profile image size in pixel.', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="number" id="<?php echo $arrIDs['avatar_size']; ?>" name="<?php echo $arrNames['avatar_size']; ?>" min="0" value="<?php echo $arrInstance['avatar_size']?>"/>
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Set 0 for no avatar.', 'fetch-tweets' ); ?> <?php _e( 'Default', 'fetch-tweets' ); ?>: 48
		</p>

		<label for="<?php echo $arrIDs['width']; ?>">
			<?php _e( 'The width of the output.', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="number" id="<?php echo $arrIDs['width']; ?>" name="<?php echo $arrNames['width']; ?>" min="0" value="<?php echo $arrInstance['width']?>"/>
			<select name="<?php echo $arrNames['width_unit']; ?>" id="<?php echo $arrIDs['width_unit']; ?>" >
				<?php 
				foreach( array( 'px' => 'px', '%' => '%', 'em' => 'em' ) as $strUnitKey => $strUnitName ) 
					echo "<option value='{$strUnitKey}' "				
						. ( $arrInstance['width_unit'] == $strUnitKey ? 'selected="Selected"' : '' )
						. ">"
						. $strUnitName
						. "</option>";
				?>
			</select>						
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Set 0 for no limit.', 'fetch-tweets' ); ?> <?php _e( 'Default', 'fetch-tweets' ); ?>: <code>100 %</code>.
		</p>		
			
		<label for="<?php echo $arrIDs['height']; ?>">
			<?php _e( 'The height of the output.', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="number" id="<?php echo $arrIDs['height']; ?>" name="<?php echo $arrNames['height']; ?>" min="0" value="<?php echo $arrInstance['height']?>"/>
			<select name="<?php echo $arrNames['height_unit']; ?>" id="<?php echo $arrIDs['height_unit']; ?>" >
				<?php 
				foreach( array( 'px' => 'px', '%' => '%', 'em' => 'em' ) as $strUnitKey => $strUnitName ) 
					echo "<option value='{$strUnitKey}' "				
						. ( $arrInstance['height_unit'] == $strUnitKey ? 'selected="Selected"' : '' )
						. ">"
						. $strUnitName
						. "</option>";
				?>
			</select>						
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Set 0 for no limit.', 'fetch-tweets' ); ?> <?php _e( 'Default', 'fetch-tweets' ); ?>: <code>400 px</code>.
		</p>			
	<?php
	}
	
	public function update( $arrNewInstance, $arrOldInstance ) {
		
		$arrNewInstance['count'] = $this->fixNumber( $arrNewInstance['count'], 20, 1 );
		$arrNewInstance['avatar_size'] = $this->fixNumber( $arrNewInstance['avatar_size'], 48, 0 );

        return $arrNewInstance;
    }
	
}