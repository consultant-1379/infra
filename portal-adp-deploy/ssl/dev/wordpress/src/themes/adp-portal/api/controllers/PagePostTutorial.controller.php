<?php
/**
 * Wordpress Pages Posts Tutorials Controller
 * 
 * All controls for any wordpress page/post/tutorial
 *
 * PHP version 7.1
 *
 * @category WP_Pages_Posts_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers;

require_once __DIR__.'/../models/PagePostTutorial.model.php';
require_once __DIR__.'/Validation.controller.php';
require_once __DIR__.'/MenuBuilder.controller.php';
require_once __DIR__.'/general/Error.controller.php';
require_once __DIR__.'/ContentInterceptor.controller.php';


use api\Models\PagePostTutorialModel;
use api\Controllers\ValidationController;
use api\Controllers\MenuBuilderController;
use api\Controllers\General\ErrorController;
use api\Controllers\ContentInterceptorController;
use api\Views\MenuBuilderView;

/**
 * Wordpress Pages Posts Tutorials Controller
 *
 * @category WP_Pages_Posts_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class PagePostTutorialController {

    /**
     * PagePostTutorialController contructor
     */
    public function __construct(){
        add_action('tutorials_settings', [$this, 'manageTutorialsSettingsPage']); 
    }

    /**
     * Get tutorial page data by Id
     * 
     * @param int $tutorialPageId the wp_post ID of the tutorial page
     * 
     * @return array containing a object containing wp tutorial page data
     * @author Cein
     */
    public static function getTutorialById( int $tutorialPageId ):array {
        $tutorialDataArray = [];
        if ($tutorialPageId > 0) {
            $tutorialDataArray = PagePostTutorialModel::getTutorialById($tutorialPageId);
            $tutorialDataArray[0]->settings = self::fetchTutorialSettings();
            $tutorialDataArray = self::updateTutorialData($tutorialDataArray);
        }
        return $tutorialDataArray;
    }

    /**
     * Get tutorial page data by slug
     * 
     * @param string $tutorialPageslug the wp_post slug of the tutorial page, updates content and post object data.
     * 
     * @return array containing a object containing wp tutorial page data
     * @author Cein
     */
    public static function getTutorialBySlug( string $tutorialPageslug ):array {
        $tutorialPageslug = trim(stripslashes($tutorialPageslug));
        $tutorialDataArray = [];
        if (isset($tutorialPageslug) && $tutorialPageSlug !== '' ) {
            $tutorialDataArray = PagePostTutorialModel::getTutorialBySlug($tutorialPageslug);
            $tutorialDataArray[0]->settings = self::fetchTutorialSettings();

            $tutorialDataArray = self::updateTutorialData($tutorialDataArray);
        }
        
        return $tutorialDataArray;
    }

    /**
     * Fetches the Page/Post/Tutorial data associated to a preview id
     * 
     * @param int $previewId the Wordpress generated Id of a Page/Post/Tutorial preview
     * 
     * @return array containing the previews data
     * @author Cein
     */
    public static function getPreviewTitleContent( int $previewId ) {
        $previewDataArray = [];
            
        if ($previewId > 0 ) {
            $previewDataArray = PagePostTutorialModel::getPreviewTitleContent($previewId);
            if (!empty($previewDataArray)) {
                $previewDataArray = self::updateTutorialData($previewDataArray);
            }
        }
        return $previewDataArray;
    }

    /**
     * Fetches a page/post and validates if its path exists to wordpress
     * 
     * @param string $articleSlug     the slug of the article to fetch full data on 
     * @param string $articleType     the slug type, either 'page' or 'post'
     * @param array  $parentPathArray string array of all url params besides the article slug, 
     *                                used to verify if that path could exist
     * 
     * @return array [
     *  slugResults : all article data
     *  parentSlugResults : data related to the url path
     * ]
     * @author Cein
     */
    public static function getPagePostAndValidatePath( string $articleSlug, string $articleType, array $parentPathArray ): array {
        $parentSlugResults = [];
        if ($articleSlug === '') {
            ErrorController::response(404, 'Article Not Found: Article slug given is blank.');
        }
        if ($articleType !== 'post' && $articleType !== 'page') {
            ErrorController::response(404, 'Article Type Not Defined Correctly: Article Type must be defined as post or page.');
        }
        
        // check the parent path
        if (!empty($parentPathArray)) {
            $parentSlugResults = ValidationController::validateBuildSlugList($parentPathArray);

            if (count($parentPathArray) !== count($parentSlugResults)) {
                ErrorController::response(404, 'Article Not Found: Invalid Path Variables.');
            }

            // fetch any associated menus to the parent items
            $parentSlugResults = MenuBuilderController::fetchLinkedMenusToSlug($parentSlugResults);
        }
        
        // fetch arrticle data
        $pagePostTutorialModel = new PagePostTutorialModel();
        $articleSlugData = $pagePostTutorialModel->getByPostTypeSlug($articleType, $articleSlug);

        if (empty($articleSlugData)) {
            ErrorController::response(404, 'Article Not Found: There is no article by that slug.');
        }
        // check for linked menu associated to this page
        $articleSlugData = MenuBuilderController::fetchLinkedMenusToSlug($articleSlugData);

        $postContent = $articleSlugData[0]->post_content;
        if (isset($postContent)) {
            $articleSlugData[0]->post_content = ContentInterceptorController::adpPortalWpServerNameContentReplace($postContent);
        }

        return [
            'slugResults' => $articleSlugData,
            'parentSlugResults' => $parentSlugResults
        ];
    }


    /**
     * Fetch a tutorial page and and validates if its path exists to wordpress
     * 
     * @param string $tutorialSlug    the slug of the tutorial page to fetch full data on 
     * @param array  $parentPathArray string array of all url params besides the article slug, used to verify if that path could exist
     * 
     * @return array [
     *  slugResults : all tutorial page data
     *  parentSlugResults : data related to the url path
     * ]
     * @author Cein
     */
    public static function getTutorialAndValidatePath( string $tutorialSlug, array $parentPathArray ): array {
        $parentSlugResults = [];

        if ($tutorialSlug === '') {
            ErrorController::response(404, 'Tutorial Slug Not Defined: Tutorial slug must be given.');
        }
        
        // check the parent path
        if (!empty($parentPathArray)) {
            $parentSlugResults = ValidationController::validateBuildSlugList($parentPathArray);
            if (count($parentPathArray) !== count($parentSlugResults)) {
                ErrorController::response(404, 'Tutorial Not Found: Invalid Path Variables.');
            }
        }
        
        // fetch the published tutorial
        $tutorialPageData = PagePostTutorialModel::getTutorialBySlug($tutorialSlug);
        $tutorialPageData[0]->settings = self::fetchTutorialSettings();

        if (empty($tutorialPageData)) {
            ErrorController::response(404, 'Tutorial Not Found: There is no tutorial by that slug.');
        }
        
        $updatedTutorialPageData = self::updateTutorialData($tutorialPageData);

        return [
            'slugResults' => $updatedTutorialPageData,
            'parentSlugResults' => $parentSlugResults
        ];
    }

    /**
     * Updates tutorial page data with extra content and fields
     * 
     * @param array $tutorialData the array containing the tutorial page object
     * 
     * @return array the updated array containing the tutorial page object
     * @author Cein
     */
    private static function updateTutorialData($tutorialData) {
        $updatedTutorialData = $tutorialData;

        $postContent = $updatedTutorialData[0]->post_content;
        if (isset($postContent)) {
            $updatedTutorialData[0]->post_content = ContentInterceptorController::adpPortalWpServerNameContentReplace($postContent);
        }
        
        $postId = $updatedTutorialData[0]->ID;
        if (isset($postId)) {
            $updatedTutorialData[0]->date_content = PagePostTutorialModel::getPostPageTutorialMetaById($postId, 'adp_portal_wp_date_content');
        }

        return $updatedTutorialData;
    }


    /**
     * Fetches a page's featured image, slug and excerpt
     * 
     * @param int $pageId the WP Id of the page
     * 
     * @return array [
     *       'page_featured_image_url' => '',
     *       'page_slug' => '',
     *       'page_excerpt' => '',
     *   ]
     * @author Cein
     */
    public static function getPageExcerptFeaturedImageSlugById( int $pageId ) {
        $returnArr = [
            'page_featured_image_url' => '',
            'page_slug' => '',
            'page_excerpt' => '',
        ];

        if (isset($pageId)) {
            $slugAndExcerptData = PagePostTutorialModel::getPageExcerptSlugById($pageId);

            $featuredImageUrl = get_the_post_thumbnail_url($pageId, 'large');
            $returnArr['page_featured_image_url'] = (isset($featuredImageUrl) ? $featuredImageUrl  : '');

            if (!empty($slugAndExcerptData)) {
                $slugAndExcerptDataObj = $slugAndExcerptData[0];
                $returnArr['page_slug'] = (isset($slugAndExcerptDataObj->post_name) ? $slugAndExcerptDataObj->post_name  : '');
                $returnArr['page_excerpt'] = (isset($slugAndExcerptDataObj->post_excerpt) ? $slugAndExcerptDataObj->post_excerpt  : '');
            }
        }

        return $returnArr;
    }

    /**
     * Fetches tutorials settings
     * 
     * @return object tutorials settings object
     * @author Omkar
     */
    public static function fetchTutorialSettings(){
        $tutorialsSettings = (object)[]; 
        $maintenanceModeIdFromDb = get_option('maintenanceModeId');
        $maintenanceModeMessageFromDb = stripcslashes(get_option('maintenanceModeMessage')); 
        if ($maintenanceModeIdFromDb) {
            $tutorialsSettings->maintenanceModeId = $maintenanceModeIdFromDb;
        } else {
            $tutorialsSettings->maintenanceModeId = '1';
        }
        if ($maintenanceModeMessageFromDb) {
            $tutorialsSettings->maintenanceModeMessage = $maintenanceModeMessageFromDb;
        } else {
            $tutorialsSettings->maintenanceModeMessage = '';
        }
        return $tutorialsSettings;
    }
    /**
     * Renders the tutorials settings submenu
     * Manages the updating of this pages variables
     * 
     * @return void
     * @author Omkar
     */
    public function manageTutorialsSettingsPage():void {     
        if (isset($_POST['maintenanceModeId'])) {
            $maintenanceModeIdNew = $_POST['maintenanceModeId'];
            update_option('maintenanceModeId', $maintenanceModeIdNew);
        }
        if (isset($_POST['maintenanceModeMessage'])) {
            $maintenanceModeMessageNew = $_POST['maintenanceModeMessage'];
            update_option('maintenanceModeMessage', $maintenanceModeMessageNew);
        }
        $menuBuilderView = new MenuBuilderView();
        $settingsModeslHtml = $menuBuilderView->buildTutorialsSettingsSelectOptionHtml($this->fetchTutorialSettings()->maintenanceModeId);
        $menuBuilderView->renderTutorialsSettingsPage($settingsModeslHtml, $this->fetchTutorialSettings()->maintenanceModeMessage);
    }


    /**
     * Fetch wp_post table rows
     * 
     * @param arr $includeIdList list of wp_post ids to return
     * @param arr $excludeIdList list of wp_post ids to not return
     * @param arr $typeList      list of wp_post types to include e.g: page, tutorial, category
     * @param str $sortBy        the wp_post table column to sort by
     * @param int $offSetBy      the pagination offset
     * @param int $limitBy       the pagination limit
     * 
     * @return arr list of wp_post table rows
     * @author Cein
     */
    public function getWpPosts(
        array $includeIdList = [],
        array $excludeIdList = [],
        array $typeList = [],
        string $sortBy = null,
        int $offSetBy = null,
        int $limitBy = null
    ): array {
        $includeIds = (is_array($includeIdList) ? $includeIdList: []);
        $excludeIds = (is_array($excludeIdList) ? $excludeIdList: []);
        $types = (is_array($typeList) ? $typeList: []);
        $sortKey = (is_string($sortBy) ? trim($sortBy): null);
        $offset = (is_numeric($offSetBy) &&  $offSetBy > 0? $offSetBy: null);
        $limit = (is_numeric($limitBy) && $limitBy > 0 ? $limitBy: null);
        return PagePostTutorialModel::getWpPostData($includeIds, $excludeIds, $types, $sortKey, $offset, $limit);
    }

}