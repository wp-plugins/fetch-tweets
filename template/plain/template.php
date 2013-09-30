<?php
/*
 * Available variables passed from the caller script
 * - $arrTweets : the fetched tweet arrays.
 * - $arrArgs	: the passed arguments such as item count etc.'
 * - $arrOptions : the plugin options saved in the database.
 * */
 
 
// Set the default template option values.
$arrDefaultTemplateValues = array(
	'fetch_tweets_template_plain_avatar_size' => 48,
	'fetch_tweets_template_plain_avatar_position' => 'left',
	'fetch_tweets_template_plain_width' => array( 'size' => 100, 'unit' => '%' ),
	'fetch_tweets_template_plain_height' => array( 'size' => 400, 'unit' => 'px' ),
	'fetch_tweets_template_plain_background_color' => 'transparent',
	'fetch_tweets_template_plain_intent_buttons' => 2,
	'fetch_tweets_template_plain_intent_script' => 1,
	'fetch_tweets_template_plain_visibilities' => array(
		'avatar' => true,
		'user_name' => true,
		// 'follow_button' => true,
		// 'user_description' => true,
		'time' => true,			
		'intent_buttons' => true,
	),
	'fetch_tweets_template_plain_margins' => array(
		'top' => array( 'size' => '', 'unit' => 'px' ),
		'left' => array( 'size' => '', 'unit' => 'px' ),
		'bottom' => array( 'size' => '', 'unit' => 'px' ),
		'right' => array( 'size' => '', 'unit' => 'px' ),
	),
	'fetch_tweets_template_plain_paddings' => array(
		'top' => array( 'size' => '', 'unit' => 'px' ),
		'left' => array( 'size' => '', 'unit' => 'px' ),
		'bottom' => array( 'size' => '', 'unit' => 'px' ),
		'right' => array( 'size' => '', 'unit' => 'px' ),
	),	
);

// Retrieve the default template option values.
if ( ! isset( $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain'] ) ) {	// for the first time of calling the template.
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain'] = $arrDefaultTemplateValues;
	update_option( FetchTweets_Commons::AdminOptionKey, $arrOptions );
}

// Some new setting items are not stored in the database, so merge the saved options with the defined default values.
$arrTemplateOptions = FetchTweets_Utilities::uniteArrays( $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain'], $arrDefaultTemplateValues );	// unites arrays recursively.

// Finalize the template option values.
$arrArgs['avatar_size']				= isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : $arrTemplateOptions['fetch_tweets_template_plain_avatar_size'];
$arrArgs['avatar_position']			= isset( $arrArgs['avatar_position'] ) ? $arrArgs['avatar_position'] : $arrTemplateOptions['fetch_tweets_template_plain_avatar_position'];
$arrArgs['width']					= isset( $arrArgs['width'] ) ? $arrArgs['width'] : $arrTemplateOptions['fetch_tweets_template_plain_width']['size'];
$arrArgs['width_unit']				= isset( $arrArgs['width_unit'] ) ? $arrArgs['width_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_width']['unit'];
$arrArgs['height']					= isset( $arrArgs['height'] ) ? $arrArgs['height']: $arrTemplateOptions['fetch_tweets_template_plain_height']['size'];
$arrArgs['height_unit']				= isset( $arrArgs['height_unit'] ) ? $arrArgs['height_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_height']['unit'];
$arrArgs['background_color']		= isset( $arrArgs['background_color'] ) ? $arrArgs['background_color'] : $arrTemplateOptions['fetch_tweets_template_plain_background_color'];
$arrArgs['visibilities']			= isset( $arrArgs['visibilities'] ) ? $arrArgs['visibilities'] : $arrTemplateOptions['fetch_tweets_template_plain_visibilities'];
$arrArgs['margin_top']				= isset( $arrArgs['margin_top'] ) ? $arrArgs['margin_top'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['top']['size'];
$arrArgs['margin_top_unit']			= isset( $arrArgs['margin_top_unit'] ) ? $arrArgs['margin_top_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['top']['unit'];
$arrArgs['margin_right']			= isset( $arrArgs['margin_right'] ) ? $arrArgs['margin_right'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['right']['size'];
$arrArgs['margin_right_unit']		= isset( $arrArgs['margin_right_unit'] ) ? $arrArgs['margin_right_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['right']['unit'];
$arrArgs['margin_bottom']			= isset( $arrArgs['margin_bottom'] ) ? $arrArgs['margin_bottom'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['bottom']['size'];
$arrArgs['margin_bottom_unit']		= isset( $arrArgs['margin_bottom_unit'] ) ? $arrArgs['margin_bottom_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['bottom']['unit'];
$arrArgs['margin_left']				= isset( $arrArgs['margin_left'] ) ? $arrArgs['margin_left'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['left']['size'];
$arrArgs['margin_left_unit']		= isset( $arrArgs['margin_left_unit'] ) ? $arrArgs['margin_left_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_margins']['left']['unit'];
$arrArgs['padding_top']				= isset( $arrArgs['padding_top'] ) ? $arrArgs['padding_top'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['top']['size'];
$arrArgs['padding_top_unit']		= isset( $arrArgs['padding_top_unit'] ) ? $arrArgs['padding_top_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['top']['unit'];
$arrArgs['padding_right']			= isset( $arrArgs['padding_right'] ) ? $arrArgs['padding_right'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['right']['size'];
$arrArgs['padding_right_unit']		= isset( $arrArgs['padding_right_unit'] ) ? $arrArgs['padding_right_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['right']['unit'];
$arrArgs['padding_bottom']			= isset( $arrArgs['padding_bottom'] ) ? $arrArgs['padding_bottom'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['bottom']['size'];
$arrArgs['padding_bottom_unit']		= isset( $arrArgs['padding_bottom_unit'] ) ? $arrArgs['padding_bottom_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['bottom']['unit'];
$arrArgs['padding_left']			= isset( $arrArgs['padding_left'] ) ? $arrArgs['padding_left'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['left']['size'];
$arrArgs['padding_left_unit']		= isset( $arrArgs['padding_left_unit'] ) ? $arrArgs['padding_left_unit'] : $arrTemplateOptions['fetch_tweets_template_plain_paddings']['left']['unit'];
$arrArgs['intent_buttons']			= isset( $arrArgs['intent_buttons'] ) ? $arrArgs['intent_buttons'] : ( ! $arrArgs['visibilities']['intent_buttons'] ? 0 : $arrTemplateOptions['fetch_tweets_template_plain_intent_buttons'] );	// 0: do not show, 1: icons and text, 2: only icons, 3: only text.
$arrArgs['intent_button_script']	= isset( $arrArgs['intent_button_script'] ) ? $arrArgs['intent_button_script'] : $arrTemplateOptions['fetch_tweets_template_plain_intent_script'];
$strWidth = $arrArgs['width'] . $arrArgs['width_unit'];
$strHeight = $arrArgs['height'] . $arrArgs['height_unit'];
$strMarginTop = empty( $arrArgs['margin_top'] ) ? 0 : $arrArgs['margin_top'] . $arrArgs['margin_top_unit'];
$strMarginRight = empty( $arrArgs['margin_right'] ) ? 0 : $arrArgs['margin_right'] . $arrArgs['margin_right_unit'];
$strMarginBottom = empty( $arrArgs['margin_bottom'] ) ? 0 : $arrArgs['margin_bottom'] . $arrArgs['margin_bottom_unit'];
$strMarginLeft = empty( $arrArgs['margin_left'] ) ? 0 : $arrArgs['margin_left'] . $arrArgs['margin_left_unit'];
$strPaddingTop = empty( $arrArgs['padding_top'] ) ? 0 : $arrArgs['padding_top'] . $arrArgs['padding_top_unit'];
$strPaddingRight = empty( $arrArgs['padding_right'] ) ? 0 : $arrArgs['padding_right'] . $arrArgs['padding_right_unit'];
$strPaddingBottom = empty( $arrArgs['padding_bottom'] ) ? 0 : $arrArgs['padding_bottom'] . $arrArgs['padding_bottom_unit'];
$strPaddingLeft = empty( $arrArgs['padding_left'] ) ? 0 : $arrArgs['padding_left'] . $arrArgs['padding_left_unit'];
$strMargins = ( $strMarginTop ? "margin-top: {$strMarginTop}; " : "" ) . ( $strMarginRight ? "margin-right: {$strMarginRight}; " : "" ) . ( $strMarginBottom ? "margin-bottom: {$strMarginBottom}; " : "" ) . ( $strMarginLeft ? "margin-left: {$strMarginLeft}; " : "" );
$strPaddings = ( $strPaddingTop ? "padding-top: {$strPaddingTop}; " : "" ) . ( $strPaddingRight ? "padding-right: {$strPaddingRight}; " : "" ) . ( $strPaddingBottom ? "padding-bottom: {$strPaddingBottom}; " : "" ) . ( $strPaddingLeft ? "padding-left: {$strPaddingLeft}; " : "" );
$strMarginForImage = $arrArgs['visibilities']['avatar'] ? ( ( $arrArgs['avatar_position'] == 'left' ? "margin-left: " : "margin-right: " ) . ( ( int ) $arrArgs['avatar_size'] ) . "px" ) : "";
$strGMTOffset = ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

/*
 * For debug - uncomment the following line to see the contents of the arrays.
 */ 
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";	 
// echo "<pre>" . htmlspecialchars( print_r( $arrArgs, true ) ) . "</pre>";	 
// return;

// Start the layout. 
?>

<div class='fetch-tweets' style="max-width: <?php echo $strWidth; ?>; max-height: <?php echo $strHeight; ?>; background-color: <?php echo $arrArgs['background_color']; ?>; <?php echo $strMargins; ?> <?php echo $strPaddings; ?>">

	<?php foreach ( $arrTweets as $arrDetail ) : ?>
	<?php 
		// If the necessary key is set,
		if ( ! isset( $arrDetail['user'] ) ) continue;
		
		// Check if it's a retweet.
		$arrTweet = isset( $arrDetail['retweeted_status']['text'] ) ? $arrDetail['retweeted_status'] : $arrDetail;
		$strRetweetClassProperty = isset( $arrDetail['retweeted_status']['text'] ) ? 'fetch-tweets-retweet' : '';
		
	?>
    <div class='fetch-tweets-item <?php echo $strRetweetClassProperty; ?>' >

		<?php if ( $arrArgs['avatar_size'] > 0  && $arrArgs['visibilities']['avatar'] ) : ?>
		<div class='fetch-tweets-profile-image' style="max-width:<?php echo $arrArgs['avatar_size'];?>px; float:<?php echo $arrArgs['avatar_position']; ?>; clear:<?php echo $arrArgs['avatar_position']; ?>;">
			<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>' target='_blank'>
				<img src='<?php echo $arrTweet['user']['profile_image_url']; ?>' style="max-width:<?php echo $arrArgs['avatar_size'];?>px;" />
			</a>
		</div>
		<?php endif; ?>
		<div class='fetch-tweets-main' style='<?php echo $strMarginForImage;?>;'>
			<div class='fetch-tweets-heading'>
			
				<?php if ( $arrArgs['visibilities']['user_name'] ) : ?>
				<span class='fetch-tweets-user-name'>
					<strong>
						<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>' target='_blank'>
							<?php echo $arrTweet['user']['name']; ?>
						</a>
					</strong>
				</span>
				<?php endif; ?>
				
				<?php if ( $arrArgs['visibilities']['time'] ) : ?>
				<span class='fetch-tweets-tweet-created-at'>
					<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>/status/<?php echo $arrTweet['id_str'] ;?>' target='_blank'>
						<?php echo human_time_diff( $arrTweet['created_at'], current_time('timestamp') - $strGMTOffset ) . ' ' . __( 'ago', 'fetch-tweets' ); ?>
					</a>			
				</span>
				<?php endif; ?>
				
			</div>
			<div class='fetch-tweets-body'>
				<p class='fetch-tweets-text'><?php echo trim( $arrTweet['text'] ); ?>				
					<?php if ( isset( $arrDetail['retweeted_status']['text'] ) ) : ?>
					<span class='fetch-tweets-retweet-credit'>
						<?php echo _e( 'Retweeted by', 'fetch-tweets' ) . ' '; ?>
						<a href='https://twitter.com/<?php echo $arrDetail['user']['screen_name']; ?>' target='_blank'>
							<?php echo $arrDetail['user']['name']; ?>
						</a>
					</span>
					<?php endif; ?>
				</p>

				<?php if ( $arrArgs['intent_buttons'] ) : ?>
					<?php if ( $arrArgs['intent_button_script'] ) : ?>
					<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
					<?php endif; ?>
					<ul class='fetch-tweets-intent-buttons'>
						<li class='fetch-tweets-intent-reply'>
							<a href='https://twitter.com/intent/tweet?in_reply_to=<?php echo $arrDetail['id_str']; ?>' rel='nofollow' target='_blank' title='<?php _e( 'Reply', 'fetch-tweets' ); ?>'>
								<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 2 ) : ?>
								<span class='fetch-tweets-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'image/reply_48x16.png' ); ?>");' ></span>
								<?php endif; ?>
								<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 3 ) : ?>
								<span class='fetch-tweets-intent-buttons-text'><?php _e( 'Reply', 'fetch-tweets' ); ?></span>
								<?php endif; ?>
							</a>
						</li>
						<li class='fetch-tweets-intent-retweet'>
							<a href='https://twitter.com/intent/retweet?tweet_id=<?php echo $arrDetail['id_str'];?>' rel='nofollow' target='_blank' title='<?php _e( 'Retweet', 'fetch-tweets' ); ?>'>
								<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 2 ) : ?>
								<span class='fetch-tweets-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'image/retweet_48x16.png' ); ?>");' ></span>
								<?php endif; ?>
								<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 3 ) : ?>
								<span class='fetch-tweets-intent-buttons-text'><?php _e( 'Retweet', 'fetch-tweets' ); ?></span>
								<?php endif; ?>
							</a>
						</li>
						<li class='fetch-tweets-intent-favorite'>
							<a href='https://twitter.com/intent/favorite?tweet_id=<?php echo $arrDetail['id_str'];?>' rel='nofollow' target='_blank' title='<?php _e( 'Favorite', 'fetch-tweets' ); ?>'>
								<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 2 ) : ?>
								<span class='fetch-tweets-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'image/favorite_48x16.png' ); ?>");' ></span>
								<?php endif; ?>
								<?php if ( $arrArgs['intent_buttons'] == 1 || $arrArgs['intent_buttons'] == 3 ) : ?>
								<span class='fetch-tweets-intent-buttons-text'><?php _e( 'Favorite', 'fetch-tweets' ); ?></span>
								<?php endif; ?>
							</a>
						</li>		

					</ul>
				<?php endif; ?>
			</div>
						
		</div>
    </div>
	<?php endforeach; ?>	
</div>
