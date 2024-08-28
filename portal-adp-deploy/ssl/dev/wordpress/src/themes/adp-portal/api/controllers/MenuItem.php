<?php
/**
 * Standardised Menu Document Object Class
 * 
 * Class to mimic the Wordpress Document Structure
 *
 * PHP version 7.1
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers;

/**
 * Standardised Menu Document Object Class
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 * 
 * @license www.ericsson.com ADP
 */
class MenuItem {

    /**
     * Constructor
     * 
     * @param str $post_date                          the menu post creation date - in the case of the pages-not-on-any-menu it is the page's 
     *                                                creation date
     * @param str $post_date_gmt                      the menu post creation date gmt - in the case of the pages-not-on-any-menu it is the page's
     *                                                creation date gmt
     * @param str $post_status                        post state published, draft etc... - in the case of the pages-not-on-any-menu it is the page's
     *                                                status
     * @param str $post_modified                      menu post modified date - in the case of the pages-not-on-any-menu it is the page's modified
     *                                                date
     * @param str $post_modified_gmt                  menu post modified date gmt - in the case of the pages-not-on-any-menu it is the page's modified
     *                                                date gmt
     * @param str $object_id                          the post's id
     * @param str $object                             the post's type such as page, tutorials, category, ...
     * @param str $type_label                         the type label human readable form of $object
     * @param str $title                              menu post's title - in the case of the pages-not-on-any-menu it is the page's title
     * @param str $slug                               the slug of the post
     * @param str $portal_url                         the adp portal route path to the post
     * @param str $ID                                 menu id
     * @param str $description                        menu post description
     * @param str $parent_slug                        the menu parent slug of the post
     * @param str $post_author                        post author the menu post author id
     * @param str $post_content                       serialized menu post content
     * @param str $post_title                         menu post title
     * @param str $post_excerpt                       menu post excerpt
     * @param str $comment_status                     post comment status
     * @param str $ping_status                        ping status
     * @param str $post_password                      menu post password
     * @param str $post_name                          menu post name is set as menu ID
     * @param str $to_ping                            to ping
     * @param str $pinged                             pinged
     * @param str $post_content_filtered              menu post content filtered
     * @param int $post_parent                        post parent
     * @param str $guid                               the theme wp url path with the menu ID as a string param
     * @param str $menu_order                         order within the menu item
     * @param str $post_type                          menu post type
     * @param str $post_mime_type                     menu post mime type
     * @param str $comment_count                      comment counter
     * @param str $filter                             filter type
     * @param int $db_id                              the menu ID in numeric form
     * @param str $menu_item_parent                   parent of menu item of this document
     * @param str $type                               the type
     * @param str $url                                the url to the themes page
     * @param str $target                             the state for external links such as _blank etc
     * @param str $attr_title                         post's attribute title
     * @param arr $classes                            classes linked to the post
     * @param str $xfn                                xfn
     * @param str $timeToComplete                     Tutorial completion time
     * @param str $linkedMenuFirstPageSlug            the first linked side menu page/tutorial slug
     * @param str $linkedMenuSlug                     the slug of the linked side menu
     * @param str $date_content                       the creation date of the post
     * @param str $menu_level                         the index level in the multidimensional menu array
     * @param str $highlights_page_featured_image_url the url of the feature image of the hightslight page
     * @param str $highlights_page_excerpt            the excerpt of the hightslight page
     * @param str $highlights_page_slug               the slug of the hightslighted linked page
     * @param str $highlights_portal_route            the highlights menu portal route for highlight navigation
     * @param arr $linked_menu_paths                  array of every menu linked menu to this menu, such as main menu to side menu relationship paths
     */
    public function __construct(
        $post_date,
        $post_date_gmt,
        $post_status,
        $post_modified,
        $post_modified_gmt,
        $object_id,
        $object,
        $type_label,
        $title,
        $slug,
        $portal_url,
        $ID = null,
        $description = '',
        $parent_slug = '',
        $post_author = '',
        $post_content = '',
        $post_title = '',
        $post_excerpt = '',
        $comment_status = 'closed',
        $ping_status = 'closed',
        $post_password = '',
        $post_name = null,
        $to_ping = '',
        $pinged = '',
        $post_content_filtered = '',
        $post_parent = 0,
        $guid = '',
        $menu_order = 0,
        $post_type = 'nav_menu_item',
        $post_mime_type = '',
        $comment_count = '0',
        $filter = 'raw',
        $db_id = null,
        $menu_item_parent = '0',
        $type = 'post_type',
        $url = '',
        $target = '',
        $attr_title = '',
        $classes = [''],
        $xfn = '',
        $timeToComplete = '',
        $linkedMenuFirstPageSlug = '',
        $linkedMenuSlug = '',
        $date_content = '',
        $menu_level = 0,
        $highlights_page_featured_image_url = '',
        $highlights_page_excerpt = '',
        $highlights_page_slug = '',
        $highlights_portal_route = '',
        $linked_menu_paths = []
    ) {
        $this->ID = (int) ($ID ? $ID : $this->generateMenuId());
        $this->post_author = $post_author;
        $this->post_date = $post_date;
        $this->post_date_gmt = $post_date_gmt;
        $this->post_content = $post_content;
        $this->post_title = $post_title;
        $this->post_excerpt = $post_excerpt;
        $this->post_status = $post_status;
        $this->comment_status = $comment_status;
        $this->ping_status = $ping_status;
        $this->post_password = $post_password;
        $this->post_name = ($post_name ? $post_name: $this->ID);
        $this->to_ping = $to_ping;
        $this->pinged = $pinged;
        $this->post_modified = $post_modified;
        $this->post_modified_gmt = $post_modified_gmt;
        $this->post_content_filtered = $post_content_filtered;
        $this->post_parent = (int) $post_parent;
        $this->guid = $guid;
        $this->menu_order = (int) $menu_order;
        $this->post_type = $post_type;
        $this->post_mime_type = $post_mime_type;
        $this->comment_count = $comment_count;
        $this->filter = $filter;
        $this->db_id = ($db_id ? (int) $db_id : $this->ID);
        $this->menu_item_parent = $menu_item_parent;
        $this->object_id = $object_id;
        $this->object = $object;
        $this->type = $type;
        $this->type_label = ($type_label ? $type_label: ($this->object ? $this->getTypeLabel($this->object): ''));
        $this->url = $url;
        $this->title = $title;
        $this->target = $target;
        $this->attr_title = $attr_title;
        $this->description = $description;
        $this->classes = (is_string($classes) ? unserialize($classes) : $classes);
        $this->xfn = $xfn;
        $this->timeToComplete = ($timeToComplete ? $timeToComplete : '');
        $this->linkedMenuFirstPageSlug = ($linkedMenuFirstPageSlug ? $linkedMenuFirstPageSlug: '');
        $this->linkedMenuSlug = ($linkedMenuSlug ? $linkedMenuSlug: '');
        $this->date_content = ($date_content ? $date_content: '' );
        $this->slug = $slug;
        $this->parent_slug = ($parent_slug ? $parent_slug: '');
        $this->portal_url = ($portal_url ? $portal_url : '');
        $this->menu_level = (is_numeric($menu_level) ? $menu_level : 0);
        $this->page_featured_image_url = ($highlights_page_featured_image_url ? $highlights_page_featured_image_url: '');
        $this->page_excerpt = ($highlights_page_excerpt ? $highlights_page_excerpt: '');
        $this->page_slug = ($highlights_page_slug ? $highlights_page_slug: '');
        $this->portal_route = ($highlights_portal_route ? $highlights_portal_route: '');
        $this->linked_menu_paths = (is_array($linked_menu_paths) ? $linked_menu_paths : []);
    }

    /**
     * Generates a random unique ID
     * 
     * @return string randomly generate unique string
     * @author Cein
     */
    private function generateMenuId(): string {
        return microtime(true) + random_int(100000, 999999);
    }

    /**
     * Fetches the in memory label of a post type
     * 
     * @param string $type the post type
     * 
     * @return string|null the string label of a post type if found
     * @author Cein
     */
    private function getTypeLabel(string $type): ?string {
        $post_type_obj = get_post_type_object($type);
        if ($post_type_obj && $post_type_obj->labels && $post_type_obj->labels->singular_name) {
            return $post_type_obj->labels->singular_name;
        } else if ($type === 'custom') {
            return 'Custom Link';
        }
        
        return null;
    }
}