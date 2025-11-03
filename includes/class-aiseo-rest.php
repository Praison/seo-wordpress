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
