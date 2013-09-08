<?php
abstract class FetchTweets_MetaBox_Template_ extends FetchTweets_AdminPageFramework_MetaBox {

	public function setUp() {
		
		$oTemplates = new FetchTweets_Templates;
		$this->addSettingFields(			
			array(
				'strFieldID'		=> 'fetch_tweets_template',
				'strTitle'			=> __( 'Select Template', 'fetch-tweets' ),
				'strDescription'	=> __( 'Sets a default template for this rule. If a template is specified in a widget, the shortcode, or the function, this setting will be overriden.', 'fetch-tweets' ),
				'vLabel'			=> $arr = $oTemplates->getTemplateArrayForSelectLabel(),
				'strType'			=> 'select',
				// 'strAfterField' 	=> '<pre>' . print_r( $arr, true ) . '</pre>', // debug
				'vDefault'			=> $oTemplates->getDefaultTemplateSlug(),
			),							
			array()
		);
		
	}
	
}