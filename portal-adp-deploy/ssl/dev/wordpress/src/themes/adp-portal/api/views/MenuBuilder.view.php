<?php
/**
 * Wordpress Menu Builder View
 * 
 * All admin area Menu builder views
 *
 * PHP version 7.1
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Views;
use api\Models\MenuBuilderModel;

/**
 * Wordpress Menu Builder View
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class MenuBuilderView {

    /**
     * Constructor
     */
    public function __construct(){
    }


    /**
     * Builds the select options values for the tutorials settings dropdown select
     * 
     * @param string $selectedOptionValue selected option from the dropdown
     * 
     * @return string html option fields for the html dropdown select
     * @author Omkar
     */
    public function buildTutorialsSettingsSelectOptionHtml(string $selectedOptionValue):string {
        $selectOptionsHtml = '';
        $menuBuilderModel = new MenuBuilderModel();
        $menuList = $menuBuilderModel->getTutorialSettingOptions();
        if (!empty($menuList)) {
            foreach ($menuList as $menuItem) {
                $selected = ( $menuItem->id === $selectedOptionValue ? 'selected': '' );
                $selectOptionsHtml .= '<option value="'.$menuItem->id.'" '.$selected.'>'.$menuItem->name.'</option>';
            }
        }
        return $selectOptionsHtml;
    }
    /**
     * Build the tutorials settings page
     * 
     * @param string $selectOptionsHtml     selected option from the dropdown for maintenance mode
     * @param string $maitenanceModeMessage the message to be displayed for the selected maintenance mode
     * 
     * @return void
     * @author Omkar
     */
    public function renderTutorialsSettingsPage(string $selectOptionsHtml, string $maitenanceModeMessage):void {
        ?>
             <form method="POST">
                <br/>
                <h1>ADP Training Environment Settings </h1><br/>
                <label for="maintenance_mode">Select Maintenance Mode</label><br/>
                <select id="maintenance_mode" name="maintenanceModeId"> 
                    <?php echo  $selectOptionsHtml; ?>
                </select>
                <br/>
                <span class="description">
                    The maintenance mode for ADP Training Environment. Based on the selection, message as entered below will be shown on ADP Portal. 
                </span><br/><br/>
                <label for="maintenance_mode_message">Message</label><br/>
                <textarea rows="3" id="maintenance_mode_message" name="maintenanceModeMessage" type="text" style="width:50%;">
                    <?php echo htmlspecialchars($maitenanceModeMessage) ?>
                </textarea><br/>
                <span class="description">
                    The Message to be displayed on ADP Portal based on above selected maintenance mode.
                </span><br/><br/>                
                <input type="submit" value="Save" class="button button-primary button-large">
            </form>
        <?php
    }

}
