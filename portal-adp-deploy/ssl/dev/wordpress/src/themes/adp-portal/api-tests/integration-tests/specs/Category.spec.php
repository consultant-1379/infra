<?php
/**
 * Category rest end point Tests
 * 
 * All category related rest endpoint tests are done here
 *
 * PHP version 7.1
 *
 * @category WP_Routes_Tests_Categories
 * @package  ADP_Portal_API_Test
 * @author   Omkar <omkar.sadegaonkar@ammeon.com>
 */
require_once __DIR__.'/../../TestSettings.php';

use apiTests\TestSettings;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
/**
 * API Route Tests for Categories
 *
 * @category WP_Routes_Tests_Categories
 * @package  ADP_Portal_API_Test
 * @author   Omkar <omkar.sadegaonkar@ammeon.com>
 */
class CategoryTest extends TestCase {
    public static $baseUrl;

    /**
     * Function run before each test cases
     *  
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * @return none
     */
    public static function setUpBeforeClass(): void {
        $testSettings = new TestSettings();
        self::$baseUrl = $testSettings->getServerBaseUrl();
        $responseToCheck = '';
    }


    /**
     * API Test for Fetch All Categories
     *  
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * @return none
     */
    public function testFetchAllCategoriesWithNoneReturnAllCategories() {
        $client = new Client(
            [
            'verify' => false
            ]
        );
        $expectedString = '"name":"Tutorials","slug":"tutorials"';
        $url = self::$baseUrl ."allcategories";
        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();
        $this->assertStringContainsString($expectedString, $responseToCheck);
    }
}