<?php
/**
 * Wordpress Settings Model
 *
 * PHP version 7.1
 *
 * @category WP_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Models;

/**
 * Wordpress Settings Model
 * 
 * @category WP_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class SettingsModel {

    /**
     * Constructor
     */
    public function __construct(){
    }

    /**
     * Retrieve setting option
     * 
     * @param string $optionKey db option key for the setting
     * 
     * @return string related option value, if no value is 
     * found a empty string is returned
     * @author Cein
     */
    public static function fetchSettingOptionString($optionKey): string {
        $optionValue = '';
        if (is_string($optionKey) && trim($optionKey) !== '') {
            $dbOptionValue = get_option($optionKey);
            if (isset($dbOptionValue) && trim($dbOptionValue) !== '') {
                $optionValue = $dbOptionValue;
            }
        }

        return $optionValue;
    }
    

    /**
     * Retrieve a boolean setting option
     * 
     * @param string $optionKey db option key for the setting
     * 
     * @return bool related option value, if no value is 
     * found a false is returned
     * @author Cein
     */
    public static function fetchSettingOptionBool($optionKey): bool {
        $optionValue = false;
        if (is_string($optionKey) && trim($optionKey) !== '') {
            $dbOptionValue = get_option($optionKey);
            if (isset($dbOptionValue) && $dbOptionValue === 'true') {
                $optionValue = $dbOptionValue;
            }
        }

        return $optionValue;
    }
}
