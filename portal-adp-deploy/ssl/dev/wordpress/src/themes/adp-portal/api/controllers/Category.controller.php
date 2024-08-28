<?php
/**
 * Wordpress Categories Controller
 *
 * PHP version 7.1
 *
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers;

/**
 * Wordpress Categories Controller
 *
 * @category WP_Categories
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class CategoryController {
    
    /**
     * Constructor
     */
    public function __construct() {
    }
    
    /**
     * Index all categories
     * 
     * @return array list of categories with their related wp relationships
     * @author Cein
     */
    public static function index() {
        return get_categories();
    }

}