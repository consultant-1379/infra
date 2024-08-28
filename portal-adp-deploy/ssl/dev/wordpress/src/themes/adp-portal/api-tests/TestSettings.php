<?php
/**
 * Test Settings
 *
 * PHP version 7.1
 *
 * @category WP_Test_Settings
 * @package  ADP_Portal_API_Test
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace apiTests;

/**
 * Test Settings
 *
 * @category WP_Admin_Tutorials
 * @package  ADP_Portal_API_Test
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class TestSettings {
    private $_serverBaseUrl;

    /**
     * Constructor
     */
    public function __construct() {
        $this->_serverBaseUrl = getenv('TestServerUrl');
    }

    /**
     * Get the server base url
     * 
     * @return string server base url
     */
    public function getServerBaseUrl() {
        return $this->_serverBaseUrl;
    }
}