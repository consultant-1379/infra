<?php
/**
 * Wordpress Pages Hook
 *
 * PHP version 7.1
 *
 * @category WP_Pages
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Hooks;

/**
 * Wordpress Pages Hook
 *
 * @category WP_Pages
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class PagesHook {
    
    /**
     * Constructor
     */
    public function __construct(){
        add_action('init', [ $this, 'pagesSettings']);
        add_post_type_support('page', 'excerpt');
        add_theme_support('post-thumbnails');
    }

    /**
     * Add extra settings to the wordpress pages section
     * 
     * @return void
     * @author Cein <cein-sven.da.costa@ericsson.com>
     */
    function pagesSettings():void {  
        // Add tags section
        register_taxonomy_for_object_type('post_tag', 'page'); 
        // Add category section
        register_taxonomy_for_object_type('category', 'page');  
    }
}