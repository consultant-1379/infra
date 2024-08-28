<?php
/**
 * Wordpress Pages Posts Tutorials Model
 * 
 * All models for any wordpress page/post/tutorial
 *
 * PHP version 7.1
 *
 * @category WP_Pages_Posts_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Models;



/**
 * Wordpress Pages Posts Tutorials Model
 *
 * @category WP_Pages_Posts_Tutorials
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class PagePostTutorialModel {

    /**
     * Constructor
     */
    public function __construct(){
    }

    /**
     * Get wp_post table rows
     * 
     * @param arr $includeIdList list of wp_post ids to return
     * @param arr $excludeIdList list of wp_post ids to not return
     * @param arr $typeList      list of wp_post types to include e.g: page, tutorial, category
     * @param str $sortBy        the wp_post table column to sort by
     * @param int $offSet        the pagination offset
     * @param int $limit         the pagination limit
     * 
     * @return arr list of fetched wp_post rows
     * @author Cein
     */
    public static function getWpPostData(
        array $includeIdList = [],
        array $excludeIdList = [],
        array $typeList = [],
        string $sortBy = null,
        int $offSet = null,
        int $limit = null
    ): array {
        global $wpdb;

        $query = '
            SELECT 
                `ID`,
                `post_author`,
                `post_date`,
                `post_date_gmt`,
                `post_content`,
                `post_title`,
                `post_excerpt`,
                `post_status`,
                `comment_status`,
                `ping_status`,
                `post_name`,
                `to_ping`,
                `pinged`,
                `post_modified`,
                `post_modified_gmt`,
                `post_content_filtered`,
                `post_parent`,
                `guid`,
                `menu_order`,
                `post_type`,
                `post_mime_type`,
                `comment_count`
            FROM 
                `wp_posts`
            WHERE
                `post_status` = \'publish\'
        ';

        $pdoQueries = [
            'where' => [],
            'other' => [],
        ];
        $pdoValues = [];

        if (is_array($includeIdList) && count($includeIdList)) {
            array_push($pdoQueries['where'], '`ID` IN ('+ str_repeat('%d', count($includeIdList) - 1) . '%d' . ')');
            $pdoValues = array_merge($pdoValues, $includeIdList);
        }

        if (is_array($typeList) && count($typeList)) {
            array_push($pdoQueries['where'], '`post_type` IN ('. str_repeat('%s,', count($typeList) - 1) . '%s' . ')');
            $pdoValues = array_merge($pdoValues, $typeList);
        }

        if (is_array($excludeIdList) && count($excludeIdList)) {
            array_push($pdoQueries['where'], '`ID` NOT IN ('. str_repeat('%d,', count($excludeIdList) - 1) . '%d' . ')');
            $pdoValues = array_merge($pdoValues, $excludeIdList);
        }

        if (is_numeric($limit)) {
            array_push($pdoQueries['other'], 'LIMIT ' . (is_numeric($offSet) ? "%d, " : '') . ', %d');
            if (is_numeric($offSet)) {
                array_push($pdoValues, $offSet);
            }
            array_push($pdoValues, $limit);
        }

        if (count($pdoQueries['where'])) {
            foreach ($pdoQueries['where'] as $whereQuery) {
                $query .= " AND $whereQuery";
            }
        }
        
        if (count($pdoQueries['other']) > 0) {
            foreach ($pdoQueries['other'] as $otherIndex => $otherQuery) {
                $query .= " $otherQuery";
            }
        }

        if ($sortBy) {
            $query = $query . " ORDER BY `wp_posts`.`$sortBy`";
        }

        $preparedQuery = $wpdb->prepare($query, $pdoValues);

        return $wpdb->get_results($preparedQuery);
    }

    /**
     * Get either a Page/Post/Tutoral page by the given slug
     * 
     * @param string $slug the slug or the Page/Post/Tutoral page
     * 
     * @return array of a object containing Page/Post/Tutoral page data
     * @author Cein
     */
    public static function getPagePostTutorialBySlug( string $slug ): array {
        global $wpdb;
        
        $query = '
            SELECT 
                `ID`,
                `post_title` as name,
                `post_name` as slug,
                `post_type` as type
            FROM `wp_posts`
            WHERE
                `post_name` = %s AND
                ( `post_type` = "page" OR `post_type` = "post" OR `post_type` = "tutorials" )
        ';
        $preparedQuery = $wpdb->prepare($query, $slug); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }

    /**
     * Get tutorial page data by Id
     * 
     * @param int $tutorialPageId the wp_post ID of the tutorial page
     * 
     * @return array containing a object containing wp tutorial page data
     * @author Cein
     */
    public static function getTutorialById( int $tutorialPageId ): array {
        global $wpdb;
        
        $query = '
            SELECT 
                wp_posts.* 
            FROM wp_posts
            WHERE 1=1 AND 
                wp_posts.ID = %d AND 
                wp_posts.post_type = "tutorials"
            ORDER BY wp_posts.post_date DESC
        ';
        $preparedQuery = $wpdb->prepare($query, $tutorialPageId); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }

    /**
     * Get tutorial page data by slug
     * 
     * @param string $tutorialPageSlug the wp_post page slug of the tutorial page
     * 
     * @return array containing a object containing wp tutorial page data
     * @author Cein
     */
    public static function getTutorialBySlug( string $tutorialPageSlug ): array {
        global $wpdb;
        
        $query = '
            SELECT 
                wp_posts.* 
            FROM wp_posts  
            WHERE 
                wp_posts.post_name = %s AND 
                wp_posts.post_type = "tutorials" AND
                wp_posts.post_status = "publish"
            ORDER BY wp_posts.post_date DESC
        ';

        $preparedQuery = $wpdb->prepare($query, $tutorialPageSlug); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }


    /**
     * Fetch Preview title and content
     * 
     * @param int $previewId the wp_post ID of the preview
     * 
     * @return array a object which has the post_title and post_content
     * @author Cein
     */
    public static function getPreviewTitleContent( int $previewId ): array {
        global $wpdb;
        
        $query = '
            SELECT 
                post_title,
                post_content
            FROM wp_posts
            WHERE
                ID = %d
            ORDER BY post_modified DESC
            LIMIT 1
        ';
        $preparedQuery = $wpdb->prepare($query, $previewId); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }

    /**
     * Fetches Page/Post/Tutorial by post type and slug
     * 
     * @param string $postType the WP post type of the page/post/tutorial
     * @param string $slug     the slug of the page/post/tutorial
     * 
     * @return array the data of the page/post/tutorial
     * @author Cein
     */
    public function getByPostTypeSlug( string $postType, string $slug ): array {
        $args = array(
            'name'        => $slug,
            'post_type'   => $postType,
            'post_status' => 'publish',
            'numberposts' => 1
        );
        return get_posts($args);
    }


    /**
     * Fetch Page Slug and excerpt by the given page Id
     * 
     * @param int $pageId the wp_post ID of the required page
     * 
     * @return array a object which has the post_name aka slug and post_excerpt
     * @author Cein
     */
    public static function getPageExcerptSlugById( int $pageId ) {
        global $wpdb;
        
        $query = '
            SELECT 
                post_excerpt,
                post_name
            FROM wp_posts 
            WHERE ID= %d
        ';
        $preparedQuery = $wpdb->prepare($query, $pageId); 
        return $wpdb->get_results($preparedQuery, 'OBJECT');
    }

    /**
     * Fetches post/page/tutorial meta data
     * 
     * @param string $id      the id of the post/page/tutorial relating to the meta data
     * @param string $metaKey the db key for the related meta data
     * 
     * @return string the value of the metadata, if none found an empty string is returned
     * @author Cein
     */
    public static function getPostPageTutorialMetaById(string $id, string $metaKey): string {
        $dbMetaData = get_post_meta($id, $metaKey, true);
        return (isset($dbMetaData) ? $dbMetaData : '');
    }
}
