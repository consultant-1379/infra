<?php
/**
 * Wordpress Settings
 *
 * PHP version 7.1
 *
 * @category WP_Settings
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */


/**
 * Exit if accessed directly
 * 
 * @author pascal
 */
if (!defined('ABSPATH')) {
    exit;
};

/**
 * Prevent the WP page/post editor from inserting <p> and <br> tags on new line or line-breaks
 * 
 * @author pascal 
 */
add_filter('the_content', 'wpautop');
add_filter('the_excerpt', 'wpautop');


/**
 * Added in the new tutorial_editor role
 * 
 * @author Cein
 */
if (!empty(get_role('tutorial_editor'))) {
    remove_role('tutorial_editor');
}
add_role( 
    'tutorial_editor', 
    'Tutorial Editor', 
    [ 
        'delete_others_posts' => true,
        'delete_posts' => true,
        'delete_published_posts' => true,
        'edit_others_posts' => true,
        'edit_posts' => true,
        'edit_published_posts' => true,
        'manage_categories' => true,
        'manage_links'  => true,
        'publish_posts'  => true,
        'read'  => true,
        'read_private_posts'  => false,
        'unfiltered_html'  => true,
        'upload_files'  => true
    ] 
);

/**
 * Added in the new tutorial_manager role
 * 
 * @author Cein
 */
if (!empty(get_role('tutorial_manager'))) {
    remove_role('tutorial_manager');
}
add_role(
    'tutorial_manager',
    'Tutorial Manager', 
    [ 
        'delete_others_posts' => true,
        'delete_posts' => true,
        'delete_published_posts' => true,
        'edit_others_posts' => true,
        'edit_posts' => true,
        'edit_published_posts' => true,
        'manage_categories' => true,
        'manage_links'  => true,
        'publish_posts'  => true,
        'read'  => true,
        'read_private_posts'  => false,
        'unfiltered_html'  => true,
        'upload_files'  => true,
        'edit_theme_options' => true,
        'manage_options'=> true
    ]
);

/**
 * Added in the new editor_manager role
 * 
 * @author Cein
 */
if (!empty(get_role('editor_manager'))) {
    remove_role('editor_manager');
}
add_role(
    'editor_manager',
    'Editor Manager',
    [ 
        'delete_others_pages'  => true,
        'delete_others_posts'  => true,
        'delete_pages'  => true,
        'delete_posts'  => true,
        'delete_private_pages'  => true,
        'delete_private_posts'  => true,
        'delete_published_pages'  => true,
        'delete_published_posts'  => true,
        'edit_others_pages'  => true,
        'edit_others_posts'  => true,
        'edit_pages'  => true,
        'edit_posts'  => true,
        'edit_private_pages'  => true,
        'edit_private_posts'  => true,
        'edit_published_pages'  => true,
        'edit_published_posts'  => true,
        'manage_categories'  => true,
        'manage_links'  => true,
        'moderate_comments'  => true,
        'publish_pages'  => true,
        'publish_posts'  => true,
        'read'  => true,
        'read_private_pages'  => true,
        'read_private_posts'  => true,
        'unfiltered_html' => true,
        'upload_files'  => true,
        'edit_theme_options' => true
    ]
);