<?php
/**
 * Error Handling Controller
 * 
 * All extra error handling
 *
 * PHP version 7.1
 *
 * @category Error_Controller
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers\General;

/**
 * Error Handling Controller
 *
 * @category Error_Controller
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class ErrorController {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Error response handler
     * 
     * @param int    $code            error reponse code
     * @param string $message         error reponse message
     * @param array  $data            data returned with the error reponse OPTIONAL
     * @param array  $additionalError error reponse additional data OPTIONAL
     * 
     * @return void
     * 
     * @author Cein
     */
    public static function response( int $code, string $message, array $data = [], array $additionalError = [] ):void {
        http_response_code($code);
        print json_encode(
            [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'additional_errors'=> $additionalError
            ]
        );
        die();
    }

}