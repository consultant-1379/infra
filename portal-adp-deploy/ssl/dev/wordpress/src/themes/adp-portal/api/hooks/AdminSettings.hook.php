<?php
/**
 * Wordpress Admin Settings Hooks
 * 
 * The Wordpress Admin Area settings hooks
 *
 * PHP version 7.1
 *
 * @category WP_Admin_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Hooks;

require_once __DIR__.'/../controllers/AdminSettings.controller.php';

use api\Controllers\AdminSettingsController;

/**
 * Wordpress Admin Settings Hooks
 *
 * @category WP_Admin_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class AdminSettingsHook {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', [ $this, 'adminGeneralSettingsInclude']);
    }

    /**
     * Hooks into the admin general settings area.
     * 
     * @return void
     * @author Cein
     */
    public static function adminGeneralSettingsInclude(): void {
        $adminSettingsController = new AdminSettingsController();
        register_setting('general', 'adp_portal_wp_server_name', ['string']);
        add_settings_field(
            'adp_portal_wp_server_name', 
            'ADP Portal WP Server Name', 
            [$adminSettingsController, 'renderAdminSettingWPServerName'], 
            'general'
        );

        register_setting('general', 'adp_portal_be_server_path', ['string']);
        add_settings_field(
            'adp_portal_be_server_path', 
            'ADP Portal Backend Server path', 
            [$adminSettingsController, 'renderAdminSettingBEServerPath'], 
            'general'
        );

        register_setting('general', 'adp_portal_be_server_ssl_enabled', ['boolean']);
        add_settings_field(
            'adp_portal_be_server_ssl_enabled', 
            'ADP Portal Backend Server SSL Enabled', 
            [$adminSettingsController, 'renderAdminSettingBEServerSSLEnabled'], 
            'general'
        );
    }

    
}