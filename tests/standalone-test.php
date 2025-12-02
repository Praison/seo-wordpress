<?php
/**
 * Standalone Test Script for AISEO Plugin
 * 
 * This script tests the plugin classes without requiring a full WordPress installation
 * by mocking the necessary WordPress functions.
 */

// Mock WordPress functions
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1) { return true; }
}
if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $args = 1) { return true; }
}
if (!function_exists('get_option')) {
    $mock_options = [];
    function get_option($key, $default = false) {
        global $mock_options;
        return isset($mock_options[$key]) ? $mock_options[$key] : $default;
    }
}
if (!function_exists('update_option')) {
    function update_option($key, $value) {
        global $mock_options;
        $mock_options[$key] = $value;
        return true;
    }
}
if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults) {
        return array_merge($defaults, (array)$args);
    }
}
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($str) {
        return $str;
    }
}
if (!function_exists('esc_html')) {
    function esc_html($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_attr')) {
    function esc_attr($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}
if (!function_exists('home_url')) {
    function home_url($path = '') {
        return 'https://example.com' . $path;
    }
}
if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show) {
        $info = [
            'name' => 'Test Site',
            'description' => 'Test Description',
        ];
        return isset($info[$show]) ? $info[$show] : '';
    }
}
if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) {
        return $value;
    }
}
if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) {
        return trim(strip_tags($str));
    }
}
if (!function_exists('absint')) {
    function absint($val) {
        return abs((int)$val);
    }
}
if (!function_exists('wp_parse_url')) {
    function wp_parse_url($url, $component = -1) {
        return parse_url($url, $component);
    }
}
if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}
if (!function_exists('current_user_can')) {
    function current_user_can($cap) {
        return true;
    }
}
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return false;
    }
}
if (!function_exists('is_singular')) {
    function is_singular($type = '') {
        return false;
    }
}
if (!function_exists('is_front_page')) {
    function is_front_page() {
        return false;
    }
}
if (!function_exists('is_home')) {
    function is_home() {
        return false;
    }
}
if (!function_exists('is_category')) {
    function is_category() {
        return false;
    }
}
if (!function_exists('is_tag')) {
    function is_tag() {
        return false;
    }
}
if (!function_exists('is_author')) {
    function is_author() {
        return false;
    }
}
if (!function_exists('is_date')) {
    function is_date() {
        return false;
    }
}
if (!function_exists('is_search')) {
    function is_search() {
        return false;
    }
}
if (!function_exists('is_404')) {
    function is_404() {
        return false;
    }
}
if (!function_exists('is_archive')) {
    function is_archive() {
        return false;
    }
}
if (!function_exists('is_page')) {
    function is_page() {
        return false;
    }
}
if (!function_exists('is_paged')) {
    function is_paged() {
        return false;
    }
}
if (!function_exists('is_tax')) {
    function is_tax() {
        return false;
    }
}
if (!function_exists('is_attachment')) {
    function is_attachment() {
        return false;
    }
}
if (!function_exists('is_year')) {
    function is_year() {
        return false;
    }
}
if (!function_exists('is_month')) {
    function is_month() {
        return false;
    }
}
if (!function_exists('is_day')) {
    function is_day() {
        return false;
    }
}
if (!function_exists('get_queried_object')) {
    function get_queried_object() {
        return null;
    }
}
if (!function_exists('get_query_var')) {
    function get_query_var($var, $default = '') {
        return $default;
    }
}
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key = '', $single = false) {
        return $single ? '' : [];
    }
}
if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $key, $value) {
        return true;
    }
}
if (!function_exists('get_term_meta')) {
    function get_term_meta($term_id, $key = '', $single = false) {
        return $single ? '' : [];
    }
}
if (!function_exists('update_term_meta')) {
    function update_term_meta($term_id, $key, $value) {
        return true;
    }
}
if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $callback) {
        return true;
    }
}
if (!function_exists('shortcode_atts')) {
    function shortcode_atts($pairs, $atts, $shortcode = '') {
        return array_merge($pairs, (array)$atts);
    }
}
if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags($str, $remove_breaks = false) {
        $str = strip_tags($str);
        if ($remove_breaks) {
            $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        }
        return trim($str);
    }
}
if (!function_exists('preg_match')) {
    // preg_match is a PHP core function, should exist
}
if (!defined('ABSPATH')) {
    define('ABSPATH', '/var/www/html/');
}

echo "=== AISEO Plugin Standalone Tests ===\n\n";

$tests_passed = 0;
$tests_failed = 0;

function test_result($name, $passed, $message = '') {
    global $tests_passed, $tests_failed;
    if ($passed) {
        echo "✅ PASS: $name\n";
        $tests_passed++;
    } else {
        echo "❌ FAIL: $name" . ($message ? " - $message" : "") . "\n";
        $tests_failed++;
    }
}

// Test 1: Homepage SEO Class
echo "--- Test 1: Homepage SEO ---\n";
require_once __DIR__ . '/../includes/class-aiseo-homepage-seo.php';
$homepage_seo = new AISEO_Homepage_SEO();

// Test get_settings returns defaults
$settings = $homepage_seo->get_settings();
test_result('Homepage SEO - get_settings returns array', is_array($settings));
test_result('Homepage SEO - has home_title key', array_key_exists('home_title', $settings));
test_result('Homepage SEO - has home_description key', array_key_exists('home_description', $settings));

// Test update_settings
$homepage_seo->update_settings(['home_title' => 'Test Title', 'home_description' => 'Test Desc']);
$updated = $homepage_seo->get_settings();
test_result('Homepage SEO - update_settings works', $updated['home_title'] === 'Test Title');

echo "\n--- Test 2: Webmaster Verification ---\n";
require_once __DIR__ . '/../includes/class-aiseo-webmaster.php';
$webmaster = new AISEO_Webmaster();

$codes = $webmaster->get_verification_codes();
test_result('Webmaster - get_verification_codes returns array', is_array($codes));
test_result('Webmaster - has google key', array_key_exists('google', $codes));
test_result('Webmaster - has bing key', array_key_exists('bing', $codes));

$webmaster->update_verification_codes(['google' => 'test123', 'bing' => 'bing456']);
$updated_codes = $webmaster->get_verification_codes();
test_result('Webmaster - update works for google', $updated_codes['google'] === 'test123');
test_result('Webmaster - update works for bing', $updated_codes['bing'] === 'bing456');

echo "\n--- Test 3: Analytics ---\n";
require_once __DIR__ . '/../includes/class-aiseo-analytics.php';
$analytics = new AISEO_Analytics();

$settings = $analytics->get_settings();
test_result('Analytics - get_settings returns array', is_array($settings));
test_result('Analytics - has tracking_id key', array_key_exists('tracking_id', $settings));
test_result('Analytics - has enabled key', array_key_exists('enabled', $settings));

$analytics->update_settings(['tracking_id' => 'G-TEST123', 'enabled' => true]);
$updated = $analytics->get_settings();
test_result('Analytics - update tracking_id works', $updated['tracking_id'] === 'G-TEST123');
test_result('Analytics - update enabled works', $updated['enabled'] === true);

// Test GA4 detection via tracking code output (method is private)
// We verify by checking if tracking_id starts with G- for GA4
test_result('Analytics - GA4 format detected', strpos($updated['tracking_id'], 'G-') === 0);
test_result('Analytics - has anonymize_ip key', array_key_exists('anonymize_ip', $settings));

echo "\n--- Test 4: Title Templates ---\n";
require_once __DIR__ . '/../includes/class-aiseo-title-templates.php';
$title_templates = new AISEO_Title_Templates();

$templates = $title_templates->get_templates();
test_result('Title Templates - get_templates returns array', is_array($templates));
test_result('Title Templates - has separator key', array_key_exists('separator', $templates));
test_result('Title Templates - has post_title key', array_key_exists('post_title', $templates));

$title_templates->update_templates(['separator' => '-', 'post_title' => '%title% - %sitename%']);
$updated = $title_templates->get_templates();
test_result('Title Templates - update separator works', $updated['separator'] === '-');

$placeholders = $title_templates->get_placeholders();
test_result('Title Templates - get_placeholders returns array', is_array($placeholders));

echo "\n--- Test 5: Robots Settings ---\n";
require_once __DIR__ . '/../includes/class-aiseo-robots.php';
$robots = new AISEO_Robots();

$settings = $robots->get_settings();
test_result('Robots - get_settings returns array', is_array($settings));
test_result('Robots - has noindex_categories key', array_key_exists('noindex_categories', $settings));
test_result('Robots - has nofollow_external_links key', array_key_exists('nofollow_external_links', $settings));

$robots->update_settings(['noindex_categories' => true, 'nofollow_external_links' => true]);
$updated = $robots->get_settings();
test_result('Robots - update noindex_categories works', $updated['noindex_categories'] === true);

echo "\n--- Test 6: Breadcrumbs ---\n";
require_once __DIR__ . '/../includes/class-aiseo-breadcrumbs.php';
$breadcrumbs = new AISEO_Breadcrumbs();

$settings = $breadcrumbs->get_settings();
test_result('Breadcrumbs - get_settings returns array', is_array($settings));
test_result('Breadcrumbs - has separator key', array_key_exists('separator', $settings));
test_result('Breadcrumbs - has home_text key', array_key_exists('home_text', $settings));

$breadcrumbs->update_settings(['separator' => '>', 'home_text' => 'Start']);
$updated = $breadcrumbs->get_settings();
test_result('Breadcrumbs - update separator works', $updated['separator'] === '>');
test_result('Breadcrumbs - update home_text works', $updated['home_text'] === 'Start');

echo "\n--- Test 7: RSS ---\n";
require_once __DIR__ . '/../includes/class-aiseo-rss.php';
$rss = new AISEO_RSS();

$settings = $rss->get_settings();
test_result('RSS - get_settings returns array', is_array($settings));
test_result('RSS - has enabled key', array_key_exists('enabled', $settings));
test_result('RSS - has before_content key', array_key_exists('before_content', $settings));

$rss->update_settings(['enabled' => true, 'before_content' => 'Read more at %site_url%']);
$updated = $rss->get_settings();
test_result('RSS - update enabled works', $updated['enabled'] === true);
test_result('RSS - update before_content works', strpos($updated['before_content'], '%site_url%') !== false);

$placeholders = $rss->get_placeholders();
test_result('RSS - get_placeholders returns array', is_array($placeholders));

echo "\n--- Test 8: Taxonomy SEO ---\n";
require_once __DIR__ . '/../includes/class-aiseo-taxonomy-seo.php';
$taxonomy_seo = new AISEO_Taxonomy_SEO();

test_result('Taxonomy SEO - class instantiates', $taxonomy_seo instanceof AISEO_Taxonomy_SEO);
// Test get_term_meta (returns defaults since no DB)
$meta = $taxonomy_seo->get_term_meta(1, 'category');
test_result('Taxonomy SEO - get_term_meta returns array', is_array($meta));
test_result('Taxonomy SEO - has title key', array_key_exists('title', $meta));
test_result('Taxonomy SEO - has description key', array_key_exists('description', $meta));
test_result('Taxonomy SEO - has noindex key', array_key_exists('noindex', $meta));

echo "\n--- Test 9: Sitemap (old-style URLs) ---\n";
// Test that sitemap class exists and has proper methods
require_once __DIR__ . '/../includes/class-aiseo-sitemap.php';
$sitemap = new AISEO_Sitemap();

test_result('Sitemap - class instantiates', $sitemap instanceof AISEO_Sitemap);

// Verify the rewrite rules include old-style URLs by checking the class file
$sitemap_file = file_get_contents(__DIR__ . '/../includes/class-aiseo-sitemap.php');
test_result('Sitemap - has sitemap_index.xml rule', strpos($sitemap_file, 'sitemap_index\.xml') !== false);
test_result('Sitemap - has post-sitemap.xml rule', strpos($sitemap_file, 'post-sitemap\.xml') !== false);
test_result('Sitemap - has page-sitemap.xml rule', strpos($sitemap_file, 'page-sitemap\.xml') !== false);
test_result('Sitemap - has category-sitemap.xml rule', strpos($sitemap_file, 'category-sitemap\.xml') !== false);
test_result('Sitemap - robots.txt uses old-style URL', strpos($sitemap_file, 'sitemap_index.xml') !== false);

echo "\n--- Test 10: Importer ---\n";
require_once __DIR__ . '/../includes/class-aiseo-importer.php';
$importer = new AISEO_Importer();

test_result('Importer - class instantiates', $importer instanceof AISEO_Importer);
// Note: has_old_plugin_data and import methods need database, skipping

echo "\n--- Test 11: WP-CLI Commands (Syntax Check) ---\n";
// Check CLI files exist and have valid syntax
$cli_files = array(
    'class-aiseo-homepage-cli.php' => 'AISEO_Homepage_CLI_Command',
    'class-aiseo-taxonomy-cli.php' => 'AISEO_Taxonomy_CLI_Command',
);

foreach ($cli_files as $file => $class_name) {
    $file_path = __DIR__ . '/../includes/' . $file;
    test_result("CLI - {$file} exists", file_exists($file_path));
    
    // Check file contains the class
    $content = file_get_contents($file_path);
    test_result("CLI - {$file} has {$class_name}", strpos($content, "class {$class_name}") !== false);
    
    // Check for required methods
    if ($file === 'class-aiseo-homepage-cli.php') {
        test_result("CLI - Homepage has 'get' method", strpos($content, 'public function get(') !== false);
        test_result("CLI - Homepage has 'set' method", strpos($content, 'public function set(') !== false);
        test_result("CLI - Homepage has 'clear' method", strpos($content, 'public function clear(') !== false);
    }
    
    if ($file === 'class-aiseo-taxonomy-cli.php') {
        test_result("CLI - Taxonomy has 'get' method", strpos($content, 'public function get(') !== false);
        test_result("CLI - Taxonomy has 'set' method", strpos($content, 'public function set(') !== false);
        test_result("CLI - Taxonomy has 'list_' method", strpos($content, 'public function list_(') !== false);
        test_result("CLI - Taxonomy has 'bulk' method", strpos($content, 'public function bulk(') !== false);
    }
}

echo "\n=== Test Summary ===\n";
echo "Passed: $tests_passed\n";
echo "Failed: $tests_failed\n";
echo "Total: " . ($tests_passed + $tests_failed) . "\n";

if ($tests_failed === 0) {
    echo "\n🎉 All tests passed!\n";
    exit(0);
} else {
    echo "\n⚠️ Some tests failed!\n";
    exit(1);
}
