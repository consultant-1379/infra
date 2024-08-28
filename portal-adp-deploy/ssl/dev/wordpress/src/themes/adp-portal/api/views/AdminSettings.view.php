<?php
/**
 * Wordpress Admin Settings View
 *
 * PHP version 7.1
 *
 * @category WP_Admin_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Views;

/**
 * Wordpress Admin Settings View
 *
 * @category WP_Admin_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class AdminSettingsView {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Renders a input field in the general setting area
     * 
     * @param string $value the value of the input field
     * @param string $name  the value of the input name attribute
     * 
     * @return void
     * @author Cein
     */
    function renderInputFieldSetting(string $value, string $name): void {
        ?>
            <input 
                type="text" 
                size="40" 
                name="<?php echo $name; ?>" 
                value="<?php echo $value; ?>" />
        <?php
    }

    /**
     * Renders a checkbox in the general setting area
     * 
     * @param bool   $checked if the field is checked
     * @param string $name    the value of the input name attribute
     * 
     * @return void
     * @author Cein
     */
    function renderCheckBoxSetting(bool $checked, string $name): void {
        ?>
            <input 
                type="checkbox" 
                name="<?php echo $name; ?>" 
                value="true" 
                <?php echo ( $checked ? 'checked="checked"' : '' ) ?>
                />
        <?php
    }
}