<div class='fetch-tweets'>

	<?php foreach ( $arrTweets as $arrDetail ) : ?>
	<?php 
		// If the necessary key is set,
		if ( ! isset( $arrDetail['user'] ) ) continue;
		
		// Chewck if it's a retweet.
		$arrTweet = isset( $arrDetail['retweeted_status']['text'] ) ? $arrDetail['retweeted_status'] : $arrDetail;
		$strRetweetClassProperty = isset( $arrDetail['retweeted_status']['text'] ) ? 'fetch-tweets-retweet' : '';
		
	?>
    <div class='fetch-tweets-item <?php echo $strRetweetClassProperty; ?>' >
		<?php if ( $intProfileImageSize > 0 ) : ?>
		<div class='fetch-tweets-profile-image' style="max-width:<?php echo $intProfileImageSize;?>px;">
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
		<p class='fetch-tweets-text'>
			<?php echo $arrTweet['text']; ?> 			
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
	<?php endforeach; ?>	
</div>
