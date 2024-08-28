<?php
/**
 * Wordpress API Request Hooks
 * 
 * PHP version 7.1
 *
 * @category WP_AP_Request
 * @package  ADP_Portal_API
 * @author   John <john.dolan@ammeon.com>
 */
namespace api\Hooks;

require_once __DIR__.'/../controllers/Metric.controller.php';

use api\Controllers\MetricController;

/**
 * Wordpress API Request Hooks
 *
 * @category WP_AP_Request
 * @package  ADP_Portal_API
 * @author   John <john.dolan@ammeon.com>
 */
class ApiRequestHook {
    
    /**
     * Constructor
     */
    public function __construct(){
        add_filter('rest_pre_serve_request', [$this,'multiformatRestPreServeRequest'], 10, 4);
    }

    /**
     * Interceptor for the metrics endpoint used to return plain text instead
     * of json data.
     * The content of the metrics string is echoed and the content type header
     * is adjusted.
     * 
     * @param bool   $served  tells the WP-API that we sent the response
     * @param string $result  unused
     * @param object $request Wordpress request object
     * @param object $server  Wordpress server object
     * 
     * @return bool 
     * @author John <john.dolan@ammeon.com>
     */
    function multiformatRestPreServeRequest( $served, $result, $request, $server ) {
        if ($request->get_route()=="/metrics") {

            header('Content-Type: text/plain; charset=' . get_option('blog_charset'));
            $metrics= MetricController::getWordpressMetrics();
            echo $metrics;
            $served = true; // tells the WP-API that we sent the response already
        }
        return $served;
    }
}