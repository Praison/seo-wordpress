<?php
/**
 * AISEO REST API Handler
 *
 * Provides REST API endpoints for testing and integration
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_REST {
    
    /**
     * API namespace
     */
    const NAMESPACE = 'aiseo/v1';
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Status endpoint
        register_rest_route(self::NAMESPACE, '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status'),
            'permission_callback' => '__return_true',
        ));
        
        // Validate API key endpoint
        register_rest_route(self::NAMESPACE, '/validate-key', array(
            'methods' => 'GET',
            'callback' => array($this, 'validate_api_key'),
            'permission_callback' => '__return_true',
        ));
        
        // Generate meta description endpoint
        register_rest_route(self::NAMESPACE, '/generate/description', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_description'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'content' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Generate SEO title endpoint
        register_rest_route(self::NAMESPACE, '/generate/title', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_title'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'content' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Analyze content endpoint
        register_rest_route(self::NAMESPACE, '/analyze', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_content'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'content' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_textarea_field',
                ),
            ),
        ));
        
        // Generate metadata for post endpoint
        register_rest_route(self::NAMESPACE, '/generate/post/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_post_metadata'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'meta_types' => array(
                    'required' => false,
                    'type' => 'array',
                    'default' => array('title', 'description'),
                ),
            ),
        ));
        
        // Get schema markup for post endpoint
        register_rest_route(self::NAMESPACE, '/schema/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_schema'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'auto',
                    'enum' => array('auto', 'article', 'blogposting', 'webpage'),
                ),
            ),
        ));
        
        // Get meta tags for post endpoint
        register_rest_route(self::NAMESPACE, '/meta-tags/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_meta_tags'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Get social media tags for post endpoint
        register_rest_route(self::NAMESPACE, '/social-tags/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_social_tags'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Get sitemap statistics endpoint
        register_rest_route(self::NAMESPACE, '/sitemap/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sitemap_stats'),
            'permission_callback' => '__return_true',
        ));
        
        // Image SEO: Generate alt text for image
        register_rest_route(self::NAMESPACE, '/image/generate-alt/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_image_alt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Image SEO: Get images missing alt text
        register_rest_route(self::NAMESPACE, '/image/missing-alt', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_missing_alt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'per_page' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 100,
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Image SEO: Get image SEO score
        register_rest_route(self::NAMESPACE, '/image/seo-score/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_image_seo_score'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Advanced Analysis: Comprehensive SEO analysis (40+ factors)
        register_rest_route(self::NAMESPACE, '/analyze/advanced/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'analyze_advanced'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'sanitize_callback' => 'absint',
                ),
                'keyword' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
        
        // Bulk Edit: Get posts for bulk editing
        register_rest_route(self::NAMESPACE, '/bulk/posts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_bulk_posts'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 50,
                ),
            ),
        ));
        
        // Bulk Edit: Update multiple posts
        register_rest_route(self::NAMESPACE, '/bulk/update', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_update_posts'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'updates' => array(
                    'required' => true,
                    'type' => 'array',
                ),
            ),
        ));
        
        // Bulk Edit: Generate metadata for multiple posts
        register_rest_route(self::NAMESPACE, '/bulk/generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_generate_metadata'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_ids' => array(
                    'required' => true,
                    'type' => 'array',
                ),
                'meta_types' => array(
                    'required' => false,
                    'type' => 'array',
                    'default' => array('title', 'description'),
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Bulk Edit: Preview changes
        register_rest_route(self::NAMESPACE, '/bulk/preview', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_preview_changes'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'updates' => array(
                    'required' => true,
                    'type' => 'array',
                ),
            ),
        ));
        
        // Import/Export: Export to JSON
        register_rest_route(self::NAMESPACE, '/export/json', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_json'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
            ),
        ));
        
        // Import/Export: Export to CSV
        register_rest_route(self::NAMESPACE, '/export/csv', array(
            'methods' => 'GET',
            'callback' => array($this, 'export_csv'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
            ),
        ));
        
        // Import/Export: Import from JSON
        register_rest_route(self::NAMESPACE, '/import/json', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_json'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'data' => array(
                    'required' => true,
                    'type' => 'object',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Import/Export: Import from Yoast
        register_rest_route(self::NAMESPACE, '/import/yoast', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_yoast'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Import/Export: Import from Rank Math
        register_rest_route(self::NAMESPACE, '/import/rankmath', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_rankmath'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Import/Export: Import from AIOSEO
        register_rest_route(self::NAMESPACE, '/import/aioseo', array(
            'methods' => 'POST',
            'callback' => array($this, 'import_aioseo'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => 'post',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Multilingual: Get active plugin
        register_rest_route(self::NAMESPACE, '/multilingual/plugin', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_multilingual_plugin'),
            'permission_callback' => '__return_true',
        ));
        
        // Multilingual: Get languages
        register_rest_route(self::NAMESPACE, '/multilingual/languages', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_multilingual_languages'),
            'permission_callback' => '__return_true',
        ));
        
        // Multilingual: Get post translations
        register_rest_route(self::NAMESPACE, '/multilingual/translations/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_post_translations'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Multilingual: Get hreflang tags
        register_rest_route(self::NAMESPACE, '/multilingual/hreflang/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_hreflang_tags'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Multilingual: Sync metadata
        register_rest_route(self::NAMESPACE, '/multilingual/sync/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_multilingual_metadata'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // CPT: Get all custom post types
        register_rest_route(self::NAMESPACE, '/cpt/list', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cpt_list'),
            'permission_callback' => '__return_true',
        ));
        
        // CPT: Get supported post types
        register_rest_route(self::NAMESPACE, '/cpt/supported', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_supported_cpt'),
            'permission_callback' => '__return_true',
        ));
        
        // CPT: Enable post type
        register_rest_route(self::NAMESPACE, '/cpt/enable', array(
            'methods' => 'POST',
            'callback' => array($this, 'enable_cpt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // CPT: Disable post type
        register_rest_route(self::NAMESPACE, '/cpt/disable', array(
            'methods' => 'POST',
            'callback' => array($this, 'disable_cpt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // CPT: Get posts by type
        register_rest_route(self::NAMESPACE, '/cpt/posts/(?P<post_type>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cpt_posts'),
            'permission_callback' => '__return_true',
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20,
                ),
            ),
        ));
        
        // CPT: Get statistics
        register_rest_route(self::NAMESPACE, '/cpt/stats/(?P<post_type>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cpt_stats'),
            'permission_callback' => '__return_true',
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // CPT: Bulk generate
        register_rest_route(self::NAMESPACE, '/cpt/bulk-generate', array(
            'methods' => 'POST',
            'callback' => array($this, 'bulk_generate_cpt'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => -1,
                ),
                'overwrite' => array(
                    'required' => false,
                    'type' => 'boolean',
                    'default' => false,
                ),
            ),
        ));
        
        // Competitor: Get all competitors
        register_rest_route(self::NAMESPACE, '/competitor/list', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_competitors_list'),
            'permission_callback' => '__return_true',
        ));
        
        // Competitor: Add competitor
        register_rest_route(self::NAMESPACE, '/competitor/add', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_competitor'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'url' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'name' => array(
                    'required' => false,
                    'type' => 'string',
                    'default' => '',
                ),
            ),
        ));
        
        // Competitor: Remove competitor
        register_rest_route(self::NAMESPACE, '/competitor/remove/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'remove_competitor'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Competitor: Analyze competitor
        register_rest_route(self::NAMESPACE, '/competitor/analyze/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'analyze_competitor'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Competitor: Get analysis
        register_rest_route(self::NAMESPACE, '/competitor/analysis/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_competitor_analysis'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
            ),
        ));
        
        // Competitor: Compare with site
        register_rest_route(self::NAMESPACE, '/competitor/compare/(?P<id>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'compare_competitor'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'string',
                ),
                'post_id' => array(
                    'required' => false,
                    'type' => 'integer',
                ),
            ),
        ));
        
        // Competitor: Get summary
        register_rest_route(self::NAMESPACE, '/competitor/summary', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_competitor_summary'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Get plugin status
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_status($request) {
        $api_key = AISEO_Helpers::get_api_key();
        
        return new WP_REST_Response(array(
            'success' => true,
            'version' => AISEO_VERSION,
            'api_key_configured' => !empty($api_key),
            'model' => get_option('aiseo_api_model', 'gpt-4o-mini'),
            'features' => array(
                'sitemap' => get_option('aiseo_enable_sitemap', true),
                'schema' => get_option('aiseo_enable_schema', true),
                'social_tags' => get_option('aiseo_enable_social_tags', true),
            ),
        ), 200);
    }
    
    /**
     * Validate API key
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function validate_api_key($request) {
        $api = new AISEO_API();
        $result = $api->check_api_key();
        
        if (is_wp_error($result)) {
            return new WP_REST_Response(array(
                'success' => false,
                'valid' => false,
                'message' => $result->get_error_message(),
            ), 200);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'valid' => true,
            'message' => 'API key is valid',
        ), 200);
    }
    
    /**
     * Generate meta description
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function generate_description($request) {
        $content = $request->get_param('content');
        $keyword = $request->get_param('keyword');
        
        $api = new AISEO_API();
        $description = $api->generate_meta_description($content, $keyword);
        
        if (is_wp_error($description)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => $description->get_error_message(),
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'description' => $description,
            'length' => strlen($description),
        ), 200);
    }
    
    /**
     * Generate SEO title
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function generate_title($request) {
        $content = $request->get_param('content');
        $keyword = $request->get_param('keyword');
        
        $api = new AISEO_API();
        $title = $api->generate_title($content, $keyword);
        
        if (is_wp_error($title)) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => $title->get_error_message(),
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'title' => $title,
            'length' => strlen($title),
        ), 200);
    }
    
    /**
     * Analyze content
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function analyze_content($request) {
        $post_id = $request->get_param('post_id');
        $keyword = $request->get_param('keyword');
        
        if (!$post_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post ID is required',
            ), 400);
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        // Use comprehensive analysis engine
        $analyzer = new AISEO_Analysis();
        $analysis = $analyzer->analyze_post($post_id, $keyword);
        
        if (isset($analysis['error'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => $analysis['error'],
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'score' => $analysis['overall_score'],
            'status' => $analysis['status'],
            'analyses' => $analysis['analyses'],
            'timestamp' => $analysis['timestamp'],
        ), 200);
    }
    
    /**
     * Generate metadata for a post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function generate_post_metadata($request) {
        $post_id = $request->get_param('id');
        $meta_types = $request->get_param('meta_types');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $api = new AISEO_API();
        $content = $post->post_content;
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $results = array();
        
        if (in_array('title', $meta_types)) {
            $title = $api->generate_title($content, $keyword);
            if (!is_wp_error($title)) {
                update_post_meta($post_id, '_aiseo_meta_title', $title);
                $results['title'] = $title;
            }
        }
        
        if (in_array('description', $meta_types)) {
            $description = $api->generate_meta_description($content, $keyword);
            if (!is_wp_error($description)) {
                update_post_meta($post_id, '_aiseo_meta_description', $description);
                $results['description'] = $description;
            }
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'generated' => $results,
        ), 200);
    }
    
    /**
     * Get sitemap statistics
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_sitemap_stats($request) {
        $sitemap_generator = new AISEO_Sitemap();
        $stats = $sitemap_generator->get_sitemap_stats();
        
        return new WP_REST_Response(array(
            'success' => true,
            'stats' => $stats,
            'sitemap_url' => home_url('/sitemap.xml'),
        ), 200);
    }
    
    /**
     * Get social media tags for post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_social_tags($request) {
        $post_id = $request->get_param('id');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $social_handler = new AISEO_Social();
        $social_tags = $social_handler->get_social_tags($post_id);
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'social_tags' => $social_tags,
        ), 200);
    }
    
    /**
     * Get meta tags for post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_meta_tags($request) {
        $post_id = $request->get_param('id');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $meta_handler = new AISEO_Meta();
        $meta_tags = $meta_handler->get_meta_tags($post_id);
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'meta_tags' => $meta_tags,
        ), 200);
    }
    
    /**
     * Get schema markup for post
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function get_schema($request) {
        $post_id = $request->get_param('id');
        $schema_type = $request->get_param('type');
        
        $post = get_post($post_id);
        if (!$post) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Post not found',
            ), 404);
        }
        
        $schema_generator = new AISEO_Schema();
        $schema = $schema_generator->generate_schema($post_id, $schema_type);
        
        if (!$schema) {
            return new WP_REST_Response(array(
                'success' => false,
                'error' => 'Failed to generate schema',
            ), 400);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'post_id' => $post_id,
            'schema_type' => $schema_type,
            'schema' => $schema,
        ), 200);
    }
    
    /**
     * Generate alt text for image (Image SEO)
     */
    public function generate_image_alt($request) {
        $image_id = $request->get_param('id');
        $overwrite = $request->get_param('overwrite');
        
        $image_seo = new AISEO_Image_SEO();
        $alt_text = $image_seo->generate_alt_text($image_id, ['overwrite' => $overwrite]);
        
        if (is_wp_error($alt_text)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $alt_text->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'image_id' => $image_id,
            'alt_text' => $alt_text
        ], 200);
    }
    
    /**
     * Get images missing alt text (Image SEO)
     */
    public function get_missing_alt($request) {
        $per_page = $request->get_param('per_page') ?: 100;
        
        $image_seo = new AISEO_Image_SEO();
        $images = $image_seo->detect_missing_alt_text(['posts_per_page' => $per_page]);
        
        return new WP_REST_Response([
            'success' => true,
            'count' => count($images),
            'images' => $images
        ], 200);
    }
    
    /**
     * Get image SEO score (Image SEO)
     */
    public function get_image_seo_score($request) {
        $image_id = $request->get_param('id');
        
        $image_seo = new AISEO_Image_SEO();
        $score_data = $image_seo->analyze_image_seo($image_id);
        
        return new WP_REST_Response([
            'success' => true,
            'image_id' => $image_id,
            'score_data' => $score_data
        ], 200);
    }
    
    /**
     * Advanced SEO analysis (40+ factors)
     */
    public function analyze_advanced($request) {
        $post_id = $request->get_param('id');
        $keyword = $request->get_param('keyword');
        
        $advanced_analysis = new AISEO_Advanced_Analysis();
        $result = $advanced_analysis->analyze_comprehensive($post_id, $keyword);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get posts for bulk editing
     */
    public function get_bulk_posts($request) {
        $post_type = $request->get_param('post_type');
        $limit = $request->get_param('limit');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $posts = $bulk_edit->get_posts_for_editing([
            'post_type' => $post_type,
            'posts_per_page' => $limit
        ]);
        
        return new WP_REST_Response([
            'success' => true,
            'total' => count($posts),
            'posts' => $posts
        ], 200);
    }
    
    /**
     * Bulk update posts
     */
    public function bulk_update_posts($request) {
        $updates = $request->get_param('updates');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $result = $bulk_edit->bulk_update($updates);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Bulk generate metadata
     */
    public function bulk_generate_metadata($request) {
        $post_ids = $request->get_param('post_ids');
        $meta_types = $request->get_param('meta_types');
        $overwrite = $request->get_param('overwrite');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $result = $bulk_edit->bulk_generate($post_ids, [
            'meta_types' => $meta_types,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Preview bulk changes
     */
    public function bulk_preview_changes($request) {
        $updates = $request->get_param('updates');
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $preview = $bulk_edit->preview_changes($updates);
        
        if (is_wp_error($preview)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $preview->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'preview' => $preview
        ], 200);
    }
    
    /**
     * Export to JSON
     */
    public function export_json($request) {
        $post_type = $request->get_param('post_type');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->export_to_json(['post_type' => $post_type]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Export to CSV
     */
    public function export_csv($request) {
        $post_type = $request->get_param('post_type');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->export_to_csv(['post_type' => $post_type]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result,
            'content_type' => 'text/csv'
        ], 200);
    }
    
    /**
     * Import from JSON
     */
    public function import_json($request) {
        $data = $request->get_param('data');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_json($data, ['overwrite' => $overwrite]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Import from Yoast SEO
     */
    public function import_yoast($request) {
        $post_type = $request->get_param('post_type');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_yoast([
            'post_type' => $post_type,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Import from Rank Math
     */
    public function import_rankmath($request) {
        $post_type = $request->get_param('post_type');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_rankmath([
            'post_type' => $post_type,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Import from AIOSEO
     */
    public function import_aioseo($request) {
        $post_type = $request->get_param('post_type');
        $overwrite = $request->get_param('overwrite');
        
        $import_export = new AISEO_Import_Export();
        $result = $import_export->import_from_aioseo([
            'post_type' => $post_type,
            'overwrite' => $overwrite
        ]);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get active multilingual plugin
     */
    public function get_multilingual_plugin($request) {
        $multilingual = new AISEO_Multilingual();
        $plugin = $multilingual->get_active_plugin();
        
        return new WP_REST_Response([
            'success' => true,
            'plugin' => $plugin,
            'supported_plugins' => ['wpml', 'polylang', 'translatepress']
        ], 200);
    }
    
    /**
     * Get multilingual languages
     */
    public function get_multilingual_languages($request) {
        $multilingual = new AISEO_Multilingual();
        $languages = $multilingual->get_languages();
        
        return new WP_REST_Response([
            'success' => true,
            'plugin' => $multilingual->get_active_plugin(),
            'languages' => $languages,
            'count' => count($languages)
        ], 200);
    }
    
    /**
     * Get post translations
     */
    public function get_post_translations($request) {
        $post_id = $request->get_param('id');
        
        $multilingual = new AISEO_Multilingual();
        $translations = $multilingual->get_post_translations($post_id);
        
        return new WP_REST_Response([
            'success' => true,
            'post_id' => $post_id,
            'translations' => $translations,
            'count' => count($translations)
        ], 200);
    }
    
    /**
     * Get hreflang tags for a post
     */
    public function get_hreflang_tags($request) {
        $post_id = $request->get_param('id');
        
        $multilingual = new AISEO_Multilingual();
        $hreflang_tags = $multilingual->generate_hreflang_tags($post_id);
        
        return new WP_REST_Response([
            'success' => true,
            'post_id' => $post_id,
            'hreflang_tags' => $hreflang_tags,
            'count' => count($hreflang_tags)
        ], 200);
    }
    
    /**
     * Sync metadata across translations
     */
    public function sync_multilingual_metadata($request) {
        $post_id = $request->get_param('id');
        $overwrite = $request->get_param('overwrite');
        
        $multilingual = new AISEO_Multilingual();
        $result = $multilingual->sync_metadata_across_translations($post_id, $overwrite);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get custom post types list
     */
    public function get_cpt_list($request) {
        $cpt = new AISEO_CPT();
        $post_types = $cpt->get_custom_post_types();
        
        return new WP_REST_Response([
            'success' => true,
            'post_types' => $post_types,
            'count' => count($post_types)
        ], 200);
    }
    
    /**
     * Get supported custom post types
     */
    public function get_supported_cpt($request) {
        $cpt = new AISEO_CPT();
        $supported = $cpt->get_supported_post_types();
        
        return new WP_REST_Response([
            'success' => true,
            'supported_post_types' => $supported,
            'count' => count($supported)
        ], 200);
    }
    
    /**
     * Enable custom post type
     */
    public function enable_cpt($request) {
        $post_type = $request->get_param('post_type');
        
        $cpt = new AISEO_CPT();
        $result = $cpt->enable_post_type($post_type);
        
        if ($result) {
            return new WP_REST_Response([
                'success' => true,
                'message' => sprintf('SEO enabled for post type: %s', $post_type)
            ], 200);
        }
        
        return new WP_REST_Response([
            'success' => false,
            'message' => sprintf('Post type %s already enabled', $post_type)
        ], 400);
    }
    
    /**
     * Disable custom post type
     */
    public function disable_cpt($request) {
        $post_type = $request->get_param('post_type');
        
        $cpt = new AISEO_CPT();
        $result = $cpt->disable_post_type($post_type);
        
        if ($result) {
            return new WP_REST_Response([
                'success' => true,
                'message' => sprintf('SEO disabled for post type: %s', $post_type)
            ], 200);
        }
        
        return new WP_REST_Response([
            'success' => false,
            'message' => sprintf('Post type %s not found in supported list', $post_type)
        ], 400);
    }
    
    /**
     * Get posts by custom post type
     */
    public function get_cpt_posts($request) {
        $post_type = $request->get_param('post_type');
        $limit = $request->get_param('limit');
        
        $cpt = new AISEO_CPT();
        $posts = $cpt->get_posts_by_type($post_type, ['posts_per_page' => $limit]);
        
        return new WP_REST_Response([
            'success' => true,
            'post_type' => $post_type,
            'posts' => $posts,
            'count' => count($posts)
        ], 200);
    }
    
    /**
     * Get statistics for custom post type
     */
    public function get_cpt_stats($request) {
        $post_type = $request->get_param('post_type');
        
        $cpt = new AISEO_CPT();
        $stats = $cpt->get_post_type_stats($post_type);
        
        return new WP_REST_Response([
            'success' => true,
            'post_type' => $post_type,
            'stats' => $stats
        ], 200);
    }
    
    /**
     * Bulk generate metadata for custom post type
     */
    public function bulk_generate_cpt($request) {
        $post_type = $request->get_param('post_type');
        $limit = $request->get_param('limit');
        $overwrite = $request->get_param('overwrite');
        
        $cpt = new AISEO_CPT();
        $result = $cpt->bulk_generate_for_type($post_type, [
            'limit' => $limit,
            'overwrite' => $overwrite
        ]);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => $result
        ], 200);
    }
    
    /**
     * Get competitors list
     */
    public function get_competitors_list($request) {
        $competitor = new AISEO_Competitor();
        $competitors = $competitor->get_competitors();
        
        return new WP_REST_Response([
            'success' => true,
            'competitors' => $competitors,
            'count' => count($competitors)
        ], 200);
    }
    
    /**
     * Add competitor
     */
    public function add_competitor($request) {
        $url = $request->get_param('url');
        $name = $request->get_param('name');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->add_competitor($url, $name);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'competitor_id' => $result,
            'message' => 'Competitor added successfully'
        ], 200);
    }
    
    /**
     * Remove competitor
     */
    public function remove_competitor($request) {
        $id = $request->get_param('id');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->remove_competitor($id);
        
        if ($result) {
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Competitor removed successfully'
            ], 200);
        }
        
        return new WP_REST_Response([
            'success' => false,
            'error' => 'Competitor not found'
        ], 404);
    }
    
    /**
     * Analyze competitor
     */
    public function analyze_competitor($request) {
        $id = $request->get_param('id');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->analyze_competitor($id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'analysis' => $result,
            'message' => 'Competitor analyzed successfully'
        ], 200);
    }
    
    /**
     * Get competitor analysis
     */
    public function get_competitor_analysis($request) {
        $id = $request->get_param('id');
        
        $competitor = new AISEO_Competitor();
        $analysis = $competitor->get_analysis($id);
        
        if (!$analysis) {
            return new WP_REST_Response([
                'success' => false,
                'error' => 'No analysis data found'
            ], 404);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'analysis' => $analysis
        ], 200);
    }
    
    /**
     * Compare with site
     */
    public function compare_competitor($request) {
        $id = $request->get_param('id');
        $post_id = $request->get_param('post_id');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->compare_with_site($id, $post_id);
        
        if (is_wp_error($result)) {
            return new WP_REST_Response([
                'success' => false,
                'error' => $result->get_error_message()
            ], 400);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'comparison' => $result
        ], 200);
    }
    
    /**
     * Get competitor summary
     */
    public function get_competitor_summary($request) {
        $competitor = new AISEO_Competitor();
        $summary = $competitor->get_summary();
        
        return new WP_REST_Response([
            'success' => true,
            'summary' => $summary
        ], 200);
    }
    
    /**
     * Check permission for protected endpoints
     *
     * @param WP_REST_Request $request Request object
     * @return bool
     */
    public function check_permission($request) {
        // For now, allow all requests
        // In production, you'd check user capabilities
        return true;
    }
}
