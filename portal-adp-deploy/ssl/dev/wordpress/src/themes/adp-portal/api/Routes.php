<?php
/**
 * Wordpress rest end points
 * 
 * All admin area Menu builder controls
 *
 * PHP version 7.1
 *
 * @category WP_Routes
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 * @license  www.ericsson.com ADP
 * @link     Routes
 */
require_once 'controllers/MenuBuilder.controller.php';
require_once 'controllers/PagePostTutorial.controller.php';
require_once 'controllers/Category.controller.php';

use api\Controllers\MenuBuilderController;
use api\Controllers\PagePostTutorialController;
use api\Controllers\CategoryController;
use api\Models\MenuBuilderModel;

/**
 * API Routes
 */
add_action(
    'rest_api_init', function ( $server ) {

        /**
         * /fetch_menu/:menuSlug
         * Fetch menu array from WP menu builder by menu slug name
         * 
         * @param string menu slug
         * 
         * @return array array of menu items
         * @author Cein
         * 
         * @deprecated use the /wp/v2/menu endpoint instead
         */
        $server->register_route(
            'wp/v2', '/wp/v2/fetch_menu/(?P<menuSlug>\w+)', [
                'methods'  => 'GET',
                'callback' => function ( WP_REST_Request $request ) {
                    $menuContr = new MenuBuilderController();
                    $menuObj = $menuContr->getMenuDetailsBySlug($request['menuSlug']);
                    // !! backward compatibility, remove this once the endpoint is no longer in use
                    if ($menuObj && $menuObj->menus && $menuObj->menus && is_array($menuObj->menus[0]->items) ) {
                        return $menuObj->menus[0]->items;
                    } else {
                        return [];
                    }; 
                }
            ]
        );

        /**
         * /menu/:menuSlug
         * Fetch menu object from WP menu builder by menu slug name if provided
         * If menuSlug is equal to 'all', gets all menus
         * 
         * @param string menu slug
         * 
         * @return array|object menu object
         * @author Omkar
         */
        $server->register_route(
            'wp/v2', '/wp/v2/menu(?:/(?P<menuSlug>[\w-]+))?', [
                'methods'  => 'GET',
                'callback' => function ( WP_REST_Request $request ) {
                    $menuBuilderController = new MenuBuilderController();
                    // backward compatibility remove this after release and only pass in $request['menuSlug'] to getMenuDetailsBySlug
                    $backwardOn = false;
                    $menu = '';
                    if (isset($request['menuSlug'])) {
                        $menu = $request['menuSlug'];
                    } else if ($request->get_param('slug') != null) {
                        $backwardOn = true;
                        $menu = $request->get_param('slug');
                    }
                    
                    //backward compatibility END

                    $response = $menuBuilderController->getMenuDetailsBySlug($menu);
                    header("last_modified: $response->last_modified");

                    //backward comp
                    if ($backwardOn) {
                        if (count($response->menus)) {
                            return $response->menus[0];
                        } else {
                            return [
                                'last_modified' => '1970-01-01 00:00:00',
                                'items' => []
                            ];
                        }
                    }
                    //backward comp
                    return $response;
                },
            ]
        );

        /**
         * /preview/:id
         * Fetch preview data to an associated wp_post Id
         * 
         * @param int preview wp_post Id
         * 
         * @return array post_title {string}  title of the preview,
         * post_content {string} content of the preview
         * @author Cein
         */
        $server->register_route( 
            'wp/v2', '/wp/v2/preview/(?P<id>\d+)', [
                'methods'  => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    return PagePostTutorialController::getPreviewTitleContent((int)$request['id']);
                }
            ]
        );
        
        /**
         * /tutorialPageById/:tutorialPageId
         * Get tutorial page WP data by the tutorial page Id
         * 
         * @param int tutorialPageId wp_post page Id
         * 
         * @return array containing a object containing wp tutorial page data
         * @author Cein
         */
        $server->register_route(
            'wp/v2', '/wp/v2/tutorialPageById/(?P<tutorialPageId>\w+)', [
                'methods'  => 'GET',
                'callback' => function ( WP_REST_Request $request ) {
                    return PagePostTutorialController::getTutorialById((int)$request['tutorialPageId']);
                },
            ]
        );

        /**
         * /tutorialPageBySlug/:slug
         * Get tutorial page WP data by the tutorial page slug
         * 
         * @param int slug wp_post page slug
         * 
         * @return array containing a object containing wp tutorial page data
         * @author Cein
         */
        $server->register_route(
            'wp/v2', '/wp/v2/tutorialPageBySlug', [
                'methods'  => 'GET',
                'callback' => function () {
                    return PagePostTutorialController::getTutorialBySlug($_GET['slug']);
                },
            ]
        );

        /**
         * /allcategories
         * Get all categories
         * 
         * @return array list of categories with their related wp relationships
         * @author Cein
         */
        $server->register_route(
            'wp/v2', '/wp/v2/allcategories', [
                'methods'  => 'GET',
                'callback' => function ( WP_REST_Request $request ) {
                    return CategoryController::index();
                },
            ]
        );

        /**
         * /fetchArticleValidatePath
         * Fetches a page/post/article and validates if its path exists to wordpress
         * 
         * @param string articleSlug the slug of the article to fetch full data on 
         * @param string articleType the slug type, either 'page' or 'post'
         * @param array  parentSlugArray string array of all url params
         * besides the article slugused to verify if that path could exist
         * 
         * @return [
         *  slugResults : all article data
         *  parentSlugResults : data related to the url path
         * ]
         * @author Cein
         */
        $server->register_route( 
            'wp/v2', '/wp/v2/fetchArticleValidatePath', [
                'methods'  => 'POST',
                'callback' => function () {

                    $requestBodyData = json_decode(file_get_contents('php://input'));
                    $articleSlug =  $requestBodyData->articleSlug;
                    $articleType =  $requestBodyData->articleType;
                    $parentSlugArray =  $requestBodyData->parentSlugArray;
                    
                    return PagePostTutorialController::getPagePostAndValidatePath($articleSlug, $articleType, $parentSlugArray);
                },
            ]
        );

        /**
         * /fetchTutorialPageValidatePath
         * Fetch a tutorial page and and validates if its path exists to wordpress
         * 
         * @param string tutorialSlug the slug of the tutorial page
         * to fetch full data on 
         * @param array  parentPathArray string array of all url params besides
         * the articleslug, used to verify if that path could exist
         * 
         * @return array [
         *  slugResults : all tutorial page data
         *  parentSlugResults : data related to the url path
         * ]
         * @author Cein
         */
        $server->register_route(
            'wp/v2', '/wp/v2/fetchTutorialPageValidatePath', [
                'methods'  => 'POST',
                'callback' => function () {
                    $requestBodyData = json_decode(file_get_contents('php://input'));
                    $tutorialSlug = $requestBodyData->tutorialSlug;
                    $parentSlugArray =  $requestBodyData->parentSlugArray;
                    
                    return PagePostTutorialController::getTutorialAndValidatePath($tutorialSlug, $parentSlugArray);
                },
            ]
        );


        /**
         * /metrics
         * Sets up the metrics endpoint for prometheus
         * This method is intercepted and does not
         * return anything useful itself.
         * 
         * @return string an empty string
         * @author John
         */
        $server->register_route( 
            '/', '/metrics', [
                'methods' => 'GET',
                'callback' => function ($request) { 
                    return ""; 
                },
            ]
        );

        /**
         * /highlightsMenuList
         * Fetches the highlight menu documents for highlight component rendering
         * 
         * @return array in order highlight menu items with the page_excerpt,
         * portal_route, page_slug, page_featured_image_url
         * @author Cein 
         * 
         * @deprecated use the /wp/v2/menu endpoint instead
         */
        $server->register_route(
            'wp/v2', '/wp/v2/highlightsMenuList', array(
                'methods'  => 'GET',
                'callback' => function () {
                    $menuContr = new MenuBuilderController();
                    $menuObj = $menuContr->getMenuDetailsBySlug('highlights');
                    // !! backward compatibility, remove this once the endpoint is no longer in use
                    if ($menuObj && $menuObj->menus && $menuObj->menus && is_array($menuObj->menus[0]->items) ) {
                        return $menuObj->menus[0]->items;
                    } else {
                        return [];
                    }; 
                }
            )
        );
        
    }
);