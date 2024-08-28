<?php
/**
 * Wordpress Categories View
 *
 * PHP version 7.1
 *
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Views;

require_once __DIR__.'/../models/MenuBuilder.model.php';

use api\Models\MenuBuilderModel;

/**
 * Wordpress Categories Hook
 *
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class CategoryView {

    /**
     * Constructor
     */
    public function __construct(){
    }

    /**
     * Renders extra field options for the category settings page
     * 
     * @param array  $categoryMetaDataArr the category meta data
     * @param string $selectOptionsHtml   the selected options html
     * 
     * @return void
     * @author Cein
     */
    public function renderExtraCategoryFields($categoryMetaDataArr, string $selectOptionsHtml):void {
        ?>
            <tr class="form-field">
                <td valign="top"><label for="timeToComplete">Avg Time To Complete</label></td>
                <td>
                    <input type="text" 
                           name="Cat_meta[timeToComplete]" 
                           id="Cat_meta[timeToComplete]" 
                           style="width:100%;" 
                           value="<?php echo ( isset($categoryMetaDataArr['timeToComplete']) ? $categoryMetaDataArr['timeToComplete'] : '') ?>"><br />
                    <span class="description">
                        The average length of time to complete this category. 
                        Please indicate the unit of measure with the time. e.g 20 min
                    </span>
                </td>
            </tr>
            <tr class="form-field">
                <td colspan="2"><br/><br/></td>
            </tr>

            <tr class="form-field">
                <td valign="top"><label for="article_side_menu_slug">Side Menu Link</label></td>
                <td>
                    <select id="article_side_menu_slug" name="Cat_meta[article_side_menu_slug]" style="width:100%">
                        <?php echo $selectOptionsHtml; ?>
                    </select>
                    <span class="description">Link a side menu to this category</span>
                </td>
            </tr>
            <tr class="form-field">
                <td colspan="2"><br/><br/></td>
            </tr>
        <?php
    }

    /**
     * Builds the select options values for the side menu dropdown select
     * 
     * @param {str} $selectedSlugValue the value of the slug that was selected after wordpress fetch
     * 
     * @return {str} html option fields for a html select
     * @author Cein
     */
    public function buildSideMenuSelectOptionHtml($selectedSlugValue):string {
        $menuBuilderModel = new MenuBuilderModel();
        $menuList = $menuBuilderModel->listSideMenus();

        $selectOptionsHtml = '<option value="" >None</option>';
        if (count($menuList) > 0) {
            foreach ($menuList as $menuItem) {
                $selected = ( $menuItem->slug === $selectedSlugValue ? 'selected': '' );
                $selectOptionsHtml .= '<option value="'.$menuItem->slug.'" '.$selected.'>'.$menuItem->name.'</option>';
            }
        }
        return $selectOptionsHtml;
    }
}
