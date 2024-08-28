<?php
/**
 * Wordpress Content Interceptor Controller
 * 
 * Any altering of wp content after db fetch can happen here.
 *
 * PHP version 7.1
 *
 * @category WP_Content_Interceptor
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers;

/**
 * Wordpress Content Interceptor Controller
 *
 * @category WP_Content_Interceptor
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class ContentInterceptorController {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Updates the given content from the existing server information to the given Adp Portal Wp Server Name
     * Setting found in Setting General in the admin area
     * 
     * @param string $stringToUpdate string whose content has incorrect wp server information
     * 
     * @return string the updated string with the update Adp Portal Wp Server Name
     * @author Cein <cein-sven.da.costa@ericsson.com>
     */
    public static function adpPortalWpServerNameContentReplace(string $stringToUpdate): string {
        $updatedString = trim($stringToUpdate);
        $adpPortalServerName = get_option('adp_portal_wp_server_name');
        $wordpressAddress = get_option('home');
        $siteAddress = get_option('siteurl');
        $siteUrlPlaceholder = 'http://!!KEEPSITEURL!!';

        $wpAddressNotSet = (!isset($wordpressAddress) || trim($wordpressAddress) === '');
        $siteAddressNotSet = (!isset($siteAddress) || trim($siteAddress) === '');

        $noServerInformation = ($wpAddressNotSet && $siteAddressNotSet);
        $adpPortalServerNameNotSet = (!isset($adpPortalServerName) || trim($adpPortalServerName) === '');

        $updatedString = str_replace($siteUrlPlaceholder, $siteAddress, $updatedString);
        if ($updatedString === '' || $adpPortalServerNameNotSet || $noServerInformation) {
            return $updatedString;
        }

        $adpPortalServerName = trim($adpPortalServerName);

        $updatedString = str_replace($wordpressAddress, $adpPortalServerName, $updatedString);
        $updatedString = str_replace($siteAddress, $adpPortalServerName, $updatedString);

        return $updatedString;
    }
}