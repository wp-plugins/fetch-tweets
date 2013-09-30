<?php
/*
 * Available variables passed from the caller script
 * - $arrTweets : the fetched tweet arrays.
 * - $arrArgs	: the passed arguments such as item count etc.
 * - $arrOptions : the plugin options saved in the database.
 * */
 
/*
 * Prepare variables for options.
 */
// Retrieve the user avatar and the screen name.
$strUserAvatarURL = null;
$strUserScreenName = null;
$strUserName = null;
$strRetweetClassProperty = '';
$strUserLang = null;
$strUserDescription = null;
foreach( $arrTweets as $arrDetail ) {
	if ( ! isset( $arrDetail['user']['profile_image_url'] ) ) continue;
	$strUserAvatarURL = $arrDetail['user']['profile_image_url'];
	$strUserScreenName = $arrDetail['user']['screen_name'];
	$strUserName = $arrDetail['user']['name'];
	$strUserLang = $arrDetail['user']['lang'];
	$strDescription = $arrDetail['user']['description'];
	break;	// the first iteration item is only necessary 
}

// Set the default template option values.
$arrDefaultTemplateValues = array(
	'fetch_tweets_template_single_avatar_size' => 48,
	'fetch_tweets_template_single_avatar_position' => 'left',
	'fetch_tweets_template_single_width' => array( 'size' => 100, 'unit' => '%' ),
	'fetch_tweets_template_single_height' => array( 'size' => 400, 'unit' => 'px' ),
	'fetch_tweets_template_single_background_color' => 'transparent',
	'fetch_tweets_template_single_intent_buttons' => 2,
	'fetch_tweets_template_single_intent_script' => 1,	
	'fetch_tweets_template_single_follow_button_elements' => array(
		'screen_name' => 0,
		'follower_count' => 0,	
	),
	'fetch_tweets_template_single_visibilities' => array(
		'avatar' => true,
		'user_name' => true,
		'follow_button' => true,
		'user_description' => true,
		'time' => true,	
		'intent_buttons' => true,
	),
	'fetch_tweets_template_single_margins' => array(
		'top' => array( 'size' => '', 'unit' => 'px' ),
		'left' => array( 'size' => '', 'unit' => 'px' ),
		'bottom' => array( 'size' => '', 'unit' => 'px' ),
		'right' => array( 'size' => '', 'unit' => 'px' ),
	),
	'fetch_tweets_template_single_paddings' => array(
		'top' => array( 'size' => '', 'unit' => 'px' ),
		'left' => array( 'size' => '', 'unit' => 'px' ),
		'bottom' => array( 'size' => '', 'unit' => 'px' ),
		'right' => array( 'size' => '', 'unit' => 'px' ),
	),	
);
// Retrieve the template option values.
if ( ! isset( $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single'] ) ) {	// for the fist time of calling the template.
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_single'] = $arrDefaultTemplateValues;
	update_option( FetchTweets_Commons::AdminOptionKey, $arrOptions );
}
// Some new setting items are not be stored in the database, so merge the saved options with the defined default values.
$arrTemplateOptions = FetchTweets_Utilities::uniteArrays( $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single'], $arrDefaultTemplateValues );	// unites arrays recursively.

// Set the template option values.
$arrArgs['avatar_size']				= isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : $arrTemplateOptions['fetch_tweets_template_single_avatar_size'];
$arrArgs['avatar_position']			= isset( $arrArgs['avatar_position'] ) ? $arrArgs['avatar_position'] : $arrTemplateOptions['fetch_tweets_template_single_avatar_position'];
$arrArgs['width']					= isset( $arrArgs['width'] ) ? $arrArgs['width'] : $arrTemplateOptions['fetch_tweets_template_single_width']['size'];
$arrArgs['width_unit']				= isset( $arrArgs['width_unit'] ) ? $arrArgs['width_unit'] : $arrTemplateOptions['fetch_tweets_template_single_width']['unit'];
$arrArgs['height']					= isset( $arrArgs['height'] ) ? $arrArgs['height']: $arrTemplateOptions['fetch_tweets_template_single_height']['size'];
$arrArgs['height_unit']				= isset( $arrArgs['height_unit'] ) ? $arrArgs['height_unit'] : $arrTemplateOptions['fetch_tweets_template_single_height']['unit'];
$arrArgs['background_color']		= isset( $arrArgs['background_color'] ) ? $arrArgs['background_color'] : $arrTemplateOptions['fetch_tweets_template_single_background_color'];
$arrArgs['visibilities']			= isset( $arrArgs['visibilities'] ) ? $arrArgs['visibilities'] : $arrTemplateOptions['fetch_tweets_template_single_visibilities'];
$arrArgs['margin_top']				= isset( $arrArgs['margin_top'] ) ? $arrArgs['margin_top'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['top']['size'];
$arrArgs['margin_top_unit']			= isset( $arrArgs['margin_top_unit'] ) ? $arrArgs['margin_top_unit'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['top']['unit'];
$arrArgs['margin_right']			= isset( $arrArgs['margin_right'] ) ? $arrArgs['margin_right'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['right']['size'];
$arrArgs['margin_right_unit']		= isset( $arrArgs['margin_right_unit'] ) ? $arrArgs['margin_right_unit'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['right']['unit'];
$arrArgs['margin_bottom']			= isset( $arrArgs['margin_bottom'] ) ? $arrArgs['margin_bottom'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['bottom']['size'];
$arrArgs['margin_bottom_unit']		= isset( $arrArgs['margin_bottom_unit'] ) ? $arrArgs['margin_bottom_unit'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['bottom']['unit'];
$arrArgs['margin_left']				= isset( $arrArgs['margin_left'] ) ? $arrArgs['margin_left'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['left']['size'];
$arrArgs['margin_left_unit']		= isset( $arrArgs['margin_left_unit'] ) ? $arrArgs['margin_left_unit'] : $arrTemplateOptions['fetch_tweets_template_single_margins']['left']['unit'];
$arrArgs['padding_top']				= isset( $arrArgs['padding_top'] ) ? $arrArgs['padding_top'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['top']['size'];
$arrArgs['padding_top_unit']		= isset( $arrArgs['padding_top_unit'] ) ? $arrArgs['padding_top_unit'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['top']['unit'];
$arrArgs['padding_right']			= isset( $arrArgs['padding_right'] ) ? $arrArgs['padding_right'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['right']['size'];
$arrArgs['padding_right_unit']		= isset( $arrArgs['padding_right_unit'] ) ? $arrArgs['padding_right_unit'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['right']['unit'];
$arrArgs['padding_bottom']			= isset( $arrArgs['padding_bottom'] ) ? $arrArgs['padding_bottom'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['bottom']['size'];
$arrArgs['padding_bottom_unit']		= isset( $arrArgs['padding_bottom_unit'] ) ? $arrArgs['padding_bottom_unit'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['bottom']['unit'];
$arrArgs['padding_left']			= isset( $arrArgs['padding_left'] ) ? $arrArgs['padding_left'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['left']['size'];
$arrArgs['padding_left_unit']		= isset( $arrArgs['padding_left_unit'] ) ? $arrArgs['padding_left_unit'] : $arrTemplateOptions['fetch_tweets_template_single_paddings']['left']['unit'];
$arrArgs['intent_buttons']			= isset( $arrArgs['intent_buttons'] ) ? $arrArgs['intent_buttons'] : ( ! $arrArgs['visibilities']['intent_buttons'] ? 0 : $arrTemplateOptions['fetch_tweets_template_single_intent_buttons'] );	// 0: do not show, 1: icons and text, 2: only icons, 3: only text.
$arrArgs['intent_button_script']	= isset( $arrArgs['intent_button_script'] ) ? $arrArgs['intent_button_script'] : $arrTemplateOptions['fetch_tweets_template_single_intent_script'];
$arrArgs['follow_button_elements']	= isset( $arrArgs['follow_button_elements'] ) ? $arrArgs['follow_button_elements'] : $arrTemplateOptions['fetch_tweets_template_single_follow_button_elements'];
$arrArgs['follow_button_screen_name'] = isset( $arrArgs['follow_button_screen_name'] ) ? $arrArgs['follow_button_screen_name'] : ( $arrArgs['follow_button_elements']['screen_name'] ? $arrArgs['follow_button_elements']['screen_name'] : "false" );
$arrArgs['follow_button_count'] = isset( $arrArgs['follow_button_count'] ) ? $arrArgs['follow_button_count'] : ( $arrArgs['follow_button_elements']['follower_count'] ? $arrArgs['follow_button_elements']['follower_count'] : "false" );
$strWidth = $arrArgs['width'] . $arrArgs['width_unit'];
$strHeight = $arrArgs['height'] . $arrArgs['height_unit'];
$strMarginTop = empty( $arrArgs['margin_top'] ) ? "" : $arrArgs['margin_top'] . $arrArgs['margin_top_unit'];
$strMarginRight = empty( $arrArgs['margin_right'] ) ? "" : $arrArgs['margin_right'] . $arrArgs['margin_right_unit'];
$strMarginBottom = empty( $arrArgs['margin_bottom'] ) ? "" : $arrArgs['margin_bottom'] . $arrArgs['margin_bottom_unit'];
$strMarginLeft = empty( $arrArgs['margin_left'] ) ? "" : $arrArgs['margin_left'] . $arrArgs['margin_left_unit'];
$strPaddingTop = empty( $arrArgs['padding_top'] ) ? "" : $arrArgs['padding_top'] . $arrArgs['padding_top_unit'];
$strPaddingRight = empty( $arrArgs['padding_right'] ) ? "" : $arrArgs['padding_right'] . $arrArgs['padding_right_unit'];
$strPaddingBottom = empty( $arrArgs['padding_bottom'] ) ? "" : $arrArgs['padding_bottom'] . $arrArgs['padding_bottom_unit'];
$strPaddingLeft = empty( $arrArgs['padding_left'] ) ? "" : $arrArgs['padding_left'] . $arrArgs['padding_left_unit'];
$strMargins = ( $strMarginTop ? "margin-top: {$strMarginTop}; " : "" ) . ( $strMarginRight ? "margin-right: {$strMarginRight}; " : "" ) . ( $strMarginBottom ? "margin-bottom: {$strMarginBottom}; " : "" ) . ( $strMarginLeft ? "margin-left: {$strMarginLeft}; " : "" );
$strPaddings = ( $strPaddingTop ? "padding-top: {$strPaddingTop}; " : "" ) . ( $strPaddingRight ? "padding-right: {$strPaddingRight}; " : "" ) . ( $strPaddingBottom ? "padding-bottom: {$strPaddingBottom}; " : "" ) . ( $strPaddingLeft ? "padding-left: {$strPaddingLeft}; " : "" );
$strMarginForImage = $arrArgs['visibilities']['avatar'] ? ( ( $arrArgs['avatar_position'] == 'left' ? "margin-left: " : "margin-right: " ) . ( ( int ) $arrArgs['avatar_size'] + 10 ) . "px" ) : "";
$strGMTOffset = ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

/*
 * For debugs - uncomment the below line to see the contents of the array.
 */  
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";	
// echo "<pre>" . htmlspecialchars( print_r( $arrArgs, true ) ) . "</pre>";	
// return;

/*
 * Start rendering
 */ 

?>

<div class="fetch-tweets-single-container" style="max-width:<?php echo $strWidth; ?>; max-height:<?php echo $strHeight; ?>; background-color: <?php echo $arrArgs['background_color']; ?>; <?php echo $strMargins; ?> <?php echo $strPaddings; ?>">
	
	<div class='fetch-tweets-single-heading'>
		<?php if ( $arrArgs['avatar_size'] > 0  && $arrArgs['visibilities']['avatar'] ) : ?>
		<div class='fetch-tweets-single-profile-image' style="max-width:<?php echo $arrArgs['avatar_size'];?>px; float:<?php echo $arrArgs['avatar_position']; ?>; clear:<?php echo $arrArgs['avatar_position']; ?>;">
			<a href='https://twitter.com/<?php echo $strUserScreenName; ?>' target='_blank'>
				<img src='<?php echo $strUserAvatarURL; ?>' style="max-width:<?php echo $arrArgs['avatar_size'];?>px;" />
			</a>		
		</div>
		<?php endif; ?>
		
		<?php if ( $arrArgs['visibilities']['user_name'] || $arrArgs['visibilities']['follow_button'] || $arrArgs['visibilities']['user_description'] ) : ?>
		<div class='fetch-tweets-single-user-profile' style='<?php echo $strMarginForImage; ?>;'>
		<?php endif; ?>
		
			<?php if ( $arrArgs['visibilities']['user_name'] ) : ?>
			<span class='fetch-tweets-single-user-name'>
				<strong>
					<a href='https://twitter.com/<?php echo $strUserScreenName; ?>' target='_blank'>
						<?php echo $strUserName; ?>
					</a>
				</strong>
			</span>	
			<?php endif; ?>
			
			<?php if ( $arrArgs['visibilities']['follow_button'] ) : ?>
			<div class='fetch-tweets-single-follow-button'>
				<a href="https://twitter.com/<?php echo $strUserScreenName;?>" class="twitter-follow-button" target="_blank" data-lang="<?php echo $strUserLang; ?>" data-show-count="<?php echo $arrArgs['follow_button_count']; ?>" data-show-screen-name="<?php echo $arrArgs['follow_button_screen_name']; ?>"><?php echo __( 'Follow', 'fetch-tweets' ) . '@' . $strUserScreenName; ?></a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>				
			</div>		
			<?php endif; ?>
			
			<?php if ( $arrArgs['visibilities']['user_description'] ) : ?>
			<p class='fetch-tweets-single-user-description'>
				<?php echo $strDescription; ?>
			</p>
			<?php endif; ?>
		
		<?php if ( $arrArgs['visibilities']['user_name'] || $arrArgs['visibilities']['follow_button'] || $arrArgs['visibilities']['user_description'] ) : ?>
		</div>
		<?php endif; ?>
		
	</div>
	
	<?php foreach ( $arrTweets as $arrDetail ) : ?>
	<?php 
	
		// If the necessary key is not set, skip.
		if ( ! isset( $arrDetail['user'] ) ) continue;
		
		// Check if it's a retweet.
		// if ( isset( $arrDetail['retweeted_status']['text'] ) ) continue;
		$arrTweet = isset( $arrDetail['retweeted_status']['text'] ) ? $arrDetail['retweeted_status'] : $arrDetail;
		$strRetweetClassProperty = isset( $arrDetail['retweeted_status']['text'] ) ? 'fetch-tweets-single-retweet' : '';
		
	?>
    <div class='fetch-tweets-single-item <?php echo $strRetweetClassProperty; ?>' >
		<div class='fetch-tweets-single-body'>
			<p class='fetch-tweets-single-text'>
				<?php echo trim( $arrTweet['text'] ); ?>
				<span class='fetch-tweets-single-credit'>
					<?php if ( isset( $arrDetail['retweeted_status']['text'] ) ) : ?>
					<span class='fetch-tweets-single-retweet-credit'>
						<?php echo _e( 'Retweeted by', 'fetch-tweets' ) . ' '; ?>
						<a href='https://twitter.com/<?php echo $arrDetail['user']['screen_name']; ?>' target='_blank'>
							<?php echo $arrDetail['user']['name']; ?>
						</a>
					</span>
					<?php endif; ?>
					
					<?php if ( $arrArgs['visibilities']['time'] ) : ?>
					<span class='fetch-tweets-single-tweet-created-at'>
						<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>/status/<?php echo $arrTweet['id_str'] ;?>' target='_blank'>
							<?php echo human_time_diff( $arrTweet['created_at'] , current_time('timestamp') - $strGMTOffset ) . ' ' . __( 'ago', 'fetch-tweets' ); ?>
						</a>			
					</span>
					<?php endif; ?>
					
				</span>
			</p>

			<?php if ( $arrArgs['intent_buttons'] ) : ?>
				<?php if ( $arrArgs['intent_button_script'] ) : ?>
				<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
				<?php endif; ?>
				<ul class='fetch-tweets-single-intent-buttons'>
					<li class='fetch-tweets-single-intent-reply'>
						<a href='https://twitter.com/intent/tweet?in_reply_to=<?php echo $arrDetail['id_str']; ?>' rel='nofollow' target='_blank' title='<?php _e( 'Reply', 'fetch-tweets' ); ?>'>
							<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 2 ) : ?>
							<span class='fetch-tweets-single-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'image/reply_48x16.png' ); ?>");' ></span>
							<?php endif; ?>
							<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 3 ) : ?>
							<span class='fetch-tweets-single-intent-buttons-text'><?php _e( 'Reply', 'fetch-tweets' ); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<li class='fetch-tweets-single-intent-retweet'>
						<a href='https://twitter.com/intent/retweet?tweet_id=<?php echo $arrDetail['id_str'];?>' rel='nofollow' target='_blank' title='<?php _e( 'Retweet', 'fetch-tweets' ); ?>'>
							<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 2 ) : ?>
							<span class='fetch-tweets-single-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'image/retweet_48x16.png' ); ?>");' ></span>
							<?php endif; ?>
							<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 3 ) : ?>
							<span class='fetch-tweets-single-intent-buttons-text'><?php _e( 'Retweet', 'fetch-tweets' ); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<li class='fetch-tweets-single-intent-favorite'>
						<a href='https://twitter.com/intent/favorite?tweet_id=<?php echo $arrDetail['id_str'];?>' rel='nofollow' target='_blank' title='<?php _e( 'Favorite', 'fetch-tweets' ); ?>'>
							<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 2 ) : ?>
							<span class='fetch-tweets-single-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'image/favorite_48x16.png' ); ?>");' ></span>
							<?php endif; ?>
							<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 3 ) : ?>
							<span class='fetch-tweets-single-intent-buttons-text'><?php _e( 'Favorite', 'fetch-tweets' ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				</ul>
			<?php endif; ?>			
			
		</div>
    </div>
	<?php endforeach; ?>	
</div>
