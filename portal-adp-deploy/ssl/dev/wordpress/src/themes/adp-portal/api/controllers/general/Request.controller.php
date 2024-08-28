<?php
/**
 * ADP Portal WP API General Request Controller
 * 
 * Any ADP Portal WP API requests will be managed here
 *
 * PHP version 7.1
 *
 * @category WP_General
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
namespace api\Controllers\General;

/**
 * ADP Portal WP API General Request Controller
 * 
 * Any ADP Portal WP API requests will be managed here
 *
 * @category WP_General
 * @package  ADP_Portal_API
 * @author   Cein <cein-sven.da.costa@ericsson.com>
 */
class RequestController {
    private $options = [
        'http' => [
            'header' => 'Content-type: application/x-www-form-urlencoded\r\n',
            'method' => '',
            'content' => ''
        ],
        'ssl' => [
            'verify_peer'=>false,
            'verify_peer_name'=>false,
        ]
    ];
    private $url = '';

    /**
     * Constructor
     * 
     * @param string $url            the url for the request
     * @param string $method         = 'GET' the request method
     * @param array  $data           = [] the request data
     * @param bool   $SSLon          = false if the request must go through ssl
     * @param array  $optionsOveride = Null override the request option settings
     */
    public function __construct(
        string $url,
        string $method = 'GET',
        array $data = [],
        bool $SSLon = false,
        array $optionsOveride = null
    ) {

        $this->url = $url;

        if (is_null($optionsOveride)) {
            $this->options['http']['method'] = $method;
            $this->options['http']['content'] = http_build_query($data);
        } else {
            $this->options = $optionsOveride;
        }

        if ($SSLon) {
            unset($this->options['ssl']);
        }
    }

    /**
     * Send the request
     * 
     * @return object service response
     * @author Cein
     */
    public function send() {
        $context = stream_context_create($this->options);
        return json_decode(file_get_contents($this->url, false, $context));
    }
}