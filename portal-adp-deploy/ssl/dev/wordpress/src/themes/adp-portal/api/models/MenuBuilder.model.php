<?php
/**
 * Wordpress Menu Builder Model
 *
 * PHP version 7.1
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Models;


/**
 * Wordpress Menu Builder Model
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class MenuBuilderModel {

    /**
     * Constructor
     */
    public function __construct(){
    }

    /**
     * Fetch list of menus that could be used as side menus
     * 
     * @return array a object which has the post_title and post_content
     * @author Cein
     */
    public function getBasicSideMenuList(): array {
        global $wpdb;
        
        $query = '
            SELECT 
                terms.`term_id`, 
                terms.`name`,
                terms.`slug`,
                termTax.`taxonomy`
            FROM `wp_terms` AS terms
            LEFT JOIN `wp_term_taxonomy` AS termTax ON termTax.term_id = terms.term_id
            WHERE 
                terms.`slug` <> "main" AND 
                termTax.taxonomy = "nav_menu"
        ';

        $preparedQuery = $wpdb->prepare($query); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }


    /**
     * Fetches all side menu items from the appearance menu builder
     * 
     * @return array of menu items
     * @author Cein 
     */
    public function listSideMenus(){
        global $wpdb;
        
        $query = '
            SELECT 
                terms.`term_id`, 
                terms.`name`,
                terms.`slug`,
                termTax.`taxonomy`
            FROM `wp_terms` AS terms
            LEFT JOIN `wp_term_taxonomy` AS termTax ON termTax.term_id = terms.term_id
            WHERE 
                terms.`slug` <> "main" AND 
                termTax.taxonomy = "nav_menu"
        ';
        return $wpdb->get_results($query, 'OBJECT');
    }

    /**
     * Fetches list of options for tutorial setting mode
     * 
     * @return array of menu items
     * @author Omkar
     */
    public function getTutorialSettingOptions(): array{
        return json_decode(
            json_encode(
                [
                    [
                        'id'=> '1',
                        'name'=> 'None'
                    ],
                    [
                        'id'=> '2',
                        'name'=> 'Warning Mode'
                    ],
                    [
                        'id'=> '3',
                        'name'=> 'Error Mode'
                    ],
                ]
            )
        ); 
    }

    /**
     * Fetches the nav_menu slug data by a given postId
     * 
     * @param integer $postId the nav_menu id from the menu creation and update
     * 
     * @return array containing the menu information including the slug
     * @author Cein
     */
    public function getMenuTermDataByPostId($postId): array {
        global $wpdb;
        
        $query = '
            SELECT 
                wp_terms.`term_id`,
                wp_terms.`name`,
                wp_terms.`slug`,
                wp_terms.`term_group`
            FROM wp_terms
                LEFT JOIN wp_term_taxonomy ON wp_terms.term_id = wp_term_taxonomy.term_id
                LEFT JOIN wp_term_relationships ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
                LEFT JOIN wp_posts ON wp_term_relationships.object_id = wp_posts.`ID`
            WHERE 
                wp_posts.post_type = "nav_menu_item" AND 
                wp_posts.ID = %d
        ';

        $preparedQuery = $wpdb->prepare($query, $postId); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }

    /**
     * Fetch menus by a list of menu slug ids or menu term ids or both
     * 
     * @param arr $slugArr   list of one or more menu slug to fetch related menus of.
     * @param arr $termIdArr list of one or more menu term id to fetch related menus of.
     * 
     * @return arr list of fetched menus
     * 
     * @author Cein
     */
    public function getMenus($slugArr = [], $termIdArr = []): array {
        global $wpdb;
        $pdoValues = [];
        $slugWhere = '';
        $termIdWhere = '';

        if (is_array($slugArr) && count($slugArr)) {
            $slugWhere = 'AND wp_terms.slug IN (' . str_repeat('%s,', count($slugArr) - 1) . '%s' . ')';
            $pdoValues = array_merge($pdoValues, $slugArr);
        }

        if (is_array($termIdArr) && count($termIdArr)) {
            $termIdWhere = 'AND wp_term_taxonomy.term_id IN (' . str_repeat('%s,', count($termIdArr) - 1) . '%s' . ')';
            $pdoValues = array_merge($pdoValues, $termIdArr);
        }

        $query = "
            SELECT
                wp_term_taxonomy.term_id,
                wp_terms.name,
                wp_terms.slug,
                wp_terms.term_group,
                wp_term_taxonomy.term_taxonomy_id,
                wp_term_taxonomy.taxonomy,
                wp_term_taxonomy.description,
                wp_term_taxonomy.parent,
                wp_term_taxonomy.count,
                'raw' AS 'filter',
                wp_posts.post_modified_gmt AS 'last_modified',
                wp_posts.ID,
                wp_posts.post_author,
                wp_posts.post_date,
                wp_posts.post_date_gmt,
                wp_posts.post_content,
                wp_posts.post_title,
                wp_posts.post_excerpt,
                wp_posts.post_status,
                wp_posts.comment_status,
                wp_posts.ping_status,
                wp_posts.post_password,
                wp_posts.post_name,
                wp_posts.to_ping,
                wp_posts.pinged,
                wp_posts.post_modified,
                wp_posts.post_modified_gmt,
                wp_posts.post_content_filtered,
                wp_posts.post_parent,
                wp_posts.guid,
                wp_posts.menu_order,
                wp_posts.post_type,
                wp_posts.post_mime_type,
                wp_posts.comment_count,
                wp_posts.ID AS 'db_id',
                pm_menu_item_parent.meta_value AS 'menu_item_parent',
                pm_object_id.meta_value AS 'object_id',
                pm_object.meta_value AS 'object',
                pm_type.meta_value AS 'type',
                CASE
                    WHEN pm_menu_item_url.meta_value = '' THEN CONCAT(
                            options_siteurl.option_value, 
                            '/',
                            CASE 
                                WHEN pm_type.meta_value = 'taxonomy' THEN terms_type_tax.slug
                                ELSE page_posts.post_name
                            END,
                            '/'
                        )
                    ELSE pm_menu_item_url.meta_value
                END AS 'url',
                CASE 
                    WHEN pm_type.meta_value = 'taxonomy' THEN terms_type_tax.name
                    ELSE page_posts.post_title
                END AS 'title',
                pm_menu_item_target.meta_value AS 'target',
                wp_posts.post_excerpt AS 'attr_title',
                wp_posts.post_content  AS 'description',
                pm_menu_item_classes.meta_value AS 'classes',
                pm_menu_item_xfn.meta_value AS 'xfn',
                pm_date_content.meta_value AS 'date_content',
                CASE
                    WHEN pm_type.meta_value = 'taxonomy' THEN terms_type_tax.slug
                    ELSE page_posts.post_name
                END AS 'post_slug',
                options_category_data.option_value AS 'category_option_data'
            -- the relationships of menu to the menu items(pages, tuts, categories etc)
            FROM wp_term_relationships
            -- wp_posts menu item information
            LEFT JOIN wp_posts ON wp_term_relationships.object_id = wp_posts.ID
            -- wp_term_taxonomy has the menu type taxonomy=nav_menu
            LEFT JOIN wp_term_taxonomy ON wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
            -- wp_terms has the name and slug of the menu
            LEFT JOIN wp_terms on wp_term_taxonomy.term_id = wp_terms.term_id
            -- link all the menu item data
            LEFT JOIN wp_postmeta AS pm_menu_item_parent ON pm_menu_item_parent.post_id = wp_posts.ID
                AND pm_menu_item_parent.meta_key = '_menu_item_menu_item_parent'
            LEFT JOIN wp_postmeta AS pm_object_id ON pm_object_id.post_id = wp_posts.ID
                AND pm_object_id.meta_key = '_menu_item_object_id'
            LEFT JOIN wp_postmeta AS pm_object ON pm_object.post_id = wp_posts.ID
                AND pm_object.meta_key = '_menu_item_object'
            LEFT JOIN wp_postmeta AS pm_type ON pm_type.post_id = wp_posts.ID
                AND pm_type.meta_key = '_menu_item_type'
            LEFT JOIN wp_postmeta AS pm_menu_item_url ON pm_menu_item_url.post_id = wp_posts.ID
                AND pm_menu_item_url.meta_key = '_menu_item_url'
            LEFT JOIN wp_postmeta AS pm_menu_item_classes ON pm_menu_item_classes.post_id = wp_posts.ID
                AND pm_menu_item_classes.meta_key = '_menu_item_classes'
            LEFT JOIN wp_postmeta AS pm_menu_item_xfn ON pm_menu_item_xfn.post_id = wp_posts.ID
                AND pm_menu_item_xfn.meta_key = '_menu_item_xfn'
            LEFT JOIN wp_postmeta AS pm_menu_item_target ON pm_menu_item_target.post_id = wp_posts.ID
                AND pm_menu_item_target.meta_key = '_menu_item_target'
            -- tutorial date_content fields
            LEFT JOIN wp_postmeta AS pm_date_content ON pm_date_content.meta_key = 'adp_portal_wp_date_content'
                AND pm_date_content.post_id = pm_object_id.meta_value
            -- get menu item page/cat/tut info
            LEFT JOIN wp_posts AS page_posts ON pm_object_id.meta_value = page_posts.ID
            -- link taxonomy data to the post document such as category
            LEFT JOIN wp_terms AS terms_type_tax ON terms_type_tax.term_id = pm_object_id.meta_value
            -- fetch category option data
            LEFT JOIN wp_options AS options_category_data ON options_category_data.option_name = CONCAT('category_', pm_object_id.meta_value)
            -- get the sites url
            LEFT JOIN wp_options AS options_siteurl ON options_siteurl.option_name = 'siteurl'
            WHERE
                wp_term_taxonomy.taxonomy = 'nav_menu'
                AND wp_posts.post_status = 'publish'
                $slugWhere
                $termIdWhere
            ORDER BY wp_terms.slug, wp_posts.menu_order
        ";

        $preparedQuery = $wpdb->prepare($query, $pdoValues);
        return $wpdb->get_results($preparedQuery);
    }
}
