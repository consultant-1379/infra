<?php
/**
 * ADP Portal BE Cache Controller
 * 
 * This controls cache interactions with the ADP Portal BE
 *
 * PHP version 7.1
 *
 * @category Portal_BE_Cache
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers\PortalBE;

require_once __DIR__.'/../../models/Settings.model.php';
require_once __DIR__.'/../general/Request.controller.php';

use api\Models\SettingsModel;
use api\Controllers\General\RequestController;

/**
 * ADP Portal BE Cache Controller
 * 
 * This controls cache interactions with the ADP Portal BE
 *
 * @category Portal_BE_Cache
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class CacheController {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Clears the ADP Portal Backend Tutorial Menu Cache
     * 
     * @param string $cacheKey the Portal BE cache key to focus the cache clear
     * 
     * @return bool true on cache clear success
     * @author Cein
     */
    public static function clear(string $cacheKey): bool {
        $beServerUrl = SettingsModel::fetchSettingOptionString('adp_portal_be_server_path');
        $beServerSSLEnabled = SettingsModel::fetchSettingOptionBool('adp_portal_be_server_ssl_enabled');

        if ($beServerUrl !== '') {
            $url = "$beServerUrl/clearcache/$cacheKey";
            $request = new RequestController($url, 'GET', [], $beServerSSLEnabled);
            $response = $request->send();
            return (isset($response) && isset($response->code) && $response->code === 200);
        }
        return false;
    }
}