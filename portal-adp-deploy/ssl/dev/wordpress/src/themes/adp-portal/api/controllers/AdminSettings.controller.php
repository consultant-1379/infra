<?php
/**
 * Wordpress Admin Settings Controller
 *
 * PHP version 7.1
 *
 * @category WP_Admin_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers;

require_once __DIR__.'/../views/AdminSettings.view.php';
require_once __DIR__.'/../models/Settings.model.php';

use api\Views\AdminSettingsView;
use api\Models\SettingsModel;

/**
 * Wordpress Admin Settings Controller
 *
 * @category WP_Admin_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class AdminSettingsController {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Calls the view  to Update the WP General settings Area with the
     * WP Server name setting field
     * 
     * @author Cein
     * @return void
     */
    function renderAdminSettingWPServerName():void {
        $dbKey = 'adp_portal_wp_server_name';
        $wpServerName = SettingsModel::fetchSettingOptionString($dbKey);
        AdminSettingsView::renderInputFieldSetting($wpServerName, $dbKey);
    }

    /**
     * Calls the view to Update the WP General settings Area with the
     * ADP portal Backend Server path
     * 
     * @author Cein
     * @return void
     */
    function renderAdminSettingBEServerPath():void {
        $dbKey = 'adp_portal_be_server_path';
        $adpPortalBEServerPath = SettingsModel::fetchSettingOptionString($dbKey);
        AdminSettingsView::renderInputFieldSetting($adpPortalBEServerPath, $dbKey);
    }


    /**
     * Calls the view to set the WP General settings Area with the
     * ADP portal Backend Server SSL enabled checkbox
     * 
     * @author Cein
     * @return void
     */
    function renderAdminSettingBEServerSSLEnabled():void {
        $dbKey = 'adp_portal_be_server_ssl_enabled';
        $adpPortalBEServerPath = SettingsModel::fetchSettingOptionBool($dbKey);
        AdminSettingsView::renderCheckBoxSetting($adpPortalBEServerPath, $dbKey);
    }
}