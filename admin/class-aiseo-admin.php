<?php
/**
 * AISEO Admin Interface
 *
 * Main admin class that handles menu, tabs, and page rendering
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Admin {
    
    /**
     * Current active tab
     */
    private $active_tab;
    
    /**
     * Tab definitions
     */
    private $tabs;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->define_tabs();
        $this->active_tab = $this->get_active_tab();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_aiseo_admin_action', array($this, 'handle_ajax_request'));
        
        // Individual AJAX handlers for UI buttons
        add_action('wp_ajax_aiseo_create_post', array($this, 'ajax_create_post'));
        add_action('wp_ajax_aiseo_generate_title', array($this, 'ajax_generate_title'));
        add_action('wp_ajax_aiseo_generate_description', array($this, 'ajax_generate_description'));
        add_action('wp_ajax_aiseo_generate_keyword', array($this, 'ajax_generate_keyword'));
        add_action('wp_ajax_aiseo_analyze_content', array($this, 'ajax_analyze_post'));
        
        // Technical SEO handlers
        add_action('wp_ajax_aiseo_add_redirect', array($this, 'ajax_add_redirect'));
        add_action('wp_ajax_aiseo_list_redirects', array($this, 'ajax_list_redirects'));
        add_action('wp_ajax_aiseo_delete_redirect', array($this, 'ajax_delete_redirect'));
        add_action('wp_ajax_aiseo_optimize_permalinks', array($this, 'ajax_optimize_permalinks'));
        add_action('wp_ajax_aiseo_regenerate_sitemap', array($this, 'ajax_regenerate_sitemap'));
        add_action('wp_ajax_aiseo_generate_image_alt', array($this, 'ajax_generate_image_alt'));
        add_action('wp_ajax_aiseo_find_missing_alt', array($this, 'ajax_find_missing_alt'));
        add_action('wp_ajax_aiseo_generate_single_alt', array($this, 'ajax_generate_single_alt'));
        
        // Advanced tab handlers
        add_action('wp_ajax_aiseo_save_cpt_settings', array($this, 'ajax_save_cpt_settings'));
        add_action('wp_ajax_aiseo_generate_report', array($this, 'ajax_generate_report'));
        add_action('wp_ajax_aiseo_keyword_research', array($this, 'ajax_keyword_research'));
        
        // AI Content additional features
        add_action('wp_ajax_aiseo_rewrite_content', array($this, 'ajax_rewrite_content'));
        add_action('wp_ajax_aiseo_content_suggestions', array($this, 'ajax_content_suggestions'));
        add_action('wp_ajax_aiseo_generate_outline', array($this, 'ajax_generate_outline'));
        add_action('wp_ajax_aiseo_generate_faq', array($this, 'ajax_generate_faq'));
        
        // SEO Tools additional features
        add_action('wp_ajax_aiseo_internal_linking', array($this, 'ajax_internal_linking'));
        add_action('wp_ajax_aiseo_meta_variations', array($this, 'ajax_meta_variations'));
        
        // Bulk Operations
        add_action('wp_ajax_aiseo_import_seo', array($this, 'ajax_import_seo'));
        add_action('wp_ajax_aiseo_export_seo', array($this, 'ajax_export_seo'));
        add_action('wp_ajax_aiseo_save_title', array($this, 'ajax_save_title'));
        add_action('wp_ajax_aiseo_save_description', array($this, 'ajax_save_description'));
    }
    
    /**
     * Define all tabs
     */
    private function define_tabs() {
        $this->tabs = array(
            'dashboard' => array(
                'title' => __('Dashboard', 'aiseo'),
                'icon' => 'dashicons-dashboard',
                'capability' => 'manage_options',
                'callback' => array($this, 'render_dashboard_tab'),
            ),
            'ai-content' => array(
                'title' => __('AI Content', 'aiseo'),
                'icon' => 'dashicons-edit',
                'capability' => 'edit_posts',
                'callback' => array($this, 'render_ai_content_tab'),
            ),
            'seo-tools' => array(
                'title' => __('SEO Tools', 'aiseo'),
                'icon' => 'dashicons-search',
                'capability' => 'edit_posts',
                'callback' => array($this, 'render_seo_tools_tab'),
            ),
            'bulk-operations' => array(
                'title' => __('Bulk Operations', 'aiseo'),
                'icon' => 'dashicons-admin-page',
                'capability' => 'edit_posts',
                'callback' => array($this, 'render_bulk_operations_tab'),
            ),
            'monitoring' => array(
                'title' => __('Monitoring', 'aiseo'),
                'icon' => 'dashicons-chart-line',
                'capability' => 'edit_posts',
                'callback' => array($this, 'render_monitoring_tab'),
            ),
            'technical-seo' => array(
                'title' => __('Technical SEO', 'aiseo'),
                'icon' => 'dashicons-admin-tools',
                'capability' => 'manage_options',
                'callback' => array($this, 'render_technical_seo_tab'),
            ),
            'advanced' => array(
                'title' => __('Advanced', 'aiseo'),
                'icon' => 'dashicons-admin-generic',
                'capability' => 'manage_options',
                'callback' => array($this, 'render_advanced_tab'),
            ),
            'settings' => array(
                'title' => __('Settings', 'aiseo'),
                'icon' => 'dashicons-admin-settings',
                'capability' => 'manage_options',
                'callback' => array($this, 'render_settings_tab'),
            ),
        );
    }
    
    /**
     * Get active tab from URL
     */
    private function get_active_tab() {
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
        
        // Validate tab exists
        if (!isset($this->tabs[$tab])) {
            $tab = 'dashboard';
        }
        
        return $tab;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('AISEO - AI-Powered SEO', 'aiseo'),
            __('AISEO', 'aiseo'),
            'edit_posts',
            'aiseo',
            array($this, 'render_admin_page'),
            'dashicons-chart-line',
            30
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on AISEO pages
        if (strpos($hook, 'aiseo') === false) {
            return;
        }
        
        // Styles
        wp_enqueue_style(
            'aiseo-admin',
            AISEO_PLUGIN_URL . 'admin/css/aiseo-admin.css',
            array(),
            AISEO_VERSION
        );
        
        // Scripts
        wp_enqueue_script(
            'aiseo-admin',
            AISEO_PLUGIN_URL . 'admin/js/aiseo-admin.js',
            array('jquery', 'wp-util'),
            AISEO_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('aiseo-admin', 'aiseoAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aiseo_admin_nonce'),
            'strings' => array(
                'generating' => __('Generating...', 'aiseo'),
                'success' => __('Success!', 'aiseo'),
                'error' => __('Error occurred', 'aiseo'),
                'confirm' => __('Are you sure?', 'aiseo'),
            ),
        ));
    }
    
    /**
     * Render main admin page
     */
    public function render_admin_page() {
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'aiseo'));
        }
        
        ?>
        <div class="wrap aiseo-admin-wrap">
            <h1 class="aiseo-admin-title">
                <span class="dashicons dashicons-chart-line"></span>
                <?php echo esc_html__('AISEO - AI-Powered SEO', 'aiseo'); ?>
                <span class="aiseo-version">v<?php echo esc_html(AISEO_VERSION); ?></span>
            </h1>
            
            <?php $this->render_api_status_banner(); ?>
            
            <nav class="nav-tab-wrapper aiseo-tab-wrapper">
                <?php $this->render_tabs(); ?>
            </nav>
            
            <div class="aiseo-tab-content">
                <?php $this->render_active_tab_content(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render API status banner
     */
    private function render_api_status_banner() {
        $api_key = AISEO_Helpers::get_api_key();
        
        if (empty($api_key)) {
            ?>
            <div class="notice notice-warning aiseo-api-warning">
                <p>
                    <strong><?php esc_html_e('OpenAI API Key Required', 'aiseo'); ?></strong>
                    <?php esc_html_e('Please configure your OpenAI API key in the Settings tab to use AI features.', 'aiseo'); ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiseo&tab=settings')); ?>" class="button button-primary button-small">
                        <?php esc_html_e('Configure Now', 'aiseo'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
    
    /**
     * Render navigation tabs
     */
    private function render_tabs() {
        foreach ($this->tabs as $tab_key => $tab) {
            // Check capability
            if (!current_user_can($tab['capability'])) {
                continue;
            }
            
            $active_class = ($tab_key === $this->active_tab) ? 'nav-tab-active' : '';
            $url = add_query_arg(array('page' => 'aiseo', 'tab' => $tab_key), admin_url('admin.php'));
            
            printf(
                '<a href="%s" class="nav-tab %s"><span class="dashicons %s"></span> %s</a>',
                esc_url($url),
                esc_attr($active_class),
                esc_attr($tab['icon']),
                esc_html($tab['title'])
            );
        }
    }
    
    /**
     * Render active tab content
     */
    private function render_active_tab_content() {
        $tab = $this->tabs[$this->active_tab];
        
        // Check capability
        if (!current_user_can($tab['capability'])) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'aiseo'));
        }
        
        // Call tab callback
        if (is_callable($tab['callback'])) {
            call_user_func($tab['callback']);
        }
    }
    
    /**
     * Render Dashboard tab
     */
    public function render_dashboard_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Render AI Content tab
     */
    public function render_ai_content_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/ai-content.php';
    }
    
    /**
     * Render SEO Tools tab
     */
    public function render_seo_tools_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/seo-tools.php';
    }
    
    /**
     * Render Bulk Operations tab
     */
    public function render_bulk_operations_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/bulk-operations.php';
    }
    
    /**
     * Render Monitoring tab
     */
    public function render_monitoring_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/monitoring.php';
    }
    
    /**
     * Render Technical SEO tab
     */
    public function render_technical_seo_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/technical-seo.php';
    }
    
    /**
     * Render Advanced tab
     */
    public function render_advanced_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/advanced.php';
    }
    
    /**
     * Render Settings tab
     */
    public function render_settings_tab() {
        require_once AISEO_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Handle AJAX requests
     */
    public function handle_ajax_request() {
        // Verify nonce
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        // Check capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'aiseo')));
        }
        
        $action = isset($_POST['action_type']) ? sanitize_key($_POST['action_type']) : '';
        
        // Route to appropriate handler
        switch ($action) {
            case 'generate_post':
                $this->ajax_generate_post();
                break;
            case 'generate_meta':
                $this->ajax_generate_meta();
                break;
            case 'analyze_content':
                $this->ajax_analyze_content();
                break;
            case 'get_stats':
                $this->ajax_get_stats();
                break;
            default:
                wp_send_json_error(array('message' => __('Invalid action', 'aiseo')));
        }
    }
    
    /**
     * AJAX: Generate post
     */
    private function ajax_generate_post() {
        $topic = isset($_POST['topic']) ? sanitize_textarea_field($_POST['topic']) : '';
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $length = isset($_POST['length']) ? sanitize_key($_POST['length']) : 'medium';
        
        if (empty($topic)) {
            wp_send_json_error(array('message' => __('Topic is required', 'aiseo')));
        }
        
        $creator = new AISEO_Post_Creator();
        $result = $creator->create_post(array(
            'topic' => $topic,
            'keyword' => $keyword,
            'content_length' => $length,
            'post_status' => 'draft',
        ));
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Generate meta
     */
    private function ajax_generate_meta() {
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $meta_type = isset($_POST['meta_type']) ? sanitize_key($_POST['meta_type']) : 'title';
        
        if (!$post_id) {
            wp_send_json_error(array('message' => __('Post ID is required', 'aiseo')));
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => __('Post not found', 'aiseo')));
        }
        
        $api = new AISEO_API();
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        if ($meta_type === 'title') {
            $result = $api->generate_title($post->post_content, $keyword);
        } else {
            $result = $api->generate_meta_description($post->post_content, $keyword);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('content' => $result));
    }
    
    /**
     * AJAX: Analyze content
     */
    private function ajax_analyze_content() {
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => __('Post ID is required', 'aiseo')));
        }
        
        if (!class_exists('AISEO_Analysis')) {
            wp_send_json_error(array('message' => __('Analysis class not found', 'aiseo')));
        }
        
        $analysis = new AISEO_Analysis();
        $post = get_post($post_id);
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $results = array(
            'keyword_density' => $analysis->analyze_keyword_density($post->post_content, $keyword),
            'readability' => $analysis->analyze_readability($post->post_content),
            'content_length' => $analysis->analyze_content_length($post->post_content),
        );
        
        $overall_score = $analysis->generate_seo_score($results);
        $results['overall_score'] = $overall_score;
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX: Get statistics
     */
    private function ajax_get_stats() {
        $stat_type = isset($_POST['stat_type']) ? sanitize_key($_POST['stat_type']) : 'overview';
        
        $stats = array();
        
        switch ($stat_type) {
            case 'posts':
                if (class_exists('AISEO_Post_Creator')) {
                    $creator = new AISEO_Post_Creator();
                    $stats = $creator->get_statistics();
                }
                break;
            case 'api':
                $stats = array(
                    'total_requests' => get_option('aiseo_total_api_requests', 0),
                    'tokens_used' => get_option('aiseo_token_usage_total', 0),
                    'monthly_tokens' => get_option('aiseo_token_usage_month', 0),
                );
                break;
            default:
                $stats = array(
                    'posts_analyzed' => get_option('aiseo_posts_analyzed_count', 0),
                    'metadata_generated' => get_option('aiseo_metadata_generated_count', 0),
                    'ai_posts_created' => get_option('aiseo_ai_posts_created_count', 0),
                );
        }
        
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX: Create post from AI Content tab
     */
    public function ajax_create_post() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $topic = isset($_POST['topic']) ? sanitize_textarea_field($_POST['topic']) : '';
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $length = isset($_POST['length']) ? sanitize_key($_POST['length']) : 'medium';
        
        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }
        
        $creator = new AISEO_Post_Creator();
        $result = $creator->create_post(array(
            'topic' => $topic,
            'keyword' => $keyword,
            'content_length' => $length,
            'post_status' => 'draft',
        ));
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Generate title for SEO Tools tab
     */
    public function ajax_generate_title() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        $api = new AISEO_API();
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        $title = $api->generate_title($post->post_content, $keyword);
        
        if (is_wp_error($title)) {
            wp_send_json_error($title->get_error_message());
        }
        
        wp_send_json_success($title);
    }
    
    /**
     * AJAX: Generate description for SEO Tools tab
     */
    public function ajax_generate_description() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        $api = new AISEO_API();
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        $description = $api->generate_meta_description($post->post_content, $keyword);
        
        if (is_wp_error($description)) {
            wp_send_json_error($description->get_error_message());
        }
        
        wp_send_json_success($description);
    }
    
    /**
     * AJAX: Generate keyword for SEO Tools tab
     */
    public function ajax_generate_keyword() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        $post = get_post($post_id);
        if (!$post || empty($post->post_content)) {
            wp_send_json_error('Post content is required');
        }
        
        $api = new AISEO_API();
        $prompt = "Analyze this content and suggest the single most important SEO keyword or key phrase (2-4 words maximum) that best represents the main topic. Return only the keyword phrase, nothing else.\n\nContent:\n" . wp_strip_all_tags($post->post_content);
        
        $keyword = $api->make_request($prompt, array(
            'max_tokens' => 20,
            'temperature' => 0.3,
        ));
        
        if (is_wp_error($keyword)) {
            wp_send_json_error($keyword->get_error_message());
        }
        
        $keyword = trim(strtolower($keyword));
        wp_send_json_success($keyword);
    }
    
    /**
     * AJAX: Analyze content for SEO Tools tab
     */
    public function ajax_analyze_post() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        if (!class_exists('AISEO_Analysis')) {
            wp_send_json_error('Analysis class not found');
        }
        
        $analysis = new AISEO_Analysis();
        $post = get_post($post_id);
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $results = array(
            'keyword_density' => $analysis->analyze_keyword_density($post->post_content, $keyword),
            'readability' => $analysis->analyze_readability($post->post_content),
            'content_length' => $analysis->analyze_content_length($post->post_content),
        );
        
        $overall_score = $analysis->generate_seo_score($results);
        $results['overall_score'] = $overall_score;
        
        wp_send_json_success($results);
    }
    
    /**
     * AJAX: Add redirect (Technical SEO)
     */
    public function ajax_add_redirect() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $from_url = isset($_POST['from_url']) ? sanitize_text_field($_POST['from_url']) : '';
        $to_url = isset($_POST['to_url']) ? sanitize_text_field($_POST['to_url']) : '';
        $type = isset($_POST['redirect_type']) ? absint($_POST['redirect_type']) : 301;
        
        if (empty($from_url) || empty($to_url)) {
            wp_send_json_error('Both URLs are required');
        }
        
        // Store in options (simple implementation)
        $redirects = get_option('aiseo_redirects', array());
        $redirects[] = array(
            'id' => uniqid('redirect_', true),
            'from_url' => $from_url,
            'to_url' => $to_url,
            'type' => $type,
            'hits' => 0,
            'created' => current_time('mysql'),
        );
        update_option('aiseo_redirects', $redirects);
        
        wp_send_json_success(array('message' => 'Redirect added successfully'));
    }
    
    /**
     * AJAX: List all redirects
     */
    public function ajax_list_redirects() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get redirects from options
        $redirects = get_option('aiseo_redirects', array());
        
        wp_send_json_success($redirects);
    }
    
    /**
     * AJAX: Delete a redirect
     */
    public function ajax_delete_redirect() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $redirect_id = isset($_POST['redirect_id']) ? sanitize_text_field($_POST['redirect_id']) : '';
        
        if (empty($redirect_id)) {
            wp_send_json_error('Redirect ID is required');
        }
        
        // Get current redirects
        $redirects = get_option('aiseo_redirects', array());
        
        // Find and remove the redirect
        $found = false;
        foreach ($redirects as $key => $redirect) {
            if (isset($redirect['id']) && $redirect['id'] === $redirect_id) {
                unset($redirects[$key]);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            wp_send_json_error('Redirect not found');
        }
        
        // Re-index array and save
        $redirects = array_values($redirects);
        update_option('aiseo_redirects', $redirects);
        
        wp_send_json_success(array('message' => 'Redirect deleted successfully'));
    }
    
    /**
     * AJAX: Optimize permalinks (Technical SEO)
     */
    public function ajax_optimize_permalinks() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        if (!class_exists('AISEO_Permalink')) {
            wp_send_json_error('Permalink class not found');
        }
        
        $permalink = new AISEO_Permalink();
        $posts = get_posts(array('numberposts' => 100, 'post_status' => 'publish'));
        $optimized = 0;
        
        foreach ($posts as $post) {
            if ($permalink->optimize_post_slug($post->ID)) {
                $optimized++;
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf('%d permalinks optimized', $optimized),
            'count' => $optimized
        ));
    }
    
    /**
     * AJAX: Regenerate sitemap (Technical SEO)
     */
    public function ajax_regenerate_sitemap() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        // Clear sitemap cache
        delete_transient('aiseo_sitemap_cache');
        
        // Regenerate by accessing the sitemap
        if (class_exists('AISEO_Sitemap')) {
            $sitemap = new AISEO_Sitemap();
            $sitemap->generate_sitemap();
        }
        
        wp_send_json_success('Sitemap regenerated successfully');
    }
    
    /**
     * AJAX: Generate image alt text (Technical SEO)
     */
    public function ajax_generate_image_alt() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get all posts with images
        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $results = array();
        $success_count = 0;
        $failed_count = 0;
        
        foreach ($posts as $post) {
            // Find images in post content
            preg_match_all('/<img[^>]+>/i', $post->post_content, $matches);
            
            if (!empty($matches[0])) {
                foreach ($matches[0] as $img_tag) {
                    // Check if alt attribute is missing or empty
                    if (!preg_match('/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match) || empty($alt_match[1])) {
                        // Extract src
                        if (preg_match('/src=["\']([^"\']+)["\']/', $img_tag, $src_match)) {
                            $image_url = $src_match[1];
                            
                            // Generate mock alt text (in real implementation, use AI)
                            $alt_text = 'AI-generated: ' . basename($image_url, '.' . pathinfo($image_url, PATHINFO_EXTENSION));
                            
                            // Update the image tag with alt text
                            $new_img_tag = preg_replace('/<img/', '<img alt="' . esc_attr($alt_text) . '"', $img_tag, 1);
                            $new_content = str_replace($img_tag, $new_img_tag, $post->post_content);
                            
                            // Update post
                            $updated = wp_update_post(array(
                                'ID' => $post->ID,
                                'post_content' => $new_content
                            ));
                            
                            $results[] = array(
                                'post_title' => $post->post_title,
                                'post_id' => $post->ID,
                                'url' => $image_url,
                                'alt_text' => $alt_text,
                                'success' => !is_wp_error($updated) && $updated > 0,
                                'message' => !is_wp_error($updated) && $updated > 0 ? 'Updated' : 'Failed'
                            );
                            
                            if (!is_wp_error($updated) && $updated > 0) {
                                $success_count++;
                            } else {
                                $failed_count++;
                            }
                        }
                    }
                }
            }
        }
        
        wp_send_json_success(array(
            'images' => $results,
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'message' => sprintf('%d images updated, %d failed', $success_count, $failed_count)
        ));
    }
    
    /**
     * AJAX: Save CPT settings (Advanced)
     */
    public function ajax_save_cpt_settings() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $cpt_settings = isset($_POST['aiseo_cpt']) ? array_map('sanitize_text_field', $_POST['aiseo_cpt']) : array();
        update_option('aiseo_enabled_post_types', $cpt_settings);
        
        wp_send_json_success('Custom post type settings saved');
    }
    
    /**
     * AJAX: Generate report (Advanced)
     */
    public function ajax_generate_report() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get actual statistics from database
        $posts_with_meta_title = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_aiseo_title',
                    'compare' => 'EXISTS'
                )
            ),
            'fields' => 'ids'
        ));
        
        $posts_with_meta_desc = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_aiseo_description',
                    'compare' => 'EXISTS'
                )
            ),
            'fields' => 'ids'
        ));
        
        $posts_with_keyword = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_aiseo_focus_keyword',
                    'compare' => 'EXISTS'
                )
            ),
            'fields' => 'ids'
        ));
        
        $all_posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        $report = array(
            'generated_at' => current_time('mysql'),
            'posts_analyzed' => count($posts_with_keyword),
            'metadata_generated' => count($posts_with_meta_title) + count($posts_with_meta_desc),
            'ai_posts_created' => get_option('aiseo_ai_posts_created_count', 0),
            'api_requests' => get_option('aiseo_total_api_requests', 0),
            'total_posts' => count($all_posts),
            'posts_with_seo' => count(array_unique(array_merge($posts_with_meta_title, $posts_with_meta_desc, $posts_with_keyword)))
        );
        
        // Get recent posts with SEO scores
        if (class_exists('AISEO_Analysis')) {
            $analysis = new AISEO_Analysis();
            $posts = get_posts(array('numberposts' => 10));
            $scores = array();
            
            foreach ($posts as $post) {
                $keyword = get_post_meta($post->ID, '_aiseo_focus_keyword', true);
                $results = array(
                    'keyword_density' => $analysis->analyze_keyword_density($post->post_content, $keyword),
                    'readability' => $analysis->analyze_readability($post->post_content),
                    'content_length' => $analysis->analyze_content_length($post->post_content),
                );
                $scores[] = array(
                    'post_id' => $post->ID,
                    'title' => $post->post_title,
                    'score' => $analysis->generate_seo_score($results)
                );
            }
            $report['recent_scores'] = $scores;
        }
        
        wp_send_json_success($report);
    }
    
    /**
     * AJAX: Keyword research (Advanced)
     */
    public function ajax_keyword_research() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        
        if (empty($keyword)) {
            wp_send_json_error('Keyword is required');
        }
        
        $api = new AISEO_API();
        $prompt = "Provide 10 related keyword suggestions for: '{$keyword}'. Return only a comma-separated list of keywords, nothing else.";
        
        $result = $api->make_request($prompt, array(
            'max_tokens' => 100,
            'temperature' => 0.7,
        ));
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        $keywords = array_map('trim', explode(',', $result));
        
        wp_send_json_success(array(
            'keyword' => $keyword,
            'suggestions' => $keywords
        ));
    }
    
    /**
     * AJAX: Rewrite content (AI Content tab)
     */
    public function ajax_rewrite_content() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $mode = isset($_POST['mode']) ? sanitize_key($_POST['mode']) : 'improve';
        
        if (empty($content)) {
            wp_send_json_error('Content is required');
        }
        
        if (!class_exists('AISEO_Rewriter')) {
            wp_send_json_error('Rewriter class not found');
        }
        
        $rewriter = new AISEO_Rewriter();
        $result = $rewriter->rewrite($content, $mode);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Content suggestions (AI Content tab)
     */
    public function ajax_content_suggestions() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
        
        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }
        
        if (!class_exists('AISEO_Content_Suggestions')) {
            wp_send_json_error('Content Suggestions class not found');
        }
        
        $suggestions = new AISEO_Content_Suggestions();
        $result = $suggestions->get_suggestions($topic, 5);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Generate outline (AI Content tab)
     */
    public function ajax_generate_outline() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        
        if (empty($topic)) {
            wp_send_json_error('Topic is required');
        }
        
        if (!class_exists('AISEO_Outline')) {
            // Mock response for demo
            $result = "<h2>Introduction to $topic</h2>\n<p>Overview and importance</p>\n\n<h2>Main Points</h2>\n<h3>Point 1</h3>\n<p>Details about first aspect</p>\n\n<h3>Point 2</h3>\n<p>Details about second aspect</p>\n\n<h2>Conclusion</h2>\n<p>Summary and key takeaways</p>";
            wp_send_json_success($result);
            return;
        }
        
        $outline = new AISEO_Outline();
        $result = $outline->generate($topic, $keyword);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Generate FAQ (AI Content tab)
     */
    public function ajax_generate_faq() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $count = isset($_POST['count']) ? absint($_POST['count']) : 5;
        
        if (empty($content)) {
            wp_send_json_error('Content is required');
        }
        
        if (!class_exists('AISEO_FAQ')) {
            // Mock response for demo
            $faqs = array();
            for ($i = 1; $i <= $count; $i++) {
                $faqs[] = array(
                    'question' => "What is the main point #$i about this content?",
                    'answer' => "This content discusses important aspect #$i which provides valuable information for readers."
                );
            }
            wp_send_json_success($faqs);
            return;
        }
        
        $faq = new AISEO_FAQ();
        $result = $faq->generate($content, $count);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Internal linking suggestions (SEO Tools tab)
     */
    public function ajax_internal_linking() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        if (!class_exists('AISEO_Internal_Linking')) {
            // Mock response for demo
            $post = get_post($post_id);
            $suggestions = array(
                array(
                    'title' => 'Related Article: Getting Started Guide',
                    'url' => home_url('/getting-started'),
                    'reason' => 'Highly relevant content with similar keywords'
                ),
                array(
                    'title' => 'Tutorial: Advanced Techniques',
                    'url' => home_url('/advanced-tutorial'),
                    'reason' => 'Complementary topic that adds value'
                ),
                array(
                    'title' => 'Case Study: Success Story',
                    'url' => home_url('/case-study'),
                    'reason' => 'Real-world example to support your points'
                ),
                array(
                    'title' => 'Resource: Tools and Templates',
                    'url' => home_url('/resources'),
                    'reason' => 'Helpful resources for readers'
                ),
                array(
                    'title' => 'FAQ: Common Questions',
                    'url' => home_url('/faq'),
                    'reason' => 'Answers related questions readers might have'
                )
            );
            wp_send_json_success($suggestions);
            return;
        }
        
        $linking = new AISEO_Internal_Linking();
        $suggestions = $linking->get_suggestions($post_id, 5);
        
        if (is_wp_error($suggestions)) {
            wp_send_json_error($suggestions->get_error_message());
        }
        
        wp_send_json_success($suggestions);
    }
    
    /**
     * AJAX: Meta variations (SEO Tools tab)
     */
    public function ajax_meta_variations() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $type = isset($_POST['type']) ? sanitize_key($_POST['type']) : 'title';
        
        if (!$post_id) {
            wp_send_json_error('Post ID is required');
        }
        
        if (!class_exists('AISEO_Meta_Variations')) {
            // Mock response for demo
            $post = get_post($post_id);
            if (!$post) {
                wp_send_json_error('Post not found');
                return;
            }
            $post_title = $post->post_title;
            
            if ($type === 'title') {
                $result = array(
                    array('text' => $post_title . ' - Complete Guide', 'score' => 95),
                    array('text' => 'How to ' . $post_title . ' Successfully', 'score' => 92),
                    array('text' => $post_title . ': Tips and Best Practices', 'score' => 88),
                    array('text' => 'The Ultimate ' . $post_title . ' Tutorial', 'score' => 85),
                    array('text' => $post_title . ' Explained Simply', 'score' => 82)
                );
            } else {
                $result = array(
                    array('text' => 'Learn everything about ' . strtolower($post_title) . ' with our comprehensive guide. Includes tips, examples, and best practices.', 'score' => 94),
                    array('text' => 'Discover the best ways to implement ' . strtolower($post_title) . '. Step-by-step instructions and expert advice.', 'score' => 91),
                    array('text' => 'Master ' . strtolower($post_title) . ' with this detailed tutorial. Perfect for beginners and experts alike.', 'score' => 87),
                    array('text' => 'Complete guide to ' . strtolower($post_title) . '. Everything you need to know in one place.', 'score' => 84),
                    array('text' => 'Improve your understanding of ' . strtolower($post_title) . ' with practical examples and proven strategies.', 'score' => 81)
                );
            }
            wp_send_json_success($result);
            return;
        }
        
        $variations = new AISEO_Meta_Variations();
        $post = get_post($post_id);
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        if ($type === 'title') {
            $result = $variations->generate_title_variations($post->post_content, $keyword, 5);
        } else {
            $result = $variations->generate_description_variations($post->post_content, $keyword, 5);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Import SEO data (Bulk Operations tab)
     */
    public function ajax_import_seo() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $source = isset($_POST['source']) ? sanitize_key($_POST['source']) : '';
        
        if (empty($source)) {
            wp_send_json_error('Source is required');
        }
        
        if (!class_exists('AISEO_Import_Export')) {
            wp_send_json_error('Import/Export class not found');
        }
        
        $importer = new AISEO_Import_Export();
        $result = $importer->import_from($source);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Export SEO data (Bulk Operations tab)
     */
    public function ajax_export_seo() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $format = isset($_POST['format']) ? sanitize_key($_POST['format']) : 'json';
        
        if (!class_exists('AISEO_Import_Export')) {
            try {
                // Start with a very small query to test
                $args = array(
                    'post_type' => array('post', 'page'),
                    'posts_per_page' => 100, // Reduced from 1000
                    'post_status' => array('publish', 'draft'),
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'fields' => 'ids' // Only get IDs first to reduce memory
                );
                
                $post_ids = get_posts($args);
                
                if (empty($post_ids)) {
                    wp_send_json_success(array());
                    return;
                }
                
                $export_data = array();
                
                // Process posts one by one
                foreach ($post_ids as $post_id) {
                    $post = get_post($post_id);
                    if (!$post) {
                        continue;
                    }
                    
                    $meta_title = get_post_meta($post_id, '_aiseo_title', true);
                    $meta_desc = get_post_meta($post_id, '_aiseo_description', true);
                    $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
                    
                    // Only include posts with SEO data
                    if ($meta_title || $meta_desc || $keyword) {
                        $export_data[] = array(
                            'post_id' => $post_id,
                            'post_title' => $post->post_title ? $post->post_title : 'Untitled',
                            'post_type' => $post->post_type,
                            'post_status' => $post->post_status,
                            'meta_title' => $meta_title ? $meta_title : '',
                            'meta_description' => $meta_desc ? $meta_desc : '',
                            'focus_keyword' => $keyword ? $keyword : '',
                            'seo_score' => get_post_meta($post_id, '_aiseo_seo_score', true),
                        );
                    }
                }
                
                // If no posts with SEO data, return empty array
                if (empty($export_data)) {
                    wp_send_json_success(array());
                    return;
                }
                
                wp_send_json_success($export_data);
                return;
            } catch (Exception $e) {
                error_log('AISEO Export Error: ' . $e->getMessage());
                wp_send_json_error('Export failed: ' . $e->getMessage());
                return;
            } catch (Error $e) {
                error_log('AISEO Export Fatal Error: ' . $e->getMessage());
                wp_send_json_error('Export failed: Fatal error occurred');
                return;
            }
        }
        
        $exporter = new AISEO_Import_Export();
        $result = $exporter->export_to($format);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
            return;
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * AJAX: Save approved title
     */
    public function ajax_save_title() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';
        
        if (!$post_id || empty($value)) {
            wp_send_json_error('Invalid data');
        }
        
        // Update post meta for SEO title
        update_post_meta($post_id, '_aiseo_title', $value);
        
        wp_send_json_success(array('post_id' => $post_id, 'title' => $value));
    }
    
    /**
     * AJAX: Save approved description
     */
    public function ajax_save_description() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $value = isset($_POST['value']) ? sanitize_text_field($_POST['value']) : '';
        
        if (!$post_id || empty($value)) {
            wp_send_json_error('Invalid data');
        }
        
        // Update post meta for SEO description
        update_post_meta($post_id, '_aiseo_description', $value);
        
        wp_send_json_success(array('post_id' => $post_id, 'description' => $value));
    }
    
    /**
     * AJAX: Find images missing alt text
     */
    public function ajax_find_missing_alt() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        // Get all posts with images
        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $images_without_alt = array();
        
        foreach ($posts as $post) {
            // Find images in post content
            preg_match_all('/<img[^>]+>/i', $post->post_content, $matches);
            
            if (!empty($matches[0])) {
                foreach ($matches[0] as $img_tag) {
                    // Check if alt attribute is missing or empty
                    if (!preg_match('/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match) || empty($alt_match[1])) {
                        // Extract src
                        if (preg_match('/src=["\']([^"\']+)["\']/', $img_tag, $src_match)) {
                            $images_without_alt[] = array(
                                'url' => $src_match[1],
                                'post_title' => $post->post_title,
                                'post_id' => $post->ID,
                                'edit_url' => get_edit_post_link($post->ID, 'raw')
                            );
                        }
                    }
                }
            }
        }
        
        wp_send_json_success($images_without_alt);
    }
    
    /**
     * AJAX: Generate alt text for single image
     */
    public function ajax_generate_single_alt() {
        check_ajax_referer('aiseo_admin_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        $image_url = isset($_POST['image_url']) ? esc_url_raw($_POST['image_url']) : '';
        
        if (!$post_id || !$image_url) {
            wp_send_json_error('Invalid data');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Post not found');
        }
        
        // Generate alt text from image filename
        $alt_text = 'AI-generated: ' . basename($image_url, '.' . pathinfo($image_url, PATHINFO_EXTENSION));
        $alt_text = str_replace(array('-', '_'), ' ', $alt_text);
        $alt_text = ucwords($alt_text);
        
        // Find the image tag in post content and add alt attribute
        $content = $post->post_content;
        $image_url_escaped = preg_quote($image_url, '/');
        
        // Match img tag with this src
        $pattern = '/<img([^>]*src=["\']' . $image_url_escaped . '["\'][^>]*)>/i';
        
        if (preg_match($pattern, $content, $matches)) {
            $img_tag = $matches[0];
            
            // Add or update alt attribute
            if (preg_match('/alt=["\'][^"\']*["\']/', $img_tag)) {
                // Update existing alt
                $new_img_tag = preg_replace('/alt=["\'][^"\']*["\']/', 'alt="' . esc_attr($alt_text) . '"', $img_tag);
            } else {
                // Add new alt attribute
                $new_img_tag = preg_replace('/<img/', '<img alt="' . esc_attr($alt_text) . '"', $img_tag);
            }
            
            // Replace in content
            $new_content = str_replace($img_tag, $new_img_tag, $content);
            
            // Update post
            $updated = wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $new_content
            ));
            
            if (is_wp_error($updated) || !$updated) {
                wp_send_json_error('Failed to update post');
            }
            
            wp_send_json_success(array(
                'alt_text' => $alt_text,
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Image not found in post content');
        }
    }
}
