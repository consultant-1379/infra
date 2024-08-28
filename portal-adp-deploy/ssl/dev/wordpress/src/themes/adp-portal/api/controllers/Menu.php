<?php
/**
 * Standardised Menu Object Class
 * 
 * Class to mimic the Wordpress's Navigational Menu Structure
 *
 * PHP version 7.1
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers;

/**
 * Standardised Menu Object Class
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 * 
 * @license www.ericsson.com ADP
 */
class Menu {

    /**
     * Constructor
     * 
     * @param int    $term_id          the menu id
     * @param string $name             the name of the menu
     * @param string $slug             the slug of the menu
     * @param int    $term_group       the term group
     * @param int    $term_taxonomy_id the wp_term_taxonomy table id
     * @param string $taxonomy         the taxonomy value, in the case of the menu: nav_menu
     * @param string $description      the menu's description
     * @param int    $parent           the menu's parent
     * @param int    $count            the count of menu items
     * @param string $filter           the menus filter type
     * @param string $last_modified    the menu's last modified date
     * @param array  $items            the list of menu items
     */
    public function __construct(
        int $term_id,
        string $name,
        string $slug,
        int $term_group,
        int $term_taxonomy_id,
        string $taxonomy,
        string $description,
        int $parent,
        int $count,
        string $filter,
        string $last_modified,
        $items = []
    ) {
        $this->term_id = $term_id;
        $this->name = $name;
        $this->slug = $slug;
        $this->term_group = $term_group;
        $this->term_taxonomy_id = $term_taxonomy_id;
        $this->term_taxonomy_id = $term_taxonomy_id;
        $this->taxonomy = $taxonomy;
        $this->description = $description;
        $this->parent = $parent;
        $this->count = $count;
        $this->filter = $filter;
        $this->last_modified = $last_modified;
        $this->items = $items;
    }
}