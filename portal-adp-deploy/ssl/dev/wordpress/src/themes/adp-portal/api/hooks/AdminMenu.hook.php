<?php
/**
 * Wordpress Main Admin Area Menu Hooks
 * 
 * The Wordpress Admin Area main menu hooks
 *
 * PHP version 7.1
 *
 * @category WP_Admin_Area_Menu
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Hooks;

require_once __DIR__.'/../controllers/MenuBuilder.controller.php';

use api\Controllers\MenuBuilderController;
use api\Controllers\PagePostTutorialController;


/**
 * Wordpress Main Admin Area Menu Hooks
 *
 * @category WP_Admin_Area_Menu
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class AdminMenuHook {
    
    /**
     * Constuctor
     */
    public function __construct(){
        add_action('admin_menu', [$this, 'adminSubMenuEntry']); 
    }

    /**
     * Adds sub menu menu items to the specified item
     * 
     * @return void
     * @author Omkar
     */
    public function adminSubMenuEntry():void {
        $tutorialController = new PagePostTutorialController();
        add_submenu_page(
            'edit.php?post_type=tutorials',
            'Tutorials Settings',
            'Settings',
            'manage_options',
            'tutorials_settings',
            [$tutorialController,'manageTutorialsSettingsPage']
        );
    }
}