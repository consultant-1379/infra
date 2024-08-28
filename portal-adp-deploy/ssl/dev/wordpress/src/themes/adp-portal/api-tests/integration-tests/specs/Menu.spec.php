<?php
/**
 * Menu rest end point Tests
 * 
 * All menu related rest endpoint tests are done here
 *
 * PHP version 7.1
 *
 * @category WP_Routes_Tests_Menu
 * @package  ADP_Portal_API_Test
 * @author   Omkar <omkar.sadegaonkar@ammeon.com>
 * @license  www.ericsson.com ADP
 * @link     Integration-Tests
 */
require_once __DIR__.'/../../TestSettings.php';

use apiTests\TestSettings;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
/**
 * API Route Tests for the menu
 *
 * @category WP_Routes_Tests_Menu
 * @package  ADP_Portal_API_Test
 * @author   Omkar <omkar.sadegaonkar@ammeon.com>
 * @license  www.ericsson.com ADP
 * @link     Integration-Tests
 */
class MenuTest extends TestCase
{
    public static $baseUrl;

    /**
     * Function run before each test cases
     *  
     * @return void
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     */
    public static function setUpBeforeClass(): void {
        $testSettings = new TestSettings();
        self::$baseUrl = $testSettings->getServerBaseUrl();
    }

    /**
     * API Test for Fetch Menu by slug
     * fetch_menu/main
     *  
     * @return void
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * 
     * @deprecated
     */
    public function testFetchMenuShouldReturnMainMenu(): void {
        $client = new Client(['verify' => false]);
        $menuSlug = 'main';
        $url = self::$baseUrl ."fetch_menu/{$menuSlug}";

        $response = $client->request('GET', $url);

        $expectedString = '"title":"e2e main menu"';

        $this->assertStringContainsString($expectedString, $response->getBody()->getContents());
    }

    /**
     * Fetch the Main navigation menu
     * fetch_menu/main
     *  
     * @return void
     * @author Omkar <omkar.sadegaonkar@ammeon.com>, Cein
     */
    public function testMenuShouldReturnMainMenu(): void {
        $client = new Client(['verify' => false]);
        $url = self::$baseUrl ."menu/main";

        $response = $client->request('GET', $url)->getBody()->getContents();
        $result = json_decode($response);
        $expectedString = '"title":"e2e main menu"';

        $this->assertEquals(count($result->menus), 1);
        $this->assertTrue(is_string($result->last_modified) && trim($result->last_modified) != '' && $result->last_modified != '1970-01-01 00:00:00');

        $this->assertStringContainsString($expectedString, $response);
    }

    /**
     * Tutorial menu check for date_content, target, portal_url, slug, parent slug
     * slug do-not-remove-uitest-tutorial should have a date
     * slug e2e-tests-tutorial should not have a date
     * fetch_menu/tutorials
     *  
     * @return void
     * @author Cein
     * 
     * @deprecated
     */
    public function testShouldReturnTutorialMenuWithAdditionalData(): void {
        $client = new Client(['verify' => false]);
        $url = self::$baseUrl ."fetch_menu/tutorials";
        $testData = [
            [
                'title' => 'Do not remove – UITEST tutorial',
                'target' => '',
                'portal_url' => '/do-not-remove-uitest-tutorial',
                'slug' => 'do-not-remove-uitest-tutorial',
                'parent_slug' => '',
                'portal_route' => '',
                'linked_menu_paths' => [
                    [
                        'slug' => 'do-not-remove-uitest-tutorial',
                        'portal_url' => '/do-not-remove-uitest-tutorial',
                        'parent_path' => '/getstarted/tutorials',
                        'parent_child_path' => '/getstarted/tutorials/do-not-remove-uitest-tutorial'
                    ],
                    [
                        'slug' => 'do-not-remove-uitest-tutorial',
                        'portal_url' => '/do-not-remove-uitest-tutorial',
                        'parent_path' => '/do-not-remove-footer-header/tutorials',
                        'parent_child_path' => '/do-not-remove-footer-header/tutorials/do-not-remove-uitest-tutorial'
                    ]
                ]
            ],
            [
                'title' => 'E2E TESTS Tutorial',
                'target' => '',
                'portal_url' => '/do-not-remove-uitest-tutorial/e2e-tests-tutorial',
                'slug' => 'e2e-tests-tutorial',
                'parent_slug' => 'do-not-remove-uitest-tutorial',
                'linked_menu_paths' => [
                    [
                        'slug' => 'e2e-tests-tutorial',
                        'portal_url' => '/do-not-remove-uitest-tutorial/e2e-tests-tutorial',
                        'parent_path' => '/getstarted/tutorials',
                        'parent_child_path' => '/getstarted/tutorials/do-not-remove-uitest-tutorial/e2e-tests-tutorial'
                    ],
                    [
                        'slug' => 'e2e-tests-tutorial',
                        'portal_url' => '/do-not-remove-uitest-tutorial/e2e-tests-tutorial',
                        'parent_path' => '/do-not-remove-footer-header/tutorials',
                        'parent_child_path' => '/do-not-remove-footer-header/tutorials/do-not-remove-uitest-tutorial/e2e-tests-tutorial'
                    ]
                ]
            ],
        ];
        
        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck);

        foreach ($responseToCheckJson as $menuItemObj) {
            $title = $menuItemObj->title;
            if ($title === $testData[0]['title'] ) {
                $this->assertIsString($menuItemObj->date_content);
                $this->assertNotEquals('', $menuItemObj->date_content);
                $this->assertEquals($testData[0]['target'], $menuItemObj->target);
                $this->assertEquals($testData[0]['portal_url'], $menuItemObj->portal_url);
                $this->assertEquals($testData[0]['slug'], $menuItemObj->slug);
                $this->assertEquals($testData[0]['parent_slug'], $menuItemObj->parent_slug);
            } else if ($title === $testData[1]['title']) {
                
                $this->assertIsString($menuItemObj->date_content);
                $this->assertNotEquals('', $menuItemObj->date_content);
                $this->assertEquals($testData[1]['target'], $menuItemObj->target);
                $this->assertEquals($testData[1]['portal_url'], $menuItemObj->portal_url);
                $this->assertEquals($testData[1]['slug'], $menuItemObj->slug);
                $this->assertEquals($testData[1]['parent_slug'], $menuItemObj->parent_slug);

                $testLinkMenu = $testData[1]['linked_menu_paths'];
                $this->assertEquals(count($testLinkMenu), count($menuItemObj->linked_menu_paths));

                $this->assertEquals($testLinkMenu[0]['slug'], $menuItemObj->linked_menu_paths[0]->slug);
                $this->assertEquals($testLinkMenu[0]['portal_url'], $menuItemObj->linked_menu_paths[0]->portal_url);
                $this->assertEquals($testLinkMenu[0]['parent_path'], $menuItemObj->linked_menu_paths[0]->parent_path);
                $this->assertEquals($testLinkMenu[0]['parent_child_path'], $menuItemObj->linked_menu_paths[0]->parent_child_path);
                $this->assertEquals($testLinkMenu[1]['slug'], $menuItemObj->linked_menu_paths[1]->slug);
                $this->assertEquals($testLinkMenu[1]['portal_url'], $menuItemObj->linked_menu_paths[1]->portal_url);
                $this->assertEquals($testLinkMenu[1]['parent_path'], $menuItemObj->linked_menu_paths[1]->parent_path);
                $this->assertEquals($testLinkMenu[1]['parent_child_path'], $menuItemObj->linked_menu_paths[1]->parent_child_path);
            }
        }
    }

    /**
     * Tutorial menu check for date_content, target, portal_url, slug, parent slug
     * slug do-not-remove-uitest-tutorial should have a date
     * slug e2e-tests-tutorial should not have a date
     * fetch_menu/tutorials
     *  
     * @return void
     * @author Cein
     */
    public function testMenuShouldReturnTutorialMenuWithAdditionalData(): void {
        $client = new Client(['verify' => false]);
        $url = self::$baseUrl ."menu/tutorials";
        $testData = [
            [
                'title' => 'Do not remove – UITEST tutorial',
                'target' => '',
                'portal_url' => '/do-not-remove-uitest-tutorial',
                'slug' => 'do-not-remove-uitest-tutorial',
                'parent_slug' => '',
            ],
            [
                'title' => 'E2E TESTS Tutorial',
                'target' => '',
                'portal_url' => '/do-not-remove-uitest-tutorial/e2e-tests-tutorial',
                'slug' => 'e2e-tests-tutorial',
                'parent_slug' => 'do-not-remove-uitest-tutorial',
            ],
        ];
        
        $result = json_decode($client->request('GET', $url)->getBody()->getContents());

        $this->assertEquals(count($result->menus), 1);
        $this->assertTrue(is_string($result->last_modified) && trim($result->last_modified) != '' && $result->last_modified != '1970-01-01 00:00:00');

        foreach ($result->menus as $menuItemObj) {
            $title = $menuItemObj->title;
            if ($title === $testData[0]['title'] ) {
                $this->assertIsString($menuItemObj->date_content);
                $this->assertNotEquals('', $menuItemObj->date_content);
                $this->assertEquals($testData[0]['target'], $menuItemObj->target);
                $this->assertEquals($testData[0]['portal_url'], $menuItemObj->portal_url);
                $this->assertEquals($testData[0]['slug'], $menuItemObj->slug);
                $this->assertEquals($testData[0]['parent_slug'], $menuItemObj->parent_slug);
            } else if ($title === $testData[1]['title']) {
                $this->assertIsString($menuItemObj->date_content);
                $this->assertNotEquals('', $menuItemObj->date_content);
                $this->assertEquals($testData[1]['target'], $menuItemObj->target);
                $this->assertEquals($testData[1]['portal_url'], $menuItemObj->portal_url);
                $this->assertEquals($testData[1]['slug'], $menuItemObj->slug);
                $this->assertEquals($testData[1]['parent_slug'], $menuItemObj->parent_slug);
            }
        }
    }


    /**
     * API Test for Fetch Highlight Menu Data
     *  
     * @return void
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     * 
     * @deprecated
     */
    public function testFetchHighlightsMenuListShouldReturnHighlightsMenuData(): void {
        $client = new Client(['verify' => false]);
        $url = self::$baseUrl ."highlightsMenuList";

        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();

        $expectedStringSlug = '"page_slug":"e2e-tests-highlights"';

        $this->assertStringContainsString($expectedStringSlug, $responseToCheck);
    }

    /**
     * Fetch highlights from the menu endpoint
     * 
     * @return void
     * @author Cein
     */
    public function testMenuShouldReturnHighlightsMenuData(): void {
        $client = new Client(['verify' => false]);
        $result = json_decode($client->request('GET', self::$baseUrl ."menu/highlights")->getBody()->getContents());

        $testMenuItems = [
            'cloud-container-distribution-ccd' => [
                'title' => 'Tutorials',
                'description' => '/getstarted/tutorials/short-code-test-parent/questionnaire-shortcode-testing',
                'portal_url' => '/cloud-container-distribution-ccd',
                'page_featured_image_url' => 'wp-content/uploads/2020/01/15420190327_web-1024x684-1024x684.jpg',
                'page_excerpt' => 'Excerpt',
                'page_slug' => 'cloud-container-distribution-ccd',
                'portal_route' => '/getstarted/tutorials/short-code-test-parent/questionnaire-shortcode-testing',
                'linked_menu_paths' => []
            ],
            'adp_roadmaps' => [
                'title'=> 'ADP Roadmaps',
                'description'=> ' ',
                'portal_url'=> '/adp_roadmaps',
                'page_featured_image_url'=> '',
                'page_excerpt'=> '',
                'page_slug'=> 'adp_roadmaps',
                'portal_route' => '/adp_roadmaps',
                'linked_menu_paths' => []
            ],
            'general-directives' => [
                'title' => 'Ericsson.com',
                'description' => 'https://www.ericsson.com/en',
                'portal_url' => '/general-directives',
                'page_featured_image_url' => 'wp-content/uploads/2020/01/Subfilm-13_web-1024x576-1024x576.jpg',
                'page_excerpt' => 'Should be General',
                'page_slug' => 'general-directives',
                'portal_route' => 'https://www.ericsson.com/en',
                'linked_menu_paths' => []
            ]

        ];
        
        $this->assertEquals(count($result->menus), 1);
        $this->assertTrue(is_string($result->last_modified) && trim($result->last_modified) != '' && $result->last_modified != '1970-01-01 00:00:00');

        $matchCount = 0;

        foreach ($result->menus[0]->items as $menuObj) {
            if (isset($testMenuItems[$menuObj->slug])) {
                foreach ($testMenuItems[$menuObj->slug] as $testKey => $testValue) {
                    if ($testKey == 'page_featured_image_url') {
                        $this->assertStringContainsString($testValue, $menuObj->$testKey);
                    } else {
                        $this->assertEquals($menuObj->$testKey, $testValue);
                    }
                }
                $matchCount++;
            }
        }

        $this->assertEquals($matchCount, (count($testMenuItems)));
        
    }


    /**
     * API Test to Get Menu Data by slug
     * /menu?slug=tutorials
     * check the target, portal_url, slug, parent slug entries
     *  
     * @return void
     * @author Omkar <omkar.sadegaonkar@ammeon.com>
     */
    public function testGetMenuBySlugShouldReturnSpecificMenuData(): void {
        $client = new Client(['verify' => false]);
        $url = self::$baseUrl ."menu" ."?slug=tutorials";

        $testData = [
            [
                'title' => 'Do not remove – UITEST tutorial',
                'target' => '',
                'portal_url' => '/do-not-remove-uitest-tutorial',
                'slug' => 'do-not-remove-uitest-tutorial',
                'parent_slug' => '',
            ],
            [
                'title' => 'E2E TESTS Tutorial',
                'target' => '',
                'portal_url' => '/do-not-remove-uitest-tutorial/e2e-tests-tutorial',
                'slug' => 'e2e-tests-tutorial',
                'parent_slug' => 'do-not-remove-uitest-tutorial',
            ],
        ];
        
        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck);

        foreach ($responseToCheckJson->items as $arrPos => $menuItemObj) {
            $title = $menuItemObj->title;
            if ($title === $testData[0]['title'] ) {
                $this->assertIsString($menuItemObj->date_content);
                $this->assertNotEquals('', $menuItemObj->date_content);
                $this->assertEquals($testData[0]['target'], $menuItemObj->target);
                $this->assertEquals($testData[0]['portal_url'], $menuItemObj->portal_url);
                $this->assertEquals($testData[0]['slug'], $menuItemObj->slug);
                $this->assertEquals($testData[0]['parent_slug'], $menuItemObj->parent_slug);
            } else if ($title === $testData[1]['title']) {
                $this->assertIsString($menuItemObj->date_content);
                $this->assertNotEquals('', $menuItemObj->date_content);
                $this->assertEquals($testData[1]['target'], $menuItemObj->target);
                $this->assertEquals($testData[1]['portal_url'], $menuItemObj->portal_url);
                $this->assertEquals($testData[1]['slug'], $menuItemObj->slug);
                $this->assertEquals($testData[1]['parent_slug'], $menuItemObj->parent_slug);
            }
        }
    }

    /**
     * Checks additional data added to the footer menu
     * /fetch_menu/footer
     * check the target, portal_url, slug, parent slug entries
     * 
     * @return void
     * @author Cein
     */
    public function testFetchFooterMenuWithAdditionalData(): void {
        $client = new Client(['verify' => false]);
        $url = self::$baseUrl ."fetch_menu/footer";

        $testData = [
            [
                'title' => 'e2e Tests',
                'target' => '',
                'portal_url' => '/do-not-remove-footer-header',
                'slug' => 'do-not-remove-footer-header',
                'object' => 'category',
                'parent_slug' => '',
                'menu_level' => 0,
                'linked_menu_paths' => []
            ],
            [
                'title' => 'Internal Link',
                'target' => '_self',
                'portal_url' => '/marketplace',
                'slug' => 'marketplace',
                'object' => 'custom',
                'parent_slug' => 'do-not-remove-footer-header',
                'menu_level' => 1,
                'linked_menu_paths' => []
            ],
            [
                'title' => 'External Link',
                'target' => '_blank',
                'portal_url' => '',
                'slug' => '',
                'object' => 'custom',
                'parent_slug' => 'do-not-remove-footer-header',
                'menu_level' => 1,
                'linked_menu_paths' => []
            ],
            [
                'title' => 'Mailto',
                'target' => 'mail',
                'portal_url' => 'mailto:PDLDPPORTA@pdl.internal.ericsson.com?subject=Portal%20Feedback&cc=PDLADPFRAM@pdl.internal.ericsson.com',
                'slug' => 'mailto:PDLDPPORTA@pdl.internal.ericsson.com?subject=Portal%20Feedback&cc=PDLADPFRAM@pdl.internal.ericsson.com',
                'object' => 'custom',
                'parent_slug' => 'do-not-remove-footer-header',
                'menu_level' => 1,
                'linked_menu_paths' => []
            ],
            [
                'title' => 'Article Page',
                'target' => '',
                'portal_url' => '/do-not-remove-footer-header/e2e-test-page-1',
                'slug' => 'e2e-test-page-1',
                'object' => 'page',
                'parent_slug' => 'do-not-remove-footer-header',
                'menu_level' => 1,
                'linked_menu_paths' => []
            ],
        ];

        $response = $client->request('GET', $url);
        $responseToCheck = $response->getBody()->getContents();
        $responseToCheckJson = json_decode($responseToCheck);

        $countTestDataComplete = 0;
        foreach ($responseToCheckJson as $menuItemObj) {
            foreach ($testData as $testItem) {
                if ($menuItemObj->title === $testItem['title']) {
                    $this->assertEquals($testItem['target'], $menuItemObj->target);
                    $this->assertEquals($testItem['portal_url'], $menuItemObj->portal_url);
                    $this->assertEquals($testItem['slug'], $menuItemObj->slug);
                    $this->assertEquals($testItem['parent_slug'], $menuItemObj->parent_slug);
                    $this->assertEquals($testItem['linked_menu_paths'], $menuItemObj->linked_menu_paths);
                    $countTestDataComplete++;
                }
            }
        }

        $this->assertEquals($countTestDataComplete, count($testData));
    }

    /**
     * API Test to Get Menu Data without slug
     * Should fetch all menus
     * /menu
     *  
     * @return void
     * @author Omkar <omkar.sadegaonkar@ammeon.com>,cein
     */
    public function testMenuWithoutSlugShouldReturnAllMenusData(): void{
        $client = new Client(['verify' => false]);
        $url = self::$baseUrl ."menu";
        
        $result = json_decode($client->request('GET', $url)->getBody()->getContents());

        $testMenuSlugs = ['tutorials' => true, 'main' => true, 'footer' => true, 'pages-not-on-any-menu' => true];

        $matchCount = 0;
        
        foreach ($result->menus as $menuObj) {
            if ($testMenuSlugs[$menuObj->slug] == true) {
                $matchCount++;
            }
        }
        
        $this->assertTrue(is_string($result->last_modified) && trim($result->last_modified) != '' && $result->last_modified != '1970-01-01 00:00:00');
        $this->assertEquals($matchCount, count($testMenuSlugs));
    }

    /**
     * Tests the menu pages-not-on-any-menu called from the menu endpoint
     * /menu/pages-not-on-any-menu
     * 
     * @return void
     * @author Cein
     */
    public function testMenuShouldReturnPagesNotOnAnyMenu(): void {
        $client = new Client(['verify' => false]);

        $testMenuObj = [
            'term_id' => 9999999999999,
            'name' => 'Pages Not On Any Menu',
            'slug' => 'pages-not-on-any-menu',
            'term_group' => 0,
            'term_taxonomy_id' => 0,
            'taxonomy' => 'nav_menu',
            'description' => '',
            'parent' => 0,
            'filter' => 'raw'
        ];

        $testMenuItemObj = [ 
            'post_status' => 'publish',
            'description' => '',
            'parent_slug' => '',
            'post_author' => '',
            'post_title' => '',
            'post_excerpt' => '',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_password' => '',
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => '',
            'post_parent' => 0,
            'menu_order' => 0,
            'post_type' => 'nav_menu_item',
            'post_mime_type' => '',
            'comment_count' => '0',
            'filter' => 'raw',
            'menu_item_parent' => '0',
            'type' => 'post_type',
            'url' => '',
            'target' => '',
            'attr_title' => '',
            'classes' => [''],
            'xfn' => '',
            'timeToComplete' => '',
            'linkedMenuFirstPageSlug' => '',
            'linkedMenuSlug' => '',
            'date_content' => '',
            'menu_level' => 0,
            'highlights_page_featured_image_url' => '',
            'highlights_page_excerpt' => '',
            'highlights_page_slug' => '',
            'highlights_portal_route' => '',
            'linked_menu_paths' => []
        ];
        
        $result = json_decode($client->request('GET', self::$baseUrl .'menu/pages-not-on-any-menu')->getBody()->getContents());
        $menu = $result->menus[0];
        $resultlLastModDate = trim($result->last_modified);

        $this->assertTrue($resultlLastModDate != '' && $resultlLastModDate != '1970-01-01 00:00:00');
        $this->assertEquals(count($result->menus), 1);
        $this->assertTrue(count($menu->items) > 1);

        foreach ($testMenuObj as $menuKey => $menuValue) {
            $this->assertEquals($menu->$menuKey, $menuValue);
        }

        foreach ($result->menus[0]->items as $menuItemObj) {
            foreach ($testMenuItemObj as $testKey => $testValue) {
                $this->assertEquals($menuItemObj->$testKey, $testValue);
            }

            $this->assertTrue(is_string($menuItemObj->object_id) && trim($menuItemObj->object_id) != '');
            $this->assertTrue($menuItemObj->object == 'page' || $menuItemObj->object == 'tutorials');
            $this->assertTrue($menuItemObj->type_label == 'Page' || $menuItemObj->type_label == 'Tutorial page');
            $this->assertTrue(is_string($menuItemObj->title) && trim($menuItemObj->title) != '');
            $this->assertTrue(is_string($menuItemObj->slug) && trim($menuItemObj->slug) != '');
            $this->assertEquals($menuItemObj->portal_url, '/'.$menuItemObj->slug);
            $this->assertTrue(is_numeric($menuItemObj->ID) && $menuItemObj->ID > 0);
            $this->assertEquals($menuItemObj->post_name, "$menuItemObj->ID");
            $this->assertEquals($menuItemObj->db_id, $menuItemObj->ID);
        }
    }
}