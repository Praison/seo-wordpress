<?php
/**
 * Settings Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['aiseo_settings_submit']) && check_admin_referer('aiseo_settings_nonce', 'aiseo_settings_nonce')) {
    // API Settings
    if (isset($_POST['aiseo_api_key']) && !empty($_POST['aiseo_api_key'])) {
        $api_key_input = sanitize_text_field($_POST['aiseo_api_key']);
        // Only save if it's not the masked value
        if (strpos($api_key_input, '*') === false && !empty($api_key_input)) {
            AISEO_Helpers::save_api_key($api_key_input);
        }
    }
    if (isset($_POST['aiseo_model'])) {
        update_option('aiseo_model', sanitize_text_field($_POST['aiseo_model']));
    }
    if (isset($_POST['aiseo_max_tokens'])) {
        update_option('aiseo_max_tokens', absint($_POST['aiseo_max_tokens']));
    }
    if (isset($_POST['aiseo_temperature'])) {
        update_option('aiseo_temperature', floatval($_POST['aiseo_temperature']));
    }
    
    // Performance Settings
    if (isset($_POST['aiseo_cache_ttl'])) {
        update_option('aiseo_cache_ttl', absint($_POST['aiseo_cache_ttl']));
    }
    if (isset($_POST['aiseo_rate_limit'])) {
        update_option('aiseo_rate_limit', absint($_POST['aiseo_rate_limit']));
    }
    
    // Feature Toggles
    update_option('aiseo_auto_generate_title', isset($_POST['aiseo_auto_generate_title']) ? '1' : '0');
    update_option('aiseo_auto_generate_description', isset($_POST['aiseo_auto_generate_description']) ? '1' : '0');
    update_option('aiseo_enable_schema', isset($_POST['aiseo_enable_schema']) ? '1' : '0');
    update_option('aiseo_enable_sitemap', isset($_POST['aiseo_enable_sitemap']) ? '1' : '0');
    
    // Logging
    update_option('aiseo_enable_logging', isset($_POST['aiseo_enable_logging']) ? '1' : '0');
    update_option('aiseo_log_level', sanitize_text_field($_POST['aiseo_log_level']));
    
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'aiseo') . '</p></div>';
}

// Get current settings
$api_key = AISEO_Helpers::get_api_key();
$model = get_option('aiseo_model', 'gpt-4o-mini');
$max_tokens = get_option('aiseo_max_tokens', 1000);
$temperature = get_option('aiseo_temperature', 0.7);
$cache_ttl = get_option('aiseo_cache_ttl', 86400);
$rate_limit = get_option('aiseo_rate_limit', 10);
$auto_title = get_option('aiseo_auto_generate_title', '0');
$auto_desc = get_option('aiseo_auto_generate_description', '0');
$enable_schema = get_option('aiseo_enable_schema', '1');
$enable_sitemap = get_option('aiseo_enable_sitemap', '1');
$enable_logging = get_option('aiseo_enable_logging', '0');
$log_level = get_option('aiseo_log_level', 'error');
?>

<div class="aiseo-settings">
    <form method="post" action="">
        <?php wp_nonce_field('aiseo_settings_nonce', 'aiseo_settings_nonce'); ?>
        
        <!-- API Settings -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('API Settings', 'aiseo'); ?></h2>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label">
                    <?php esc_html_e('OpenAI API Key', 'aiseo'); ?>
                    <span class="required">*</span>
                </label>
                <input type="password" 
                       name="aiseo_api_key" 
                       value="<?php echo esc_attr($api_key ? str_repeat('*', 20) : ''); ?>" 
                       class="large-text"
                       placeholder="sk-..." />
                <span class="aiseo-form-description">
                    <?php esc_html_e('Get your API key from', 'aiseo'); ?> 
                    <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                </span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('AI Model', 'aiseo'); ?></label>
                <select name="aiseo_model" class="regular-text">
                    <option value="gpt-4o-mini" <?php selected($model, 'gpt-4o-mini'); ?>>GPT-4o Mini (Recommended)</option>
                    <option value="gpt-4o" <?php selected($model, 'gpt-4o'); ?>>GPT-4o</option>
                    <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>>GPT-4</option>
                    <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
                </select>
                <span class="aiseo-form-description"><?php esc_html_e('GPT-4o-mini offers the best balance of speed and quality', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Max Tokens', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_max_tokens" 
                       value="<?php echo esc_attr($max_tokens); ?>" 
                       class="small-text"
                       min="100" 
                       max="4000" />
                <span class="aiseo-form-description"><?php esc_html_e('Maximum tokens per API request (100-4000)', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Temperature', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_temperature" 
                       value="<?php echo esc_attr($temperature); ?>" 
                       class="small-text"
                       min="0" 
                       max="2" 
                       step="0.1" />
                <span class="aiseo-form-description"><?php esc_html_e('Controls randomness: 0 = focused, 2 = creative (0-2)', 'aiseo'); ?></span>
            </div>
        </div>
        
        <!-- Performance Settings -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Performance', 'aiseo'); ?></h2>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Cache TTL (seconds)', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_cache_ttl" 
                       value="<?php echo esc_attr($cache_ttl); ?>" 
                       class="small-text"
                       min="3600" />
                <span class="aiseo-form-description"><?php esc_html_e('How long to cache AI responses (default: 86400 = 24 hours)', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Rate Limit (requests/minute)', 'aiseo'); ?></label>
                <input type="number" 
                       name="aiseo_rate_limit" 
                       value="<?php echo esc_attr($rate_limit); ?>" 
                       class="small-text"
                       min="1" 
                       max="60" />
                <span class="aiseo-form-description"><?php esc_html_e('Maximum API requests per minute (default: 10)', 'aiseo'); ?></span>
            </div>
            
            <div class="aiseo-form-group">
                <button type="button" class="button button-secondary" onclick="if(confirm('Clear all AISEO caches?')) { location.href='<?php echo esc_url(admin_url('admin.php?page=aiseo&tab=settings&action=clear_cache')); ?>'; }">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Clear All Caches', 'aiseo'); ?>
                </button>
            </div>
        </div>
        
        <!-- Feature Toggles -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Features', 'aiseo'); ?></h2>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_auto_generate_title" value="1" <?php checked($auto_title, '1'); ?> />
                    <?php esc_html_e('Auto-generate SEO titles for new posts', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_auto_generate_description" value="1" <?php checked($auto_desc, '1'); ?> />
                    <?php esc_html_e('Auto-generate meta descriptions for new posts', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_enable_schema" value="1" <?php checked($enable_schema, '1'); ?> />
                    <?php esc_html_e('Enable Schema Markup (JSON-LD)', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_enable_sitemap" value="1" <?php checked($enable_sitemap, '1'); ?> />
                    <?php esc_html_e('Enable XML Sitemap', 'aiseo'); ?>
                </label>
            </div>
        </div>
        
        <!-- Security & Logging -->
        <div class="aiseo-form-section">
            <h2 class="aiseo-section-title"><?php esc_html_e('Security & Logging', 'aiseo'); ?></h2>
            
            <div class="aiseo-card" style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">
                <p style="margin: 0;">
                    <strong><?php esc_html_e('Security Status:', 'aiseo'); ?></strong>
                    <?php esc_html_e('API keys are encrypted with AES-256-CBC', 'aiseo'); ?>
                </p>
            </div>
            
            <div class="aiseo-form-group">
                <label>
                    <input type="checkbox" name="aiseo_enable_logging" value="1" <?php checked($enable_logging, '1'); ?> />
                    <?php esc_html_e('Enable Error Logging', 'aiseo'); ?>
                </label>
            </div>
            
            <div class="aiseo-form-group">
                <label class="aiseo-form-label"><?php esc_html_e('Log Level', 'aiseo'); ?></label>
                <select name="aiseo_log_level" class="regular-text">
                    <option value="error" <?php selected($log_level, 'error'); ?>>Error Only</option>
                    <option value="warning" <?php selected($log_level, 'warning'); ?>>Warning & Error</option>
                    <option value="info" <?php selected($log_level, 'info'); ?>>Info, Warning & Error</option>
                    <option value="debug" <?php selected($log_level, 'debug'); ?>>Debug (All)</option>
                </select>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="aiseo-form-section">
            <button type="submit" name="aiseo_settings_submit" class="button button-primary button-large">
                <span class="dashicons dashicons-saved"></span>
                <?php esc_html_e('Save Settings', 'aiseo'); ?>
            </button>
        </div>
    </form>
</div>
