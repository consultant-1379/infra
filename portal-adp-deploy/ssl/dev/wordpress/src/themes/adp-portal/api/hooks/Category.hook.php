<?php
/**
 * Wordpress Categories Hook
 *
 * PHP version 7.1
 *
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Hooks;

require_once __DIR__.'/../views/Category.view.php';

use api\Views\CategoryView;


/**
 * Wordpress Categories Hook
 *
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class CategoryHook {
    
    /**
     * Constructor
     */
    public function __construct(){
        // Adds extra category page fields
        add_action('edit_category_form_fields', [$this, 'addAdditionalCategoryFields']);
        add_action('category_add_form_fields', [$this, 'addAdditionalCategoryFields']);
        // Allows those extra category page fields to save and update
        add_action('edited_category', [$this, 'saveAdditionalCategoryFields']);
        add_action('create_category', [$this, 'saveAdditionalCategoryFields']);
    }

    /**
     * Adds extra fields the category section
     * 
     * @param object $categoryDataObj the default wp category object to build from
     * 
     * @return void
     * @author Cein <cein-sven.da.costa@ericsson.com>
     */
    function addAdditionalCategoryFields($categoryDataObj):void{
        $categoryView = new CategoryView();

        $categoryMetaDataArr = get_option("category_$categoryDataObj->term_id");
        
        $selectedSideMenuSlug = ( isset($categoryMetaDataArr['article_side_menu_slug']) ? $categoryMetaDataArr['article_side_menu_slug'] : '' );
        $selectOptionsHtml = $categoryView->buildSideMenuSelectOptionHtml($selectedSideMenuSlug);

        $categoryView->renderExtraCategoryFields($categoryMetaDataArr, $selectOptionsHtml);

    }


    /**
     * Saves the additional field value within the category area
     * 
     * @param int $categoryId id of the worked category
     * 
     * @return void
     * @author Cein <cein-sven.da.costa@ericsson.com>
     */
    function saveAdditionalCategoryFields( $categoryId ): void {
        if (isset($_POST['Cat_meta'])) {
            $categoryMetaData = get_option('category_'.$categoryId);
            foreach (array_keys($_POST['Cat_meta']) as $key) {
                if (isset($_POST['Cat_meta'][$key])) {
                    $categoryMetaData[$key] = $_POST['Cat_meta'][$key];
                }
            }
            //save the option array
            update_option('category_'.$categoryId, $categoryMetaData);
        }
    }

}