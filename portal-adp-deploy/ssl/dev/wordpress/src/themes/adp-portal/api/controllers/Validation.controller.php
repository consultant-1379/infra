<?php
/**
 * Wordpress Validation Controller
 * 
 * Any complex validation
 *
 * PHP version 7.1
 *
 * @category WP_Validation
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers;

require_once __DIR__.'/../models/PagePostTutorial.model.php';
require_once __DIR__.'/../models/Category.model.php';

use api\Models\PagePostTutorialModel;
use api\Models\CategoryModel;

/**
 * Wordpress Validation Controller
 *
 * @category WP_Validation
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class ValidationController {

    /**
     * Contructor
     */
    public function __construct(){
    }

    /**
     * Loops through a list of slugs and matches related data to Pages/Posts/Tutorials/Categories
     * 
     * @param array $slugList list of slugs
     * 
     * @return array matched slugs with related data to the Pages/Posts/Tutorials/Categories
     * @author Cein
     */
    public static function validateBuildSlugList( array $slugList) {
        $returnArray = [];
        
        if (!empty($slugList)) {
            $pagePostTutorialModel = new PagePostTutorialModel();

            foreach ($slugList as $slug) {
                $pagePostTutorialResult = $pagePostTutorialModel::getPagePostTutorialBySlug($slug);
                if (!empty($pagePostTutorialResult)) {
                    array_push($returnArray, $pagePostTutorialResult[0]);
                    continue;
                }
                
                $categoryResult = CategoryModel::getBySlug($slug);
                if (!empty($categoryResult)) {
                    array_push($returnArray, $categoryResult[0]);
                }
            }
        }

        return $returnArray;
    }

    /**
     * Validates a route path to all wp page, post, tutorial and category slugs and ignores the current article slug 
     * 
     * @param string $currentArticleSlug the article slug to ignore withing the route path
     * @param string $routeToValidate    route containing slugs to validate
     * 
     * @return bool if the route is valid
     * @author Cein
     */
    public static function validateStrRoute( string $currentArticleSlug, string $routeToValidate ):bool {
        $pathToTest = str_replace($currentArticleSlug, '', $routeToValidate);
        $explodedPath = explode("/", $pathToTest);
        $slugTestArray = [];
        foreach ($explodedPath as $position => $slug) {
            if (trim($slug) !== '') {
                array_push($slugTestArray, $slug);
            }
        }
        
        if (!empty($slugTestArray)) {
            $validatedArray = self::validateBuildSlugList($slugTestArray);
            return ( count($validatedArray) === count($slugTestArray) );
        }
        return false;
    }

}