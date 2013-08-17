<?php

abstract class FetchTweets_Widget_ extends WP_Widget {

	protected $arrStructure_FormElements = array(
		'title'			=> null,
		'selected_ids'	=> array(),
		'count'			=> 20,	// default
		// template options
		'template'		=> null,
		'avatar_size'	=> 48,
		'width'			=> 100,
		'width_unit'	=> '%', 	
		'height'		=> 400,
		'height_unit'	=> 'px',
	);

	
	public static function registerWidget() {
		return register_widget( 'Put_The_Extended_Class_Name_Here' );	// the class name - get_class( self ) does not work.
	}	
	
	public function widget( $arrWidgetInfo, $arrInstance ) {	// must be public, the protected scope will cause fatal error.
		
		echo $arrWidgetInfo['before_widget']; 
		
		// Avoid undefined index warnings.
		$arrInstance = $arrInstance + $this->arrStructure_FormElements;
		if ( $arrInstance['title'] )
			echo "<h3 class='fetch-tweets-widget widget-title'>{$arrInstance['title']}</h3>";
		
		$this->echoTweets( $arrInstance );
		
		echo $arrWidgetInfo['after_widget'];
		
	}	

	public function form( $arrInstance ) {	
		
		// Avoid undefined index warnings.
		$arrInstance = $arrInstance + $this->arrStructure_FormElements;
		$arrInstance['template'] = isset( $arrInstance['template'] ) 
			? $arrInstance['template']
			: $GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug();
		$arrIDs = $this->getFieldValues( 'id' );
		$arrNames = $this->getFieldValues( 'name' );
		
		$this->echoFormElements( $arrInstance, $arrIDs, $arrNames );
		
	}
	protected function echoFormElements( $arrInstance, $arrIDs, $arrNames ) {
		// Render form elements in the extended class method.
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
	 * Methods for Debug
	 * */
	protected function getArray( $arr ) {
		
		return '<pre>' . htmlspecialchars( print_r( $arr, true ) ) . '</pre>';
		
	}		
}