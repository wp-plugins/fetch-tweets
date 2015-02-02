<?php
/**
 * Fetch Tweets
 * 
 * Fetches and displays tweets from twitter.com.
 * 
 * http://en.michaeluno.jp/fetch-tweets/
 * Copyright (c) 2013-2015 Michael Uno; Licensed GPLv2
 */

/**
 * Provides an abstract base for adding pages.
 * 
 * @since       2.4.5
 */
abstract class FetchTweets_AdminPage_Tab_Base {

    /**
     * Sets up hooks and properties.
     */
    public function __construct( $oFactory, $sPageSlug, array $aTabDefinition ) {
        
        $this->oFactory     = $oFactory;
        $this->sPageSlug    = $sPageSlug;
        $this->sTabSlug     = isset( $aTabDefinition['tab_slug'] ) ? $aTabDefinition['tab_slug'] : '';
        
        if ( ! $this->sTabSlug ) {
            return;
        }
        
        $this->_addTab( $this->sPageSlug, $aTabDefinition );
                
    }
    
    private function _addTab( $sPageSlug, $aTabDefinition ) {
        
        $this->oFactory->addInPageTabs(
            $sPageSlug,
            $aTabDefinition + array(
                'tab_slug'          => null,
                'title'             => null,
                'parent_tab_slug'   => null,
                'show_in_page_tab'  => null,
            )
        );
            
        if ( $aTabDefinition['tab_slug'] ) {
            add_action( 
                "load_{$sPageSlug}_{$aTabDefinition['tab_slug']}",
                array( $this, 'replyToLoadTab' ) 
            );
        }
        
    }

    /**
     * Called when the in-page tab loads.
     * 
     * @remark      This method should be overridden in each extended class.
     */
    public function replyToLoadTab( $oFactory ) {}
    
}