<?php
/**
 * Wordpress Pages Posts Preview Hook
 *
 * PHP version 7.1
 *
 * @category WP_Pages_Posts_Preview
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Hooks;

/**
 * Wordpress Pages Posts Preview Hook
 *
 * @category WP_Pages_Posts_Preview
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class PagePostPreviewHook {
    
    /**
     * Constructor
     */
    public function __construct(){
        add_filter('preview_post_link', [$this, 'previewRedirect']);
    }

    /**
     * Preview redirect hook for dev, staging and live
     * 
     * @param string $link default string that contains the wp generated url
     * 
     * @return string the correct preview url for the portal to render
     * @author Cein
     */
    function previewRedirect(string $link):string {
        global $post;
        $serverUrl = $_SERVER['HTTP_HOST'];

        // for dev
        $serverUrl = str_replace(':23309', ':58090', $serverUrl);
        // for live & staging
        $serverUrl = str_replace(':23307', '', $serverUrl);
        
        return "https://$serverUrl/preview/$post->ID";
    }
    
}