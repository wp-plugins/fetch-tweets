<?php
/**
 * Fetches and displays tweets.
 * 
 * @package           Fetch Tweets
 * @subpackage        
 * @copyright         Michael Uno
 */
abstract class FetchTweets_Fetch_ extends FetchTweets_Fetch_ByTweetID {
    
    /**
     * Returns the output of tweets by the given arguments.
     * 
     * @remark      called from the shortcode callback.
     */
    public function getTweetsOutput( $aArgs ) {   
        
        ob_start();
        $this->drawTweets( $aArgs );
        $_sContent = ob_get_contents();
        ob_end_clean();
        return $_sContent;
        
    }

    /**
     * Prints tweets based on the given arguments.
     * 
     * @param            array            $aArgs 
     *     id - The post id. default: null. e.g. 125  or 124, 235
     *     tag - default: null. e.g. php or php, WordPress. In this method this tag is only used to pass the argument to the template filter.
     *  sort - default: descending. Either ascending, descending, or random can be used.
     *     count - default: 20
     *     operator - default: AND. Either AND or IN or NOT IN is used.
     *  q - default: null e.g. WordPress
     *  screen_name - default: null e.g. miunosoft
     *  include_rts - default: 0. Either 1 or 0.
     *  exclude_replies - default: 0. Either 1 or 0.
     *  cache - default: 1200
     *    lang - default: null.  
     *    result_type - default: mixed
     *    list_id - default: null. e.g. 8044403
     *    twitter_media - ( boolean ) determines whether the Twitter media should be displayed or not. Currently only photos are supported by the Twitter API.
     *    external_media - ( boolean ) determines whether the plugin attempts to replace external media links to embedded elements.
     * show_error_on_no_result     - 2.4.7+ default: true
     * apply_template_on_no_result - 2.4.8+ default: true
     * Template options
     *    template - the template slug.
     *    width - 
     *    width_unit - 
     *    height    - 
     *    height_unit - 
     *    avatar_size - default: 48 
     * 
     */    
    public function drawTweets( $aArgs ) {

        $_aTweets   = $this->getTweetsAsArray( 
            $aArgs // Passed by reference. Gets formatted and updated in the method.
        );
        $_sError    = $this->getErrorMessage( $_aTweets, $aArgs );
        if ( $_sError ) {
            echo $_sError;
            return;
        }
    
        // Output the tweets by applying the template 
        $this->applyTemplate( $_aTweets, $aArgs );
        
    }
 
    /**
     * Generates error message from the tweets array.
     * 
     * @since       2.4.7
     * @since       2.4.8       Changed the scope to public to let some extension plugins access this method.
     * @return      string      the error message. An empty string on no error.
     */
    private function getErrorMessage( array $_aTweets, array $aArgs ) {
                
        if ( empty( $_aTweets ) ) {
            return isset( $aArgs[ 'show_error_on_no_result' ] ) && $aArgs[ 'show_error_on_no_result' ]
                ? __( 'No result could be fetched.', 'fetch-tweets' )
                : '';
        }
        
        if ( isset( $_aTweets['errors'][ 0 ]['message'], $_aTweets['errors'][ 0 ]['code'] ) ) {
            return '<strong>Fetch Tweets</strong>: ' . $_aTweets['errors'][ 0 ]['message'] . ' ' . __( 'Code', 'fetch-tweets' ) . ':' . $_aTweets['errors'][ 0 ]['code'];    
        }
        if ( isset( $_aTweets['error'] ) && $_aTweets['error'] && is_string( $_aTweets['error'] ) ) {
            return '<strong>Fetch Tweets</strong>: ' . $_aTweets['error'];    
        }        
        return '';
        
    }
    
    /**
     * Fetches tweets based on the argument.
     * 
     * @remark      The scope is public as the feed extension uses it.
     * @since       unknown
     * @since       2.4.6       Deprecated the second parameter.
     * @param       array       $aArgs      The argument array that is merged with the default option values. 
     * It is passed by reference to format the arguments. Also to let post meta options being applied.
     * @return      array
     */
    public function getTweetsAsArray( & $aArgs, $mDeprecated=null ) {    
        
        $_aRawArgs  = ( array ) $aArgs ;
        $aArgs      = $this->_getFormattedArguments( $_aRawArgs );
        
        switch( $this->_getRequestType( $aArgs ) ) {
            case 'search':
                $_aTweets = $this->getTweetsBySearch(
                    $aArgs['q'],
                    $aArgs['count'],
                    $aArgs['lang'],
                    $aArgs['result_type'],
                    $aArgs['until'],
                    $aArgs['geocode'],
                    $aArgs['cache']
                );
                break;
            
            case 'screen_name':
                $_aTweets = $this->getTweetsByScreenNames(
                    $aArgs['screen_name'],
                    $aArgs['count'],
                    $aArgs['include_rts'],
                    $aArgs['exclude_replies'],
                    $aArgs['cache'] 
                );
                break;
            
            case 'list':
                $_aTweets = $this->_getTweetsByListID(
                    $aArgs['list_id'],
                    $aArgs['include_rts'],
                    $aArgs['cache']
                );
                break;                
                
            case 'timeline':
                $_aTweets = $this->_getTweetsByHomeTimeline(
                    $aArgs['account_id'],
                    $aArgs['exclude_replies'],
                    $aArgs['include_rts']
                );
                break;                                

            case 'tweet_id':
                $_aTweets = $this->_getResponseByTweetID( 
                    $aArgs['tweet_id'], 
                    $aArgs['cache']
                );
                break;                                
                
            // normal
            default:
                $_aTweets = $this->_getTweetsAsArrayByPostIDs( 
                    $aArgs['id'], 
                    $aArgs, 
                    $_aRawArgs 
                );
                break;
                
        }
                        
        // Format the array and return it.
        $this->_formatTweetArrays( 
            $_aTweets,      // passed by reference
            $aArgs 
        ); 
        return $_aTweets;
        
    }
    
        /**
         * Determines the request type.
         */
        private function _getRequestType( array $aArgs ) {
            
            // custom call by search keyword
            if ( isset( $aArgs['q'] ) ) {   
                return 'search';
            }
            
            // custom call by screen name
            if ( isset( $aArgs['screen_name'] ) ) {
                return 'screen_name';
            }
            
            // only public list can be fetched with this method
            if ( isset( $aArgs['list_id'] ) ) {
                return 'list';
            }
            
            // Time line by registered account.
            if ( isset( $aArgs['account_id'] ) ) {
                return 'timeline';
            }
            
            // Tweet ID
            if ( isset( $aArgs['tweet_id'] ) ) {
                return 'tweet_id';
            }
            
        }
    
        /**
         * 
         * @param            array|integer            $vPostIDs            The target post ID of the Fetch Tweet rule post type.
         * @param            array                    $aArgs                The argument array. It is passed by reference to let assign post meta options.
         */
        protected function _getTweetsAsArrayByPostIDs( $vPostIDs, & $aArgs, $aRawArgs ) {    
        
            $_aTweets = array();
            foreach( ( array ) $vPostIDs as $_iPostID ) {
                
                $aArgs['tweet_type']    = get_post_meta( $_iPostID, 'tweet_type', true );
                $aArgs['count']         = get_post_meta( $_iPostID, 'item_count', true );
                $aArgs['include_rts']   = get_post_meta( $_iPostID, 'include_rts', true );
                $aArgs['cache']         = get_post_meta( $_iPostID, 'cache', true );
                
                $_aRetrievedTweets      = array();
                switch ( $aArgs['tweet_type'] ) {
                    case 'search':
                        $aArgs['q']                     = get_post_meta( $_iPostID, 'search_keyword', true );    
                        $aArgs['result_type']           = get_post_meta( $_iPostID, 'result_type', true );
                        $aArgs['lang']                  = get_post_meta( $_iPostID, 'language', true );
                        $aArgs['until']                 = get_post_meta( $_iPostID, 'until', true );
                        $aArgs['geocentric_coordinate'] = get_post_meta( $_iPostID, 'geocentric_coordinate', true );
                        $aArgs['geocentric_radius']     = get_post_meta( $_iPostID, 'geocentric_radius', true );
                        $_sGeoCode                      = '';
                        if ( 
                            is_array( $aArgs['geocentric_coordinate'] ) && is_array( $aArgs['geocentric_radius'] )
                            && isset( $aArgs['geocentric_coordinate']['latitude'], $aArgs['geocentric_radius']['size'] ) 
                            && $aArgs['geocentric_coordinate']['latitude'] !== '' && $aArgs['geocentric_coordinate']['longitude'] !== ''    // the coordinate can be 0
                            && $aArgs['geocentric_radius']['size'] !== '' 
                        ) {
                            // "latitude,longitude,radius",
                            $_sGeoCode              = trim( $aArgs['geocentric_coordinate']['latitude'] ) . "," . trim( $aArgs['geocentric_coordinate']['longitude'] ) 
                                . "," . trim( $aArgs['geocentric_radius']['size'] ) . $aArgs['geocentric_radius']['unit'] ;
                        }                        
                        $aArgs                      = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
                        $_aRetrievedTweets          = $this->getTweetsBySearch( $aArgs['q'], $aArgs['count'], $aArgs['lang'], $aArgs['result_type'], $aArgs['until'], $_sGeoCode, $aArgs['cache'] );
                        break;
                    case 'list':
                        $aArgs['account_id']        = get_post_meta( $_iPostID, 'account_id', true );
                        $aArgs['mode']              = get_post_meta( $_iPostID, 'mode', true );
                        $aArgs['list_id']           = get_post_meta( $_iPostID, 'list_id', true );
                        $aArgs                      = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
                        $_aRetrievedTweets          = $this->_getTweetsByListID( $aArgs['list_id'], $aArgs['include_rts'], $aArgs['cache'], $aArgs['account_id'], $aArgs['mode'] );
                        break;
                    case 'home_timeline':
                        $aArgs['account_id']        = get_post_meta( $_iPostID, 'account_id', true );
                        $aArgs['exclude_replies']   = get_post_meta( $_iPostID, 'exclude_replies', true );
                        $aArgs                      = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
                        $_aRetrievedTweets          = $this->_getTweetsByHomeTimeline( $aArgs['account_id'], $aArgs['exclude_replies'], $aArgs['include_rts'], $aArgs['cache'] );
                        break;
                    case 'feed':
                        $aArgs['json_url']          = get_post_meta( $_iPostID, 'json_url', true );
                        $aArgs                      = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
                        $_aRetrievedTweets          = $this->_getTweetsByJSONFeed( $aArgs['json_url'], $aArgs['cache'] );
                        break;
                    case 'custom_query':
                        $aArgs['custom_query']      = get_post_meta( $_iPostID, 'custom_query', true );
                        $aArgs['response_key']      = get_post_meta( $_iPostID, 'response_key', true );
                        $aArgs                      = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
                        $_aRetrievedTweets          = $this->_getResponseWithCustomRequest( $aArgs['custom_query'], $aArgs['response_key'], $aArgs['cache'] );
                        break;
                    case 'tweet_id':
                        $aArgs['tweet_id']          = get_post_meta( $_iPostID, 'tweet_id', true );
                        $aArgs                      = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
                        $_aRetrievedTweets          = $this->_getResponseByTweetID( $aArgs['tweet_id'], $aArgs['cache'] );
                        break;
                    case 'screen_name':
                    default:    
                        $aArgs['screen_name']       = get_post_meta( $_iPostID, 'screen_name', true );
                        $aArgs['exclude_replies']   = get_post_meta( $_iPostID, 'exclude_replies', true );
                        $aArgs                      = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
                        $_aRetrievedTweets          = $this->getTweetsByScreenNames( $aArgs['screen_name'], $aArgs['count'], $aArgs['include_rts'], $aArgs['exclude_replies'], $aArgs['cache'] );
                        break;                
                }    

                $_aTweets = array_merge( $_aRetrievedTweets, $_aTweets );
                    
            }

            return $_aTweets;
            
        }
    
}