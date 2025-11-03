<?php
/**
 * AISEO Admin Interface
 *
 * Handles admin settings page and dashboard widgets
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
     * Initialize admin interface
     */
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        
        // Enqueue admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'AISEO Settings',
            'AISEO',
            'manage_options',
            'aiseo-settings',
            array($this, 'render_settings_page'),
            'dashicons-search',
            80
        );
        
        add_submenu_page(
            'aiseo-settings',
            'AISEO Settings',
            'Settings',
            'manage_options',
            'aiseo-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'aiseo-settings',
            'AISEO Statistics',
            'Statistics',
            'manage_options',
            'aiseo-stats',
            array($this, 'render_stats_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // API Settings
        register_setting('aiseo_settings', 'aiseo_api_key', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('aiseo_settings', 'aiseo_model', array(
            'default' => 'gpt-4o-mini',
            'sanitize_callback' => 'sanitize_text_field'
        ));
        
        // Social Media Settings
        register_setting('aiseo_settings', 'aiseo_twitter_site', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('aiseo_settings', 'aiseo_default_og_image', array(
            'sanitize_callback' => 'esc_url_raw'
        ));
        
        // Sitemap Settings
        register_setting('aiseo_settings', 'aiseo_sitemap_post_types', array(
            'default' => array('post', 'page'),
            'sanitize_callback' => array($this, 'sanitize_post_types')
        ));
        
        // Add settings sections
        add_settings_section(
            'aiseo_api_section',
            'OpenAI API Configuration',
            array($this, 'render_api_section'),
            'aiseo-settings'
        );
        
        add_settings_section(
            'aiseo_social_section',
            'Social Media Settings',
            array($this, 'render_social_section'),
            'aiseo-settings'
        );
        
        add_settings_section(
            'aiseo_sitemap_section',
            'Sitemap Settings',
            array($this, 'render_sitemap_section'),
            'aiseo-settings'
        );
        
        // Add settings fields
        add_settings_field(
            'aiseo_api_key',
            'OpenAI API Key',
            array($this, 'render_api_key_field'),
            'aiseo-settings',
            'aiseo_api_section'
        );
        
        add_settings_field(
            'aiseo_model',
            'AI Model',
            array($this, 'render_model_field'),
            'aiseo-settings',
            'aiseo_api_section'
        );
        
        add_settings_field(
            'aiseo_twitter_site',
            'Twitter Site Handle',
            array($this, 'render_twitter_site_field'),
            'aiseo-settings',
            'aiseo_social_section'
        );
        
        add_settings_field(
            'aiseo_sitemap_post_types',
            'Sitemap Post Types',
            array($this, 'render_sitemap_post_types_field'),
            'aiseo-settings',
            'aiseo_sitemap_section'
        );
    }
    
    /**
     * Render API section
     */
    public function render_api_section() {
        echo '<p>Configure your OpenAI API settings. Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>.</p>';
    }
    
    /**
     * Render social section
     */
    public function render_social_section() {
        echo '<p>Configure social media optimization settings.</p>';
    }
    
    /**
     * Render sitemap section
     */
    public function render_sitemap_section() {
        echo '<p>Configure XML sitemap generation settings.</p>';
    }
    
    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $api_key = AISEO_Helpers::get_api_key();
        $masked_key = $api_key ? substr($api_key, 0, 7) . '...' . substr($api_key, -4) : '';
        
        echo '<input type="password" name="aiseo_api_key" value="' . esc_attr($masked_key) . '" class="regular-text" placeholder="sk-..." />';
        echo '<p class="description">Your OpenAI API key. Also can be set via .env file (OPENAI_API_KEY).</p>';
        
        if ($api_key) {
            echo '<p class="description" style="color: green;">✓ API key is configured</p>';
        }
    }
    
    /**
     * Render model field
     */
    public function render_model_field() {
        $model = get_option('aiseo_model', 'gpt-4o-mini');
        
        echo '<select name="aiseo_model">';
        echo '<option value="gpt-4o-mini"' . selected($model, 'gpt-4o-mini', false) . '>GPT-4o Mini (Recommended)</option>';
        echo '<option value="gpt-4o"' . selected($model, 'gpt-4o', false) . '>GPT-4o</option>';
        echo '<option value="gpt-4-turbo"' . selected($model, 'gpt-4-turbo', false) . '>GPT-4 Turbo</option>';
        echo '<option value="gpt-3.5-turbo"' . selected($model, 'gpt-3.5-turbo', false) . '>GPT-3.5 Turbo</option>';
        echo '</select>';
        echo '<p class="description">AI model to use for content generation.</p>';
    }
    
    /**
     * Render Twitter site field
     */
    public function render_twitter_site_field() {
        $twitter_site = get_option('aiseo_twitter_site', '');
        
        echo '<input type="text" name="aiseo_twitter_site" value="' . esc_attr($twitter_site) . '" class="regular-text" placeholder="@yoursite" />';
        echo '<p class="description">Your site\'s Twitter handle (e.g., @yoursite).</p>';
    }
    
    /**
     * Render sitemap post types field
     */
    public function render_sitemap_post_types_field() {
        $enabled_types = get_option('aiseo_sitemap_post_types', array('post', 'page'));
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $enabled_types) ? 'checked' : '';
            echo '<label><input type="checkbox" name="aiseo_sitemap_post_types[]" value="' . esc_attr($post_type->name) . '" ' . esc_attr($checked) . ' /> ' . esc_html($post_type->label) . '</label><br>';
        }
        
        echo '<p class="description">Select which post types to include in the sitemap.</p>';
    }
    
    /**
     * Sanitize post types array
     */
    public function sanitize_post_types($value) {
        if (!is_array($value)) {
            return array();
        }
        return array_map('sanitize_text_field', $value);
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Handle form submission
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification handled by settings API
        if (isset($_GET['settings-updated'])) {
            add_settings_error('aiseo_messages', 'aiseo_message', 'Settings Saved', 'updated');
        }
        
        settings_errors('aiseo_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="aiseo-admin-header">
                <p>AI-powered SEO optimization for WordPress. Configure your settings below.</p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('aiseo_settings');
                do_settings_sections('aiseo-settings');
                submit_button('Save Settings');
                ?>
            </form>
            
            <div class="aiseo-quick-actions">
                <h2>Quick Actions</h2>
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiseo-stats')); ?>" class="button">View Statistics</a>
                    <a href="<?php echo esc_url(home_url('/sitemap.xml')); ?>" class="button" target="_blank">View Sitemap</a>
                    <a href="<?php echo esc_url(rest_url('aiseo/v1/status')); ?>" class="button" target="_blank">API Status</a>
                </p>
            </div>
            
            <div class="aiseo-documentation">
                <h2>Documentation</h2>
                <ul>
                    <li><strong>REST API:</strong> <?php echo esc_url(rest_url('aiseo/v1/')); ?></li>
                    <li><strong>WP-CLI:</strong> <code>wp aiseo --help</code></li>
                    <li><strong>Sitemap:</strong> <?php echo esc_url(home_url('/sitemap.xml')); ?></li>
                </ul>
            </div>
        </div>
        
        <style>
            .aiseo-admin-header {
                background: #f0f0f1;
                padding: 15px 20px;
                margin: 20px 0;
                border-left: 4px solid #2271b1;
            }
            .aiseo-quick-actions,
            .aiseo-documentation {
                margin-top: 30px;
                padding: 20px;
                background: #fff;
                border: 1px solid #c3c4c7;
            }
            .aiseo-quick-actions .button {
                margin-right: 10px;
            }
        </style>
        <?php
    }
    
    /**
     * Render statistics page
     */
    public function render_stats_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Get statistics
        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard stats from custom tables
        $total_requests = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aiseo_logs");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard stats from custom tables
        $failed_requests = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aiseo_failed_requests");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard stats from custom tables
        $total_tokens = $wpdb->get_var("SELECT SUM(tokens_used) FROM {$wpdb->prefix}aiseo_usage_stats");
        
        // Get sitemap stats
        $sitemap = new AISEO_Sitemap();
        $sitemap_stats = $sitemap->get_sitemap_stats();
        
        ?>
        <div class="wrap">
            <h1>AISEO Statistics</h1>
            
            <div class="aiseo-stats-grid">
                <div class="aiseo-stat-box">
                    <h3>API Requests</h3>
                    <p class="aiseo-stat-number"><?php echo number_format($total_requests); ?></p>
                    <p class="aiseo-stat-label">Total requests made</p>
                </div>
                
                <div class="aiseo-stat-box">
                    <h3>Failed Requests</h3>
                    <p class="aiseo-stat-number"><?php echo number_format($failed_requests); ?></p>
                    <p class="aiseo-stat-label">Requests that failed</p>
                </div>
                
                <div class="aiseo-stat-box">
                    <h3>Tokens Used</h3>
                    <p class="aiseo-stat-number"><?php echo number_format($total_tokens); ?></p>
                    <p class="aiseo-stat-label">Total AI tokens consumed</p>
                </div>
                
                <div class="aiseo-stat-box">
                    <h3>Sitemap URLs</h3>
                    <p class="aiseo-stat-number"><?php echo number_format($sitemap_stats['total_urls']); ?></p>
                    <p class="aiseo-stat-label">URLs in sitemap</p>
                </div>
            </div>
            
            <div class="aiseo-stats-details">
                <h2>Sitemap Breakdown</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Post Type</th>
                            <th>URL Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sitemap_stats['post_types'] as $type => $count): ?>
                        <tr>
                            <td><?php echo esc_html(ucfirst($type)); ?></td>
                            <td><?php echo number_format($count); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <style>
            .aiseo-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .aiseo-stat-box {
                background: #fff;
                padding: 20px;
                border: 1px solid #c3c4c7;
                text-align: center;
            }
            .aiseo-stat-box h3 {
                margin: 0 0 10px 0;
                font-size: 14px;
                color: #646970;
            }
            .aiseo-stat-number {
                font-size: 36px;
                font-weight: bold;
                color: #2271b1;
                margin: 10px 0;
            }
            .aiseo-stat-label {
                font-size: 12px;
                color: #646970;
                margin: 0;
            }
            .aiseo-stats-details {
                margin-top: 30px;
                background: #fff;
                padding: 20px;
                border: 1px solid #c3c4c7;
            }
        </style>
        <?php
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'aiseo_dashboard_widget',
            'AISEO Statistics',
            array($this, 'render_dashboard_widget')
        );
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        global $wpdb;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard widget stats from custom tables
        $total_requests = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aiseo_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Dashboard widget stats from custom tables
        $total_tokens = $wpdb->get_var("SELECT SUM(tokens_used) FROM {$wpdb->prefix}aiseo_usage_stats WHERE date > DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        
        ?>
        <div class="aiseo-dashboard-widget">
            <p><strong>Last 7 Days:</strong></p>
            <ul>
                <li>API Requests: <strong><?php echo number_format($total_requests); ?></strong></li>
                <li>Tokens Used: <strong><?php echo number_format($total_tokens); ?></strong></li>
            </ul>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=aiseo-stats')); ?>">View Full Statistics →</a>
            </p>
        </div>
        <style>
            .aiseo-dashboard-widget ul {
                margin: 10px 0;
            }
            .aiseo-dashboard-widget li {
                margin: 5px 0;
            }
        </style>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Load on all admin pages (for metabox styling)
        wp_enqueue_style('aiseo-admin', AISEO_PLUGIN_URL . 'css/aiseo-admin.css', array(), AISEO_VERSION);
    }
}
