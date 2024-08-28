<?php
/**
 * Wordpress Categories Model
 *
 * PHP version 7.1
 *
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Models;

/**
 * Wordpress Categories Model
 * 
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class CategoryModel {

    /**
     * Constructor
     */
    public function __construct(){
    }

    /**
     * Fetches a category by its slug
     * 
     * @param string $slug the slug of the category to fetch
     * 
     * @return array containing the matched category
     * @author Cein
     */
    public static function getBySlug( string $slug ): array {
        global $wpdb;
        
        $query = '
        SELECT 
            terms.`term_id`,
            terms.`name`,
            terms.`slug`,
            "category" AS "type"
        FROM `wp_terms` AS terms
        LEFT JOIN `wp_term_taxonomy` AS termTax ON terms.`term_id` = termTax.`term_id`
        WHERE
            terms.`slug` = %s AND
            termTax.taxonomy <> "nav_menu"
        ';
        $preparedQuery = $wpdb->prepare($query, $slug); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }

}
