<?php
/**
 * Defines constants and static properties.
 *	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013-2014, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 * @since		1.3.4			Renamed to FetchTweets_Bootstrap from FetchTweets_InitialLoader
 * @since		2				Moved to a separate file.
 * 
*/

final class FetchTweets_Commons {
	
	public static $sPluginPath = '';
	public static $sPluginKey = 'fetch_tweets';
	public static $strAdminKey = 'fetch_tweets_admin';
	public static $sOptionKey = 'fetch_tweets_option';
	
	const TextDomain = 'fetch-tweets';
	const PluginName = 'Fetch Tweets';
	const PostTypeSlug = 'fetch_tweets';
	const PostTypeSlugAccounts = 'fetchtweets_accounts';		// post type slugs cannot exceed 20 characters. 
	const TagSlug = 'fetch_tweets_tag';
	const AdminOptionKey = 'fetch_tweets_admin';
	const PageSettingsSlug = 'fetch_tweets_settings';
	const TransientPrefix = 'FTWS';
	const ConsumerKey = '97LqHiMs06VhV2rf5tUQw';
	const ConsumerSecret = 'FIH9cr0eXtd7q9caYVqBjd5mvfUS6hZqREYsUhh9wA';
	
	public static function setUp( $sPluginFilePath ) {
		
		self::$sPluginPath = $sPluginFilePath;
		
	}
	
	public static function getPluginKey() {
		return self::$sPluginKey;
	}
	public static function getAdminKey() {
		return self::$strAdminKey;
	}
	public static function getOptionKey() {
		return self::$sOptionKey;
	}	
	public static function getPluginFilePath() {
		return self::$sPluginPath;
	} 
	public static function getPluginDirPath() {
		return dirname( self::$sPluginPath );
	}
	public static function getPluginURL( $sRelativePath='' ) {
		return plugins_url( $sRelativePath, self::$sPluginPath );
	}
	
}
