<?php

abstract class FetchTweets_WidgetByTag_ extends WP_Widget {
	
	// Properties
	protected $arrStructure_FormElements = array(
		'title'	=> null,
		'selected_tag_slugs' => array(),
		'count'	=> 20,	// default
		'avatar_size' => 48,
		'operator' => 'AND',
	);

	public static function registerWidget() {
		return register_widget( 'FetchTweets_WidgetByTag' );	// the class name - get_class( self ) does not work.
	}	
	
	public function __construct() {
						
		parent::__construct(
	 		'fetch_tweets_widget_by_tag', // base ID
			'Fetch Tweets by Tag', 	// widget name
			array( 'description' => __( 'A widget that fetches tweets by tag.', 'fetch-tweets' ), ) 
		);
		
	}

	public function widget( $arrWidgetInfo, $arrInstance ) {	// must be public, the protected scope will cause fatal error.
		
		echo $arrWidgetInfo['before_widget']; 
		
		// Avoid undefined index warnings.
		$arrInstance = $arrInstance + $this->arrStructure_FormElements;

		if ( $arrInstance['title'] )
			echo "<h3 class='fetch-tweets-widget'>{$arrInstance['title']}</h3>";

		echo fetchTweets( 
			array( 	
				// this argument takes term 'name' if the strFieldType key is not specified.
				'tags'	=> $arrInstance['selected_tag_slugs'] + array( 'strFieldType' => 'slug' ),	// strFieldType can be either slug or id.	
				'count' => $arrInstance['count'],
				'avatar_size' => $arrInstance['avatar_size'],
				'operator'	=> $arrInstance['operator'],
			) 
		);
		
		echo $arrWidgetInfo['after_widget'];
		
	}	
		

	public function form( $arrInstance ) {	
					
		// Avoid undefined index warnings.
		$arrInstance = $arrInstance + $this->arrStructure_FormElements;
		$arrIDs = $this->getFieldValues( 'id' );
		$arrNames = $this->getFieldValues( 'name' );
		
		?>
		<label for="<?php echo $arrIDs['title']; ?>">
			<?php _e( 'Title', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="text" name="<?php echo $arrNames['title']; ?>" id="<?php echo $arrIDs['title']; ?>" value="<?php echo $arrInstance['title']?>"/>
		</p>
		
		<label for="<?php echo $arrIDs['selected_tag_slugs']; ?>">
			<?php _e( 'Select Tags', 'fetch-tweets' ); ?>:
		</label>
		<br />
		<select name="<?php echo $arrNames['selected_tag_slugs']; ?>[]" id="<?php echo $arrIDs['selected_tag_slugs']; ?>"  multiple style="min-width: 220px;">
			<?php 
			foreach( $this->getTagSlugArrays() as $strTagSlug => $strTagName ) 
				echo "<option value='{$strTagSlug}' "				
					. ( in_array( $strTagSlug, $arrInstance['selected_tag_slugs'] ) ? 'selected="Selected"' : '' )
					. ">"
					. $strTagName
					. "</option>";
			?>
		</select>
		<p class="description" style="margin-top: 10px;">
			<?php _e( 'Hold down the Ctrl (windows) / Command (Mac) key to select multiple items.', 'fetch-tweets' ); ?>
		</p>	 
		
		<p>
		<?php _e( 'Apply the rule sets that have:', 'fetch-tweets' ); ?>
			<span style="display: block; margin: 8px;">
				<input id="<?php echo $arrIDs['operator']; ?>[0]" type="radio" name="<?php echo $arrNames['operator']; ?>" value="AND" <?php echo $arrInstance['operator'] == 'AND' ? "Checked" : ""; ?> />
				<label for="<?php echo $arrIDs['operator']; ?>[0]">&nbsp;<?php _e( 'All', 'fetch-tweets' ); ?></label>
				&nbsp;&nbsp;
				<input id="<?php echo $arrIDs['operator']; ?>[1]" type="radio" name="<?php echo $arrNames['operator']; ?>" value="IN" <?php echo $arrInstance['operator'] == 'IN' ? "Checked" : ""; ?> />
				<label for="<?php echo $arrIDs['operator']; ?>[1]">&nbsp;<?php _e( 'Any', 'fetch-tweets' ); ?></label>
				&nbsp;&nbsp;
				<input id="<?php echo $arrIDs['operator']; ?>[2]" type="radio" name="<?php echo $arrNames['operator']; ?>" value="NOT IN" <?php echo $arrInstance['operator'] == 'NOT IN' ? "Checked" : ""; ?> />
				<label for="<?php echo $arrIDs['operator']; ?>[2]">&nbsp;<?php _e( 'None', 'fetch-tweets' ); ?></label>
				
			</span>
			<?php _e( 'of the selected tags.', 'fetch-tweets' ); ?>
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
		
		<label for="<?php echo $arrIDs['avatar_size']; ?>">
			<?php _e( 'The profile image size in pixel.', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="number" id="<?php echo $arrIDs['avatar_size']; ?>" name="<?php echo $arrNames['avatar_size']; ?>" min="0" value="<?php echo $arrInstance['avatar_size']?>" />
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Set 0 for no avatar.', 'fetch-tweets' ); ?> <?php _e( 'Default', 'fetch-tweets' ); ?>: 48
		</p>
		<?php
		
	}
	protected function getFieldValues( $strField='id' ) {
		
		// Returns an array of filed values by a specified field.
		// $strField can be either name or id.
		$arrFields = array();
		foreach( $this->arrStructure_FormElements as $strFieldKey => $v )  
			$arrFields[ $strFieldKey ] = $strField == 'id' 
				? $this->get_field_id( $strFieldKey )
				: $this->get_field_name( $strFieldKey );
	
		return $arrFields;
	}
	public function update( $arrNewInstance, $arrOldInstance ) {
		
		$arrNewInstance['count'] = $this->fixNumber( $arrNewInstance['count'], 20, 1 );
		$arrNewInstance['avatar_size'] = $this->fixNumber( $arrNewInstance['avatar_size'], 48, 0 );

        return $arrNewInstance;
    }
	protected function fixNumber( $numToFix, $numDefault, $numMin="", $numMax="" ) {
			
		if ( ! is_numeric( trim( $numToFix ) ) ) return $numDefault;
		if ( $numMin !== "" && $numToFix < $numMin ) return $numMin;
		if ( $numMax !== "" && $numToFix > $numMax ) return $numMax;
		return $numToFix;
		
	}		
	
	/*
	 * Private methods
	 * */
	protected function getTagSlugArrays() {
		$arrTagSlugs = array();
		$arrTagObjects = get_terms( 
			FetchTweets_Commons::Tag,			// taxonomy slug
			array(
				'hide_empty' => true,
			) 
		);
		foreach( $arrTagObjects as $oTerm ) 
			$arrTagSlugs[ $oTerm->slug ] = $oTerm->name;		
		return $arrTagSlugs;
	}
	
	/*
	 * Methods for Debug
	 * */
	function getArray( $arr ) {
		
		return '<pre>' . htmlspecialchars( print_r( $arr, true ) ) . '</pre>';
		
	}		
}