<?php

abstract class FetchTweets_WidgetByTag_ extends FetchTweets_Widget_ {
	
	public static function registerWidget() {
		return register_widget( 'FetchTweets_WidgetByTag' );	// the class name - get_class( self ) does not work.
	}	
	
	public function __construct() {
				
		$this->arrStructure_FormElements = $this->arrStructure_FormElements + array(
			'selected_tag_slugs' => array(),
			'operator' => 'AND',		
		);
				
		parent::__construct(
	 		'fetch_tweets_widget_by_tag', // base ID
			'Fetch Tweets by Tag', 	// widget name
			array( 'description' => __( 'A widget that fetches tweets by tag.', 'fetch-tweets' ), ) 
		);
		
	}
	
	protected function echoTweets( $arrInstance ) {
		
		fetchTweets( 
			array( 	
				'tags'	=> $arrInstance['selected_tag_slugs'],
				'tag_field_type' => 'slug',
				'count' => $arrInstance['count'],
				'operator'	=> $arrInstance['operator'],
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
			<input type="number" id="<?php echo $arrIDs['avatar_size']; ?>" name="<?php echo $arrNames['avatar_size']; ?>" min="0" value="<?php echo $arrInstance['avatar_size']?>" />
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
	
	
	/*
	 * Private methods
	 * */
	protected function getTagSlugArrays() {
		$arrTagSlugs = array();
		$arrTagObjects = get_terms( 
			FetchTweets_Commons::TagSlug,			// taxonomy slug
			array(
				'hide_empty' => true,
			) 
		);
		foreach( $arrTagObjects as $oTerm ) 
			$arrTagSlugs[ $oTerm->slug ] = $oTerm->name;		
		return $arrTagSlugs;
	}
	
}