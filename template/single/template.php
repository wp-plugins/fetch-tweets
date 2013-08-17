<?php
/*
 * Available variables passed from the caller script
 * - $arrTweets : the fetched tweet arrays.
 * - $arrArgs	: the passed arguments such as item count etc.
 * - $arrOptions : the plugin options saved in the database.
 * */
 
// echo "<pre>" . htmlspecialchars( print_r( $arrTweets, true ) ) . "</pre>";		 
// var_dump( $arrTweets );

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
	break;
}

// Retrieve the default template option values.
if ( ! isset( $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single'] ) ) {	// for the fist time of calling the template.
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_avatar_size'] = 48;
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_width'] = 100;
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_width_unit'] = '%';	
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_height'] = 400;
	$arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_height_unit'] = 'px';
	update_option( FetchTweets_Commons::AdminOptionKey, $arrOptions );
}

$arrArgs['avatar_size'] = isset( $arrArgs['avatar_size'] ) ? $arrArgs['avatar_size'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_avatar_size'];
$arrArgs['width']		= isset( $arrArgs['width'] ) ? $arrArgs['width'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_width'];
$arrArgs['width_unit']	= isset( $arrArgs['width_unit'] ) ? $arrArgs['width_unit'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_width_unit'];
$arrArgs['height']		= isset( $arrArgs['height'] ) ? $arrArgs['height']: $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_height'];
$arrArgs['height_unit']	= isset( $arrArgs['height_unit'] ) ? $arrArgs['height_unit'] : $arrOptions['fetch_tweets_templates']['fetch_tweets_template_single']['fetch_tweets_template_single_height_unit'];
$strWidth = $arrArgs['width'] . $arrArgs['width_unit'];
$strHeight = $arrArgs['height'] . $arrArgs['height_unit'];
?>

<div class='fetch-tweets-single-container' style='max-width:<?php echo $strWidth; ?>; max-height:<?php echo $strHeight; ?>;'>
	
	<div class='fetch-tweets-single-heading'>
		<?php if ( $arrArgs['avatar_size'] > 0 ) : ?>
		<div class='fetch-tweets-single-profile-image' style="width:<?php echo $arrArgs['avatar_size'];?>px;">
			<a href='https://twitter.com/<?php echo $strUserScreenName; ?>' target='_blank'>
				<img src='<?php echo $strUserAvatarURL; ?>' />
			</a>		
		</div>
		<?php endif; ?>
		<span class='fetch-tweets-single-user-name'>
			<strong>
				<a href='https://twitter.com/<?php echo $strUserScreenName; ?>' target='_blank'>
					<?php echo $strUserName; ?>
				</a>
			</strong>
		</span>	
		<div class='fetch-tweets-single-follow-button'>
			<a href="https://twitter.com/<?php echo $strUserScreenName;?>" class="twitter-follow-button" data-show-count="false" data-lang="<?php echo $strUserLang; ?>" target="_blank">Follow @<?php echo $strUserScreenName; ?></a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div>		
		<p class='fetch-tweets-single-user-description'>
			<?php echo $strDescription; ?>
		</p>
		
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
					<span class='fetch-tweets-single-tweet-created-at'>
						<a href='https://twitter.com/<?php echo $arrTweet['user']['screen_name']; ?>/status/<?php echo $arrTweet['id_str'] ;?>' target='_blank'>
							<?php echo FetchTweets_humanTiming( $arrTweet['created_at'] ) . ' ' . __( 'ago', 'fetch-tweets' ); ?>
						</a>			
					</span>
				</span>
			</p>
		</div>
    </div>
	<?php endforeach; ?>	
</div>
