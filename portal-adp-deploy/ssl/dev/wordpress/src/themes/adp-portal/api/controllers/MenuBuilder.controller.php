<?php
/**
 * Wordpress Menu Builder Controller
 * 
 * All admin area Menu builder controls
 *
 * PHP version 7.1
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * 
 * @author  Cein <cein-sven.da.costa@ericsson.com>
 * @license www.ericsson.com ADP
 * @link    Routes
 */
namespace api\Controllers;

require_once __DIR__.'/../models/PagePostTutorial.model.php';
require_once __DIR__.'/../views/MenuBuilder.view.php';
require_once __DIR__.'/PagePostTutorial.controller.php';
require_once __DIR__.'/Validation.controller.php';
require_once __DIR__.'/ContentInterceptor.controller.php';
require_once __DIR__.'/portalBE/Cache.controller.php';
require_once __DIR__.'/../models/MenuBuilder.model.php';
require_once __DIR__.'/MenuItem.php';
require_once __DIR__.'/Menu.php';


use api\Models\PagePostTutorialModel;
use api\Models\MenuBuilderModel;
use api\Views\MenuBuilderView;

use api\Controllers\PagePostTutorialController;
use api\Controllers\ValidationController;
use api\Controllers\ContentInterceptorController;
use api\Controllers\PortalBE\CacheController;
use api\Controllers\General\ErrorController;
use api\Controllers\MenuItem;
use api\Controllers\Menu;


/**
 * Wordpress Menu Builder Controller
 *
 * @category WP_Menu_Builder
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 * 
 * @license www.ericsson.com ADP
 * @link    Routes
 */
class MenuBuilderController
{
    private const DEFAULT_MIN_DATE = '1970-01-01 00:00:00';
    private const PAGES_NOT_ON_MENU_BASE_ARR = [
        'term_id' => 9999999999999,
        'name' => 'Pages Not On Any Menu',
        'slug' => 'pages-not-on-any-menu',
        'term_group' => 0,
        'term_taxonomy_id' => 0,
        'taxonomy' => 'nav_menu',
        'description' => '',
        'parent' => 0,
        'count' => 0,
        'filter' => 'raw',
        'last_modified' => self::DEFAULT_MIN_DATE,
        'items' => [],
    ];
    private const PAGES_NOT_ON_MENU_TYPES = ['page', 'tutorials'];
    public $primaryMenus = ['main', 'footer', 'tutorials'];

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Fetches the highlight menu document for highlight component rendering
     * 
     * @param object $highlightsMenuItemObj the highlight menu item object to update
     * 
     * @return object the update highlight menu item object with updates to: 
     * the page_excerpt
     * portal_route, 
     * page_slug,
     * page_featured_image_url
     * @author Cein 
     */
    private function _updateHighlightMenuItem($highlightsMenuItemObj) {
        $docHighLightObj = $highlightsMenuItemObj;
        
        $objId = $docHighLightObj->object_id;
        $pageExcerptImageSlugArr
            = PagePostTutorialController
            ::getPageExcerptFeaturedImageSlugById($objId);

        $featuredImage = $pageExcerptImageSlugArr['page_featured_image_url'];
        if (isset($featuredImage)) {
            $updatedImgPath = ContentInterceptorController
            ::adpPortalWpServerNameContentReplace($featuredImage);
            $docHighLightObj->page_featured_image_url = $updatedImgPath;
        } else {
            $docHighLightObj->page_featured_image_url = $featuredImage;
        }
        
        $docHighLightObj->page_excerpt
            = $pageExcerptImageSlugArr['page_excerpt'];
        $docHighLightObj->page_slug = $pageExcerptImageSlugArr['page_slug'];

        // validate the path given in the menu item description
        if (isset($docHighLightObj->description) && trim($docHighLightObj->description) !== '') {
            $docHighLightObj->portal_route = $docHighLightObj->description; 
        } 

        if (!isset($docHighLightObj->portal_route)) {
            $docHighLightObj->portal_route = "/$docHighLightObj->page_slug";
        }

        return $docHighLightObj;
    }

    /**
     * Joins the url slug paths of parent menus to child menus
     * 
     * @param arr $childMenuItemsArr child menus array of menu items
     * @param arr $parentMenuLookUp  the linked parent menu lookup array
     * 
     * @return arr updated child menu items array
     * @author Cein
     */
    private function _linkedParentChildMenuPath(Array $childMenuItemsArr, Array $parentMenuLookUp) {
        $updatedChildMenuItemsArr = $childMenuItemsArr;
        // child menu updates
        if (count($childMenuItemsArr)) {
            foreach ($childMenuItemsArr as $childItemIndex => $childMenuObj) {
                if ($childMenuObj->portal_url && $parentMenuLookUp['portalUrl']) {
                    $parentPortalUrl = $parentMenuLookUp['portalUrl'];
                    $linkedMenuPath = [
                        'slug' => $childMenuObj->slug,
                        'portal_url' => $childMenuObj->portal_url,
                        'parent_path' => $parentPortalUrl,
                        'parent_child_path' => $parentPortalUrl . $childMenuObj->portal_url
                    ];

                    if ($parentMenuLookUp['menuSlug'] === 'main') {
                        array_unshift(
                            $updatedChildMenuItemsArr[$childItemIndex]->linked_menu_paths,
                            $linkedMenuPath
                        );
                    } else {
                        array_push(
                            $updatedChildMenuItemsArr[$childItemIndex]->linked_menu_paths,
                            $linkedMenuPath
                        );
                    }
                }
            }
        }
        return $updatedChildMenuItemsArr;
    }

    /**
     * Fetches the first page for every given side menu.
     * Checks if the existing menuArr has those side menus already else it fetches the sidemenus from the db and retrieves them from there.
     * 
     * @param array $menuArr             the menu list to update
     * @param array $menuSlugLookup      the menu slug key and the index of the menu slug in the $menuArr
     * @param array $sideMenuFetchLookup the look up array for the side menu items in the main menu array of menu items:
     *                                   ['menu-slug'] array of menu positions to update:
     *                                   ['menu-slug'][0]['menuIndex'] int - the position in the menu level
     *                                   ['menu-slug'][0]['menuItemIndex'] int - the position in the menu item level 
     * 
     * @return array the updated menu array with any linkedMenuFirstPageSlugs
     * @author Cein
     */
    private function _updateMenuWithSideMenuData($menuArr, $menuSlugLookup, $sideMenuFetchLookup) {
        $menuSlugsToFetchLookup = [];
        $updateMenuArr = $menuArr;

        foreach ($sideMenuFetchLookup as $sideMenuSlug => $sideMenuLookupArr) {
            if (isset($menuSlugLookup[$sideMenuSlug])) {
                $foundSideMenuIndex = $menuSlugLookup[$sideMenuSlug];
                if (count($menuArr[$foundSideMenuIndex]->items)) {
                    foreach ($sideMenuLookupArr as $parentMenuLookUp) {
                        $firstPageSlug = $menuArr[$foundSideMenuIndex]->items[0]->slug;

                        // parent menu updates
                        $updateMenuArr[$parentMenuLookUp['menuIndex']]
                            ->items[$parentMenuLookUp['menuItemIndex']]->linkedMenuFirstPageSlug = $firstPageSlug;
                        $updateMenuArr[$parentMenuLookUp['menuIndex']]
                            ->items[$parentMenuLookUp['menuItemIndex']]->linkedMenuSlug = $sideMenuSlug;

                        // child menu updates
                        $updateMenuArr[$foundSideMenuIndex]->items = $this->_linkedParentChildMenuPath(
                            $updateMenuArr[$foundSideMenuIndex]->items, $parentMenuLookUp
                        );
                    }
                }
            } else {
                $menuSlugsToFetchLookup[$sideMenuSlug] = $sideMenuLookupArr;
            }
        }

        if (count($menuSlugsToFetchLookup)) {
            $menuModel = new MenuBuilderModel();
            $dbMenus = $menuModel->getMenus(array_keys($menuSlugsToFetchLookup));

            foreach ($dbMenus as $dbMenuRow) {
                if (isset($menuSlugsToFetchLookup[$dbMenuRow->slug])) {
                    foreach ($menuSlugsToFetchLookup[$dbMenuRow->slug] as $parentMenuLookUpObj) {

                        // parent menu updates
                        $updateMenuArr[$parentMenuLookUpObj['menuIndex']]
                            ->items[$parentMenuLookUpObj['menuItemIndex']]->linkedMenuFirstPageSlug = $dbMenuRow->post_slug;
                        $updateMenuArr[$parentMenuLookUpObj['menuIndex']]
                            ->items[$parentMenuLookUpObj['menuItemIndex']]->linkedMenuSlug = $dbMenuRow->slug;

                    }
                    unset($menuSlugsToFetchLookup['$sideMenuSlug']);
                }
            }
        }

        return $updateMenuArr;
    }

    /**
     * Unserialises the category_option_data field and update the timeToComplete while building the lookup for the
     * sidemenu first page data for fields:  linkedMenuFirstPageSlug, linkedMenuSlug
     * 
     * @param object $dbMenuObj           the menu currently being updated
     * @param array  $sideMenuFetchLookup the side menu lookup array, reverse lookup to the menu item this is attached to
     * @param int    $menuIndex           the position of the menu in the array of menus
     * @param string $menuSlug            the menu slug
     * @param int    $menuItemIndex       the position of the menu item in the current menu items array
     * 
     * @return array containing keys:
     * ['menuObj'] object the update menu object
     * ['sideMenuFetchLookup'] the update side menu lookup
     * @author Cein
     */
    private function _unserializeCategoryOptionData($dbMenuObj, $sideMenuFetchLookup, $menuIndex, $menuSlug, $menuItemIndex): array {
        $updateMenuObj = $dbMenuObj;
        $updateSideMenuFetchLookup = $sideMenuFetchLookup;
        if (is_string($dbMenuObj->category_option_data) && trim($dbMenuObj->category_option_data) != '') {
            $catOptData = unserialize($dbMenuObj->category_option_data);
            
            if (isset($catOptData['timeToComplete'])) {
                $updateMenuObj->timeToComplete = $catOptData['timeToComplete'];
            }

            if (is_string($catOptData['article_side_menu_slug']) && trim($catOptData['article_side_menu_slug']) != '') {
                if (!isset($updateSideMenuFetchLookup[$catOptData['article_side_menu_slug']])) {
                    $updateSideMenuFetchLookup[$catOptData['article_side_menu_slug']] = [];
                }
                
                array_push(
                    $updateSideMenuFetchLookup[$catOptData['article_side_menu_slug']], [
                    'menuIndex' => $menuIndex,
                    'menuItemIndex' => $menuItemIndex,
                    'portalUrl' => $dbMenuObj->portal_url,
                    'menuSlug' => $menuSlug,
                    ]
                );
                
            }
        }

        return ['menuObj' => $updateMenuObj, 'sideMenuFetchLookup' => $updateSideMenuFetchLookup];
    }

    /**
     * Sets the parent information to each child:
     * Slug, parent_slug, portal_url and menuLevel added here
     * 
     * @param object $dbMenuObj       the current db menu response object
     * @param array  $parentArr       list of parent ids relating to the page in question 
     * @param array  $parentlookupArr lookup the page id to the related data:
     *                                [$id]['slug'] the page slug
     *                                [$id]['portal_url'] the updated portal url
     *                                [$id]['parent_slug'] the parents slug
     *                                [$id]['target'] the target type for this page
     * @param int    $menuLevel       the hierarchy position of the last item
     * 
     * @return array ['parentArr']       updated list of parent ids relating to the page in question 
     *               ['parentLookupArr'] update lookup of the page id to the related data, see params
     *               ['slug']            update slug
     *               ['portal_url']      the update portal route for this page 
     *               ['target']          the update target type for this page
     *               ['menu_level']      the hierarchy position of the menu item
     * @author Cein
     */
    private function _trackMenuParents($dbMenuObj, $parentArr, $parentlookupArr, $menuLevel) {
        // menu item slug, parent_slug, portal_url and menuLevel added here
        $menuItemParentInt = (int)$dbMenuObj->menu_item_parent;
        $menuSlugPathData = [];
        $parentSize = count($parentArr);
        $parentId = ($parentSize ? $parentArr[$parentSize - 1] : '0');
        $lastParentId = (($parentSize - 2) >= 0 ? $parentArr[$parentSize - 2] : '0');

        if ($menuItemParentInt === 0) {
            $menuSlugPathData = self::menuItemSlugPathData($dbMenuObj, '');
            $parentlookupArr[$dbMenuObj->ID] = $menuSlugPathData;
            $parentArr = [];
            $menuLevel = 0;
        } else {
            $parentMenuObj = $parentlookupArr[$menuItemParentInt];
            $menuSlugPathData = self::menuItemSlugPathData($dbMenuObj, $parentMenuObj['portal_url'], $parentMenuObj['slug']);
            $parentlookupArr[$dbMenuObj->ID] = $menuSlugPathData;

            if ($parentId !== $menuItemParentInt) {
                if ($lastParentId !== $menuItemParentInt) {
                    $menuLevel += 1;
                    array_push($parentArr, $menuItemParentInt);
                } else {
                    array_pop($parentArr);
                    $menuLevel -= 1;
                }
            }
        }

        return [
            'parentArr' => $parentArr,
            'parentLookupArr' => $parentlookupArr,
            'slug' => $menuSlugPathData['slug'],
            'parent_slug' => $menuSlugPathData['parent_slug'],
            'portal_url' => $menuSlugPathData['portal_url'],
            'target' => $menuSlugPathData['target'],
            'menu_level' => $menuLevel
        ];
    }

    /**
     * Builds the Menu document Item standard object
     * 
     * @param object $dbMenuObj the menu object of the db
     * 
     * @return object the menu item standard object
     * @author Cein
     */
    private function _menuItemStructuralUpdate($dbMenuObj) {
        return new MenuItem(
            $dbMenuObj->post_date,
            $dbMenuObj->post_date_gmt,
            $dbMenuObj->post_status,
            $dbMenuObj->post_modified,
            $dbMenuObj->post_modified_gmt,
            $dbMenuObj->object_id,
            $dbMenuObj->object,
            $dbMenuObj->type_label,
            ($dbMenuObj->post_title ? $dbMenuObj->post_title: $dbMenuObj->title),
            ($dbMenuObj->object == 'custom' ? $dbMenuObj->slug: $dbMenuObj->post_slug),
            $dbMenuObj->portal_url,
            $dbMenuObj->ID,
            $dbMenuObj->description,
            $dbMenuObj->parent_slug,
            $dbMenuObj->post_author,
            $dbMenuObj->post_content,
            $dbMenuObj->post_title,
            $dbMenuObj->post_excerpt,
            $dbMenuObj->comment_status,
            $dbMenuObj->ping_status,
            $dbMenuObj->post_password,
            $dbMenuObj->post_name,
            $dbMenuObj->to_ping,
            $dbMenuObj->pinged,
            $dbMenuObj->post_content_filtered,
            $dbMenuObj->post_parent,
            $dbMenuObj->guid,
            $dbMenuObj->menu_order,
            $dbMenuObj->post_type,
            $dbMenuObj->post_mime_type,
            $dbMenuObj->comment_count,
            $dbMenuObj->filter,
            $dbMenuObj->db_id,
            $dbMenuObj->menu_item_parent,
            $dbMenuObj->type,
            $dbMenuObj->url,
            $dbMenuObj->target,
            $dbMenuObj->attr_title,
            $dbMenuObj->classes,
            $dbMenuObj->xfn,
            $dbMenuObj->timeToComplete,
            $dbMenuObj->linkedMenuFirstPageSlug,
            $dbMenuObj->linkedMenuSlug,
            $dbMenuObj->date_content,
            $dbMenuObj->menu_level,
            $dbMenuObj->page_featured_image_url,
            $dbMenuObj->page_excerpt,
            $dbMenuObj->page_slug,
            $dbMenuObj->portal_route
        );
    }

    /**
     * Rearranges the menu and puts the primary menus to the top of the page
     * 
     * @param array $menuArr        current menu list
     * @param array $menuSlugLookup the menu lookup array
     * 
     * @return array updated menu array with primary menus on first in the list
     * @author Cein
     */
    private function _arrangePrimaryMenusFirst($menuArr, $menuSlugLookup) {
        $updatedMenu = $menuArr;
        if (count($menuArr) > 1) {
            $primMenus = [];
            foreach ($this->primaryMenus as $primMenuSlug) {
                $menuPos = $menuSlugLookup[$primMenuSlug];
                if (is_numeric($menuPos)) {
                    array_push($primMenus, $updatedMenu[$menuPos]);
                    unset($updatedMenu[$menuPos]);
                }
            }
            if (count($primMenus)) {
                $updatedMenu = array_merge($primMenus, $updatedMenu);
            }
        }

        return $updatedMenu;
    }

    /**
     * Builds the standard menu object and sets all the menu items according to specific rules
     * 
     * @param array $dbMenuList list of of menu object of the db 
     * 
     * @return array ['updatedMenus'] array of menu structured menu objects
     * ['uniquePageIDArrByType'] array list of unique document ids that exists on every menu.
     * ['lastModifiedMenu'] string the latest last modified date for all the menus.
     * @author Cein
     */
    private function _menuStructuralUpdate(array $dbMenuList): array {
        $menuArr = [];
        $uniquePageIDArrByType = [];
        $lastModifiedMenu = self::DEFAULT_MIN_DATE;
        $menuSlugLookup = [];

        if (is_array($dbMenuList) && count($dbMenuList)) {
            $menuObj = null;
            $sideMenuFetchLookup = [];
            $parentlookupArr = [];
            $parentArr = [];
            $menuLevel = 0;

            foreach ($dbMenuList as $menuIdex => $dbMenuObj) {
                if ($menuObj == null || $menuObj->term_id != $dbMenuObj->term_id) {
                    if (strtotime($lastModifiedMenu) < strtotime($dbMenuObj->last_modified)) {
                        $lastModifiedMenu = $dbMenuObj->last_modified;
                    }
                    if ($menuObj != null) {
                        array_push($menuArr, $menuObj);
                    }
                    $menuObj = new Menu(
                        (int) $dbMenuObj->term_id,
                        $dbMenuObj->name,
                        $dbMenuObj->slug,
                        (int) $dbMenuObj->term_group,
                        (int) $dbMenuObj->term_taxonomy_id,
                        $dbMenuObj->taxonomy,
                        $dbMenuObj->description,
                        $dbMenuObj->parent,
                        (int) $dbMenuObj->count,
                        $dbMenuObj->filter,
                        $dbMenuObj->last_modified
                    );
                    $menuSlugLookup[$dbMenuObj->slug] = count($menuArr);
                }

                $trackArr = $this->_trackMenuParents($dbMenuObj, $parentArr, $parentlookupArr, $menuLevel);
                $parentArr = $trackArr['parentArr'];
                $parentlookupArr = $trackArr['parentLookupArr'];
                $menuLevel = $trackArr['menu_level'];

                $dbMenuObj->slug = $trackArr['slug'];
                $dbMenuObj->parent_slug = $trackArr['parent_slug'];
                $dbMenuObj->portal_url = $trackArr['portal_url'];
                $dbMenuObj->target = $trackArr['target'];
                $dbMenuObj->menu_level = $menuLevel;

                $categoryOptionDataArr = $this->_unserializeCategoryOptionData(
                    $dbMenuObj, $sideMenuFetchLookup,  count($menuArr), $menuObj->slug, count($menuObj->items)
                );
                $dbMenuObj = $categoryOptionDataArr['menuObj'];
                $sideMenuFetchLookup = $categoryOptionDataArr['sideMenuFetchLookup'];
                
                if ($menuObj->slug === 'highlights') {
                    $dbMenuObj = $this->_updateHighlightMenuItem($dbMenuObj);
                }

                $menuItemObj = $this->_menuItemStructuralUpdate($dbMenuObj, $menuObj->slug);
                
                $uniquePageIDArrByType[$menuItemObj->object]["$menuItemObj->object_id"] = true;
                array_push($menuObj->items, $menuItemObj);

                if ($menuIdex == (count($dbMenuList) - 1)) {
                    array_push($menuArr, $menuObj);
                }
            }

            if (count($sideMenuFetchLookup)) {
                $menuArr = $this->_updateMenuWithSideMenuData($menuArr, $menuSlugLookup, $sideMenuFetchLookup);
            }
            $menuArr = $this->_arrangePrimaryMenusFirst($menuArr, $menuSlugLookup);            
        }
        return [
            'updatedMenus' => $menuArr,
            'uniquePageIDArrByType' => $uniquePageIDArrByType,
            'lastModifiedMenu' => $lastModifiedMenu
        ];
    }

    /**
     * Fetches menus details by the given slug
     * 
     * @param str $menuSlug slugs, empty string fetches all items
     * 
     * @return obj obj.menus         array containing a single menu or all menus
     *             obj.last_modified last modified date of from the list of menus.
     * @author Omkar, Cein
     */
    public function getMenuDetailsBySlug($menuSlug) {
        $cleanMenuSlug = (isset($menuSlug) && trim("$menuSlug") !== '' ? stripslashes(trim("$menuSlug")) : '');
        $updatedMenus = [];
        $cleanWpMenus = [];
        $uniquePageIDArrByType = [];
        $lastModifiedMenu = self::DEFAULT_MIN_DATE;
        $menuModel = new MenuBuilderModel();

        $cleanWpMenus = $menuModel->getMenus();

        if (count($cleanWpMenus)) {
            $menuStrArr = $this->_menuStructuralUpdate($cleanWpMenus);
            $updatedMenus = $menuStrArr['updatedMenus'];
            $uniquePageIDArrByType = $menuStrArr['uniquePageIDArrByType'];
            $lastModifiedMenu = $menuStrArr['lastModifiedMenu'];

            $pagesIdsNotOnMenus = [];
            $types = self::PAGES_NOT_ON_MENU_TYPES;
            foreach ($types as $type) {
                if (is_array($uniquePageIDArrByType[$type]) && count($uniquePageIDArrByType[$type])) {
                    $pagesIdsNotOnMenus = array_merge($pagesIdsNotOnMenus, array_keys($uniquePageIDArrByType[$type]));
                }
            }
            
            $pagesNotOnMenuObj = $this->buildMenuPagesNotOnMenus($pagesIdsNotOnMenus, $types);
            
            if (strtotime($pagesNotOnMenuObj->last_modified) > strtotime($lastModifiedMenu)) {
                $lastModifiedMenu = $pagesNotOnMenuObj->last_modified;
            }
            if ($cleanMenuSlug == self::PAGES_NOT_ON_MENU_BASE_ARR['slug']) {
                $updatedMenus = [$pagesNotOnMenuObj];
            } else if ($cleanMenuSlug  != '') {
                $matched = false;
                foreach ($updatedMenus as $menuObj) {
                    if ($menuObj->slug == $cleanMenuSlug) {
                        $updatedMenus = [$menuObj];
                        $lastModifiedMenu = $menuObj->last_modified;
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $updatedMenus = [];
                    $lastModifiedMenu = '';
                }
            } else if ($cleanMenuSlug  == '') {
                array_push($updatedMenus, $pagesNotOnMenuObj);
            }
        }

        return (object) [
            'menus' => $updatedMenus,
            'last_modified' => $lastModifiedMenu
        ];
    }

    /**
     * Build the menu item pages-not-on-any-menu
     * This menu contains all page & tutorials that are not on any menu
     * 
     * @param array $pagesIdsNotOnMenus array of menu string ids that are not currently part of any menu
     * @param array $postTypes          array of accepted post types
     * 
     * @return object menu object of the build pages-not-on-any-menu item
     * @author Cein
     */
    public function buildMenuPagesNotOnMenus(array $pagesIdsNotOnMenus, array $postTypes) {
        $pagePostTutContr = new PagePostTutorialController();
        $pages = $pagePostTutContr->getWpPosts([], $pagesIdsNotOnMenus, $postTypes, 'post_title');
        $menu = self::PAGES_NOT_ON_MENU_BASE_ARR;

        if (is_array($menu) && count($menu) > 0) {
            $menu['count'] = count($pages) -1;
            $menuStructItems = [];
            
            // update the document structure to match the WP menu document structure
            foreach ($pages as $docObj) {
                $newDocObj = new MenuItem(
                    $docObj->post_date,
                    $docObj->post_date_gmt,
                    $docObj->post_status,
                    $docObj->post_modified,
                    $docObj->post_modified_gmt,
                    "$docObj->ID",
                    $docObj->post_type,
                    null,
                    $docObj->post_title,
                    $docObj->post_name,
                    "/$docObj->post_name"
                );
                array_push($menuStructItems, $newDocObj);

                // fetch the latest modified date of the returned pages
                if (strtotime($menu['last_modified']) < strtotime($docObj->post_modified_gmt)) {
                    $menu['last_modified'] = $docObj->post_modified_gmt;
                }
            }

            $menu['items'] = $menuStructItems;
        }

        return (object) $menu;
    }

    /**
     * Fetches menus by the given slug list
     * 
     * @param array $menuSlugArray list of slugs
     * 
     * @return array of all fetched menu data
     * @author Cein
     */
    public function getMenusBySlug( array $menuSlugArray ): array
    {
        $fetchedMenuArray = [];
        if (!empty($menuSlugArray)) {
            foreach ($menuSlugArray as $menuSlug) {
                $slugsSideMenuArray = wp_get_nav_menu_items($menuSlug);
                if (!empty($slugsSideMenuArray)) {
                    array_push($fetchedMenuArray, $slugsSideMenuArray);
                }
            }
        }
        return $fetchedMenuArray;
    }


    /**
     * Builds the menu object slug, the menu portalurl and parentslug
     * 
     * @param object $menuObj     the wordpress menu object
     * @param string $parentRoute the menu path of the parent
     * @param string $parentSlug  the slug of the parent, defaults to empty string
     * 
     * @return array string ['slug'] the slug of the current menu item
     * string portal_url'] the full menu path to this item
     * string ['parent_slug'] the slug of the parent
     * @author Cein
     */
    public function menuItemSlugPathData($menuObj, $parentRoute, $parentSlug = '') {
        $object = $menuObj->object;
        $url = $menuObj->url;
        $httpPos = strpos($url, 'http');
        $target = '';
    
        if ($object && ($object === 'category' || $object === 'page' || $object === 'post' || $object === 'tutorials')) {
            $splitUrlArray = explode('/', $url);
            $slug = $splitUrlArray[(count($splitUrlArray) - 2)];
            
            return [
                'slug' => $slug,
                'portal_url' => "$parentRoute/$slug",
                'parent_slug' => $parentSlug,
                'target' => $target,
            ];
        } 
        
        if ($object === 'custom') {
            if (strpos($url, 'mailto') === false) {
                $target = ($httpPos === false ? '_self' : '_blank');
            } else {
                $target = 'mail';
            }
        }

        return [
            'slug' => ($httpPos !== false ? '' : str_replace('/', '', $url)),
            'portal_url' => ($httpPos === false ? $url: ''),
            'parent_slug' => $parentSlug,
            'target' => $target
        ];
    }

    /**
     * Gets the slug from a url
     * 
     * @param string $url the url to strip the slug from the end of
     * 
     * @return string the slug from the url
     * @author Cein
     */
    function getSlugFromUrl( $url )
    {
        $urlArray = explode('/', $url);
        $urlLength = count($urlArray) - 1;
        
        if ($url !== '') {
            if ($urlArray[ $urlLength ] !== '') {
                return $urlArray[ $urlLength ];
            } else if ($urlArray[ ( $urlLength -1 ) ] !== '') {
                return $urlArray[ ( $urlLength -1 ) ];
            }
        }
        return '';
    }

    /**
     * This fetches all linked menus associated to any given slug and adds
     * it to the array given
     * 
     * @param array $arrayOfItemsToFetchLinkedMenus the populated results of both or 
     *                                              either the category or post/page
     *                                              fetch data array
     * 
     * @return array builds on the returned from Article validation path
     * @author Cein
     */
    public static function fetchLinkedMenusToSlug(
        array $arrayOfItemsToFetchLinkedMenus
    ): array {
        $itemArrayToBuildOff = $arrayOfItemsToFetchLinkedMenus;

        if (!empty($itemArrayToBuildOff)) {
            foreach ($itemArrayToBuildOff as $arrayPosition => $itemObject) {
                $fetchedMenuArray = [];
                if (isset($itemObject->ID)) {
                    // check if a page or post has a menu linked to it
                    $pageCustomData = get_post_custom($itemObject->ID);
                    $articleData = $pageCustomData['article_side_menu_slug'];
                    $articleSideMenuSlugArray = (isset($articleData) ? $articleData : []);
                    $fetchedMenuArray = self::getMenusBySlug($articleSideMenuSlugArray);
                } else if (isset($itemObject->term_id)) {
                    // check if category has a menu
                    $categoryMetaData = get_option('category_'.$itemObject->term_id);
                    $menuSlug = $categoryMetaData['article_side_menu_slug'];
                    if (isset($menuSlug) && $menuSlug !== '' ) {
                        $fetchedMenuArray = self::getMenusBySlug([ $menuSlug ]);
                    }
                }
                
                $itemArrayToBuildOff[$arrayPosition]->linked_menu
                    = $fetchedMenuArray;
            }
        }

        return $itemArrayToBuildOff;
    }


    /**
     * Manages menu update/creation intercepts from the 
     * wp_setup_nav_menu_item hook
     * 
     * @param object $wpMenuObj the wp menu object from the wp_setup_nav_menu_item Hook
     * 
     * @return void
     * @author Cein
     */
    public static function menuCreateUpdateHandler($wpMenuObj): void
    {
        if (isset($wpMenuObj) && isset($wpMenuObj->ID) && is_numeric($wpMenuObj->ID)) {
            // menu save intercept code goes here
        }
    }

}