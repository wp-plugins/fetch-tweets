<?php
abstract class FetchTweets_MetaBox_Media_ extends FetchTweets_AdminPageFramework_MetaBox {
	
	public function setUp() { 
	
		$this->addSettingFields(		
			array(
				'strFieldID'		=> 'show_twitter_media',
				'strTitle'			=> __( 'Media Images', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
				'vLabel'			=> __( 'Display media images posted in the tweet that are recognized as media file by Twitter.' ),
				'strDescription'	=> __( 'Currently only photos are supported.' ),
				'vDefault'			=> true,
			),
			array(
				'strFieldID'		=> 'replace_external_media_links',
				'strTitle'			=> __( 'External Media Links', 'fetch-tweets' ),
				'strType'			=> 'checkbox',
				'vLabel'			=> __( 'Replace media links of external sources to an embedded element.', 'fetch-tweets' ),
				'strDescription'	=> __( 'Unlike the above media images, there are media links that are not categorized as media by the Twitter API. Thus, enabling this option will attempt to replace them to the embedded elements.', 'fetch-tweets' ) . ' e.g. youtube, vimeo, dailymotion etc.',
				'vDefault'			=> true,
			),			
			array()
		);	
	
	}


	public function validation_FetchTweets_MetaBox_Media( $arrInput ) {	// validation_ + extended class name
			
		return $arrInput;
		
	}	
	
}