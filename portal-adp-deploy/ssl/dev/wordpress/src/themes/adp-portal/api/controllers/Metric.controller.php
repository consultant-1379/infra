<?php
/**
 * Metrics for prometheus control
 * 
 * All metrics used for prometheus
 *
 * PHP version 7.1
 *
 * @category Metrics
 * @package  ADP_Portal_API
 * @author   John Dolan <john.dolan@ammeon.com>
 */
namespace api\Controllers;

/**
 * Metrics for prometheus control
 *
 * @category Metrics
 * @package  ADP_Portal_API
 * @author   John Dolan <john.dolan@ammeon.com>
 */
class MetricController {

    /**
     * Constructor
     */
    public function __construct() {
    }
    
    /**
     * Function to create the output string for the metrics endpoint.
     * 
     * @return string Metrics output required by prometheus
     * 
     * @author John
     */
    public static function getWordpressMetrics(): string{
        $result="";
        $result.="# HELP wp_users_total Total number of users.\n";
        $result.="# TYPE wp_users_total counter\n";
        $result.="wp_users_total 4\n";
        $posts=wp_count_posts();
        $n_posts_pub=$posts->publish;
        $n_posts_dra=$posts->draft;
        $n_pages=wp_count_posts('page');
        $result.='wp_posts_total{status="published"} '.$n_posts_pub."\n";
        $result.="# HELP wp_posts_draft_total Total number of posts published.\n";
        $result.="# TYPE wp_posts_draft_total counter\n";
        $result.='wp_posts_total{status="draft"} '.$n_posts_dra."\n";
        $result.="# HELP wp_pages_total Total number of posts published.\n";
        $result.="# TYPE wp_pages_total counter\n";
        $result.='wp_pages_total{status="published"} '.$n_pages->publish."\n";
        $result.='wp_pages_total{status="draft"} '.$n_pages->draft."\n";
        return $result;
    }

}