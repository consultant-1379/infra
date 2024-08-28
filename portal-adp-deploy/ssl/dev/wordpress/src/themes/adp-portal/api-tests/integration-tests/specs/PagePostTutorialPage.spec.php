<?php
/**
 * Page, post and tutorial page rest end point Tests
 * 
 * All page, post and page related rest endpoint tests are done here
 *
 * PHP version 7.1
 *
 * @category WP_Routes_Tests_Page_Post_Tutorial
 * @package  ADP_Portal_API_Test
 * @author   Omkar <omkar.sadegaonkar@ammeon.com>
 */
require_once __DIR__.'/../../TestSettings.php';

use apiTests\TestSettings;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
/**
 * API Route Tests for the menu
 *
 * @category WP_Routes_Tests_Page_Post_Tutorial
 * @package  ADP_Portal_API_Test
 * @author   Omkar <omkar.sadegaonkar@ammeon.com>
 */
class PagePostTutorialPageTest extends TestCase {
    private static $_baseUrl;

    /**
     * Function run before each test cases
     *  
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * @return void
     */
    public static function setUpBeforeClass(): void {
        $testSettings = new TestSettings();
        self::$_baseUrl = $testSettings->getServerBaseUrl();
    }

    /**
     * API Test for Fetch Tutorial Page by Slug & Fetch Tutorial Page by Id
     * Check if the date_content is not set for e2e-tests-tutorial
     *  
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * @return void
     */
    public function testTutorialPageBySlugAndTutorialPageByIdShouldReturnTutorialPageData() {
        $client = new Client([ 'verify' => false ]);
        $tutorialPageSlug = 'e2e-tests-tutorial';
        $url = self::$_baseUrl ."tutorialPageBySlug?slug={$tutorialPageSlug}";

        $expectedStringTitle = '"post_title":"E2E TESTS Tutorial"';
        
        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck);

        $this->assertStringContainsString($expectedStringTitle, $responseToCheck);        
        $this->assertCount(1, $responseToCheckJson);
        $this->assertStringContainsString('ID', json_encode($responseToCheckJson[0]));
        $this->assertStringContainsString('', $responseToCheckJson[0]->date_content);

        $requestIdToCheck = $responseToCheckJson[0]->ID;
        $url = self::$_baseUrl ."tutorialPageById/{$requestIdToCheck}";

        $expectedStringId = '"ID":"' . $requestIdToCheck . '"';

        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();

        $this->assertStringContainsString($expectedStringTitle, $responseToCheck);
        $this->assertStringContainsString($expectedStringId, $responseToCheck);
    }

    /**
     * Check if the date_content is set for slug do-not-remove-uitest-tutorial
     * 
     * @author Cein
     * @return void
     */
    public function testTutorialPageBySlugAndTutorialPageByIdShouldReturnDateContentSet() {
        $client = new Client([ 'verify' => false ]);
        $tutorialPageSlug = 'do-not-remove-uitest-tutorial';
        $url = self::$_baseUrl ."tutorialPageBySlug?slug={$tutorialPageSlug}";

        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck);
        $requestIdToCheck = $responseToCheckJson[0]->ID;

        $this->assertNotEquals('', $responseToCheckJson[0]->date_content);

        $url = self::$_baseUrl ."tutorialPageById/{$requestIdToCheck}";

        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck);

        $this->assertNotEquals('', $responseToCheckJson[0]->date_content);
    }

    /**
     * API Test for Fetch and Validate Tutorial Page path
     * Check if the date_content is not set for e2e-tests-tutorial
     *  
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * @return void
     */
    public function testfetchTutorialPageValidatePathShouldReturnTutorialData() {
        $client = new Client(['verify' => false]);
        $url = self::$_baseUrl ."fetchTutorialPageValidatePath";
        $request_param = [
            'tutorialSlug' => 'e2e-tests-tutorial',
            'parentSlugArray' => []
        ];
        $requestData = json_encode($request_param);

        $response = $client->request(
            'POST', $url, 
            [
            'body' => $requestData
            ]
        );
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck)->slugResults;

        $expectedStringTitle = '"post_title":"E2E TESTS Tutorial"';
        $expectedStringName = '"post_name":"e2e-tests-tutorial"';

        $this->assertStringContainsString($expectedStringTitle, $responseToCheck);
        $this->assertStringContainsString($expectedStringName, $responseToCheck);
        $this->assertStringContainsString('', $responseToCheckJson[0]->date_content);
    }

    /**
     * Check if the date_content is set for slug do-not-remove-uitest-tutorial
     *  
     * @author Cein
     * @return none
     */
    public function testfetchTutorialPageValidatePathShouldReturnDateContentSet() {
        $client = new Client(['verify' => false]);
        $url = self::$_baseUrl ."fetchTutorialPageValidatePath";
        $request_param = [
            'tutorialSlug' => 'do-not-remove-uitest-tutorial',
            'parentSlugArray' => []
        ];
        $requestData = json_encode($request_param);

        $response = $client->request('POST', $url, ['body' => $requestData]);
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck)->slugResults;

        $this->assertNotEquals('', $responseToCheckJson[0]->date_content);
    }



    /**
     * API Test for Fetch and Validate Article Path
     *  
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * @return none
     */
    public function testfetchArticleValidatePathShouldReturnArticleData() {
        $client = new Client(['verify' => false]);
        $url = self::$_baseUrl ."fetchArticleValidatePath";
        $request_param = [
            'articleSlug' => 'e2e-tests-highlights',
            'articleType' => 'page',
            'parentSlugArray' => []
        ];
        $requestData = json_encode($request_param);
        $response = $client->request(
            'POST', $url, 
            [
            'body' => $requestData
            ]
        );

        $responseToCheck = $response->getBody()->getContents();

        $expectedStringTitle = '"post_title":"E2E Tests Highlights"';
        $expectedStringName = '"post_name":"e2e-tests-highlights"';

        $this->assertStringContainsString($expectedStringTitle, $responseToCheck);
        $this->assertStringContainsString($expectedStringName, $responseToCheck);
    }
}