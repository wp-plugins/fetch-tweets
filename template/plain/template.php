<?php
/*
 * Available variables passed from the caller script
 * - $arrTweets : the fetched tweet arrays.
 * - $arrArgs	: the passed arguments such as item count etc.'
 * - $arrOptions : the plugin options saved in the database.
 * */
 

// Retrieve the default template option values.
if ( ! isset( $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain'] ) ) {	// for the fist time of calling the template.
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_avatar_size'] = 48;
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_width'] = 100;
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_width_unit'] = '%';	
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_height'] = 400;
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_height_unit'] = 'px';	
	update_option( FetchTweets_Commons::AdminOptionKey, $arrOptions );
}
$arrArgs['avatar_size'] = isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_avatar_size'];
$arrArgs['width']		= isset( $arrArgs['width'] ) ? $arrArgs['width'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_width'];
$arrArgs['width_unit']	= isset( $arrArgs['width_unit'] ) ? $arrArgs['width_unit'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_width_unit'];
$arrArgs['height']		= isset( $arrArgs['height'] ) ? $arrArgs['height']: $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_height'];
$arrArgs['height_unit']	= isset( $arrArgs['height_unit'] ) ? $arrArgs['height_unit'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_plain']['fetch_tweets_template_plain_height_unit'];
$strWidth = $arrArgs['width'] . $arrArgs['width_unit'];
$strHeight = $arrArgs['height'] . $arrArgs['height_unit'];

// echo "<pre>" . htmlspecialchars( print_r( $arrArgs, true ) ) . "</pre>";	 
?>

<div class='fetch-tweets' style="max-width: <?php echo $strWidth; ?>; max-height: <?php echo $strHeight; ?>;">

	<?php foreach ( $arrTweets as $arrDetail ) : ?>
	<?php 
		// If the necessary key is set,
		if ( ! isset( $arrDetail['user'] ) ) continue;
		
		// Chewck if it's a retweet.
		$arrTweet = isset( $arrDetail['retweeted_status']['text'] ) ? $arrDetail['retweeted_status'] : $arrDetail;
		$strRetweetClassProperty = isset( $arrDetail['retweeted_status']['text'] ) ? 'fetch-tweets-retweet' : '';
		
	?>
    <div class='fetch-tweets-item <?php echo $strRetweetClassProperty; ?>' >
		<?php if ( $arrArgs['avatar_size'] > 0 ) : ?>
		<div class='fetch-tweets-profile-image' style="width:<?php echo $arrArgs['avatar_size'];?>px;">
			<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>' target='_blank'>
				<img src='<?php echo $arrTweet['user']['profile_image_url']; ?>' />
			</a>
		</div>
		<?php endif; ?>
		<div class='fetch-tweets-heading'>
			<span class='fetch-tweets-user-name'>
				<strong>
					<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>' target='_blank'>
						<?php echo $arrTweet['user']['name']; ?>
					</a>
				</strong>
			</span>
			<span class='fetch-tweets-tweet-created-at'>
				<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>/status/<?php echo $arrTweet['id_str'] ;?>' target='_blank'>
					<?php echo FetchTweets_humanTiming( $arrTweet['created_at'] ) . ' ' . __( 'ago', 'fetch-tweets' ); ?>
				</a>			
			</span>
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
		</div>
    </div>
	<?php endforeach; ?>	
</div>
