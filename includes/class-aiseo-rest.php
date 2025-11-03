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
