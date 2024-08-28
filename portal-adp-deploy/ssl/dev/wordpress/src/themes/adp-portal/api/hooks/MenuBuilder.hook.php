<?php
/**
 * Wordpress Menu Builder Controller
 * 
 * All admin area Menu builder controls
 *
 * PHP version 7.1
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 * @license  www.ericsson.com ADP
 * @link     Routes
 */
namespace api\Hooks;

require_once __DIR__.'/../controllers/MenuBuilder.controller.php';

use api\Controllers\MenuBuilderController;

/**
 * Wordpress Menu Builder Controller
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 * @license  www.ericsson.com ADP
 * @link     Routes
 */
class MenuBuilderHook
{
    
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', [$this,'registerAdminMenuLocations']);
        add_action('wp_create_nav_menu', [$this,'saveMenuModifiedTimestamp']);
        add_action('wp_update_nav_menu', [$this,'saveMenuModifiedTimestamp']);
        add_action('wp_delete_nav_menu', [$this,'saveMenuModifiedTimestamp']);
        add_filter('wp_setup_nav_menu_item', [$this, 'menuPageHook']);
    }

    /**
     * Register Appearance Menu Locations
     * At least one of these items are needed for WP to render the admin menu builder
     * 
     * @return void
     * @author Cein
     */
    public function registerAdminMenuLocations(): void
    {
        register_nav_menus(
            [
                'primary' => ( 'Primary Navigation' ),
                'side' => ( 'Left Sidebar' ),
                'footer' => ( 'Footer Navigation' ),
                'Socials Menu' => ( 'Social Icons' ),
            ]
        );
    }

    /**
     * Main menu builder page hook
     * 
     * @param object $menu currently selected menu object
     * 
     * @return object currently selected menu object
     * @author Cein
     */
    public function menuPageHook($menu)
    {
        if ($_POST) {
            MenuBuilderController::menuCreateUpdateHandler($menu);
        }

        return $menu;
    }

    /**
     * Register menu modified date for menu create or update
     * 
     * @param string $menu_id ID of menu
     * 
     * @return void
     * @author Omkar
     */
    public function saveMenuModifiedTimestamp($menu_id): void
    {
        $modified_timestamps = get_option('_wds_menu_modified', array());
           $modified_timestamps[ $menu_id ] = current_time('timestamp');

        update_option('_wds_menu_modified', $modified_timestamps, false);
    }
}