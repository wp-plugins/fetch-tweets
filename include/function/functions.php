<?php
/*
 * User functions - users may use them in their templates.
 * */
function fetchTweets( $aArgs, $bEcho=true ) {
	
	$_sOutput = '';
	if ( ! class_exists( 'FetchTweets_Fetch' ) ) {
		$_sOutput = __( 'The class has not been loaded yet. Use this function after the <code>plugins_loaded</code> hook.', 'fetch-tweets' );
		if ( $bEcho ) {
			echo $_sOutput;
		} else {
			return $_sOutput;
		}
	}
	
	$_oFetch = new FetchTweets_Fetch();
	if ( isset( $aArgs['id'] ) || isset( $aArgs['ids'] ) || isset( $aArgs['q'] ) || isset( $aArgs['screen_name'] ) ) {
		$_sOutput = $_oFetch->getTweetsOutput( $aArgs );
	} else if ( isset( $aArgs['tag'] ) || isset( $aArgs['tags'] ) ) {
		$_sOutput = $_oFetch->getTweetsOutputByTag( $aArgs );
	}

	if ( $bEcho ) {
		echo $_sOutput;
	} else {
		return $_sOutput;
	}
		
}