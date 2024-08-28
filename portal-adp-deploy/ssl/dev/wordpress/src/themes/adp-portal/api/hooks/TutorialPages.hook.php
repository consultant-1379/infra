<?php
/**
 * Wordpress Tutorials Hook
 *
 * PHP version 7.1
 *
 * @category WP_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Hooks;

require_once __DIR__.'/../views/TutorialPages.view.php';
require_once __DIR__.'/../models/PagePostTutorial.model.php';
require_once __DIR__.'/../controllers/portalBE/Cache.controller.php';

use api\Views\TutorialPagesView;
use api\Models\PagePostTutorialModel;
use api\Controllers\PortalBE\CacheController;

/**
 * Wordpress Tutorials Hook
 *
 * @category WP_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class TutorialPagesHook {
    
    /**
     * Constructor
     */
    public function __construct(){
        add_action('init', [$this,'createTutorialPagesSection']);
        add_action('add_meta_boxes_tutorials', [$this, 'addTutorialMetaBox']);
        add_action('save_post_tutorials', [$this, 'saveTutorial']);
    }

    /**
     * Creates the tutorial pages section
     * 
     * @return void
     * @author Cein
     */
    function createTutorialPagesSection():void {
        $supports = array(
            'title',
            'editor',
            'revisions',
            'thumbnail',
            'excerpt'
        );
        $labels = array(
            'name' => _x('Tutorial pages', 'plural'),
            'singular_name' => _x('Tutorial page', 'singular'),
            'menu_name' => _x('Tutorial Pages', 'admin menu'),
            'name_admin_bar' => _x('Tutorial pages', 'admin bar'),
            'add_new' => _x('Add New', 'add new'),
            'add_new_item' => __('Add New Tutorial Page'),
            'new_item' => __('New tutorial page'),
            'edit_item' => __('Edit tutorial page'),
            'view_item' => __('View tutorial page'),
            'all_items' => __('All tutorial pages'),
            'search_items' => __('Search tutorial pages'),
            'not_found' => __('No tutorial pages found.'),
        );
        $args = array(
            'show_in_rest' => true,
            'supports' => $supports,
            'labels' => $labels,
            'public' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'tutorials'),
            'has_archive' => true,
            'hierarchical' => false,
            'show_in_nav_menus' => true,
            'taxonomies' => [ 'category']
        );

        register_post_type('tutorials', $args);
    }

    /**
     * All tutorial metaboxes will be setup here
     * 
     * @return void
     * @author Cein
     */
    function addTutorialMetaBox(): void {
        $tutorialPagesView = new TutorialPagesView();
        add_meta_box(
            'adp_portal_wp_date_content',
            'Notify Updates',
            [$tutorialPagesView, 'renderTutorialPageDateContentMetaBox'],
            'tutorials',
            'side',
            'high'
        );
    }

    /**
     * Tutorial page save hook
     * 
     * @param string $tutorialId given by the hook
     * 
     * @return void
     * @author Cein
     */
    function saveTutorial($tutorialId): void {
        if (isset($tutorialId)) {
            $dateContent = ( isset($_POST['adp_portal_wp_date_content']) ? $_POST['adp_portal_wp_date_content'] : '' );

            update_post_meta($tutorialId, 'adp_portal_wp_date_content', $dateContent);
        }
    }
}