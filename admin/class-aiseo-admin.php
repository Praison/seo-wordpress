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
}
