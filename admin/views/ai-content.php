<?php
/**
 * AI Content Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$api_key = AISEO_Helpers::get_api_key();
?>

<div class="aiseo-ai-content">
    
    <?php if (empty($api_key)): ?>
        <div class="aiseo-alert aiseo-alert-warning">
            <strong><?php esc_html_e('API Key Required', 'aiseo'); ?></strong>
            <?php esc_html_e('Please configure your OpenAI API key in Settings to use AI features.', 'aiseo'); ?>
        </div>
    <?php endif; ?>
    
    <!-- AI Post Creator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e('AI Post Creator', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-create-post-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Topic or Subject', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <textarea name="topic" rows="3" class="large-text" placeholder="<?php esc_attr_e('e.g., 10 Best SEO Practices for 2024', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>></textarea>
                    <span class="aiseo-form-description"><?php esc_html_e('Describe what you want the post to be about', 'aiseo'); ?></span>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Focus Keyword (Optional)', 'aiseo'); ?></label>
                    <input type="text" name="keyword" class="regular-text" placeholder="<?php esc_attr_e('e.g., SEO best practices', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                    <span class="aiseo-form-description"><?php esc_html_e('Main keyword to optimize for', 'aiseo'); ?></span>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Content Length', 'aiseo'); ?></label>
                    <select name="content_length" class="regular-text" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <option value="short"><?php esc_html_e('Short (~500 words)', 'aiseo'); ?></option>
                        <option value="medium" selected><?php esc_html_e('Medium (~1000 words)', 'aiseo'); ?></option>
                        <option value="long"><?php esc_html_e('Long (~2000 words)', 'aiseo'); ?></option>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary button-large aiseo-generate-post" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Generate Post', 'aiseo'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Content Rewriter -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Content Rewriter', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <p><?php esc_html_e('Rewrite existing content with AI using 6 different modes:', 'aiseo'); ?></p>
            <ul>
                <li><strong><?php esc_html_e('Improve:', 'aiseo'); ?></strong> <?php esc_html_e('Enhance quality and clarity', 'aiseo'); ?></li>
                <li><strong><?php esc_html_e('Simplify:', 'aiseo'); ?></strong> <?php esc_html_e('Make it easier to understand', 'aiseo'); ?></li>
                <li><strong><?php esc_html_e('Expand:', 'aiseo'); ?></strong> <?php esc_html_e('Add more details and depth', 'aiseo'); ?></li>
                <li><strong><?php esc_html_e('Shorten:', 'aiseo'); ?></strong> <?php esc_html_e('Make it more concise', 'aiseo'); ?></li>
                <li><strong><?php esc_html_e('Professional:', 'aiseo'); ?></strong> <?php esc_html_e('Formal business tone', 'aiseo'); ?></li>
                <li><strong><?php esc_html_e('Casual:', 'aiseo'); ?></strong> <?php esc_html_e('Friendly conversational tone', 'aiseo'); ?></li>
            </ul>
            <p><em><?php esc_html_e('Available via REST API and WP-CLI', 'aiseo'); ?></em></p>
        </div>
    </div>
    
    <!-- Content Suggestions -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-lightbulb"></span>
                <?php esc_html_e('Content Suggestions', 'aiseo'); ?>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <p><?php esc_html_e('Get AI-powered content ideas:', 'aiseo'); ?></p>
            <ul>
                <li><?php esc_html_e('Topic ideas based on your niche', 'aiseo'); ?></li>
                <li><?php esc_html_e('Trending topics in your industry', 'aiseo'); ?></li>
                <li><?php esc_html_e('Content optimization tips', 'aiseo'); ?></li>
                <li><?php esc_html_e('Content briefs with structure', 'aiseo'); ?></li>
            </ul>
            <p><em><?php esc_html_e('Available via REST API and WP-CLI', 'aiseo'); ?></em></p>
        </div>
    </div>
    
    <!-- Outline Generator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('Content Outline Generator', 'aiseo'); ?>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <p><?php esc_html_e('Generate structured content outlines with AI:', 'aiseo'); ?></p>
            <ul>
                <li><?php esc_html_e('Hierarchical heading structure (H1, H2, H3)', 'aiseo'); ?></li>
                <li><?php esc_html_e('Key points for each section', 'aiseo'); ?></li>
                <li><?php esc_html_e('SEO-optimized structure', 'aiseo'); ?></li>
            </ul>
            <p><em><?php esc_html_e('Available via REST API and WP-CLI', 'aiseo'); ?></em></p>
        </div>
    </div>
    
    <!-- FAQ Generator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php esc_html_e('FAQ Generator', 'aiseo'); ?>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <p><?php esc_html_e('Auto-generate FAQs from your content:', 'aiseo'); ?></p>
            <ul>
                <li><?php esc_html_e('AI-powered question and answer generation', 'aiseo'); ?></li>
                <li><?php esc_html_e('Automatic FAQ schema markup', 'aiseo'); ?></li>
                <li><?php esc_html_e('Improves SEO with rich snippets', 'aiseo'); ?></li>
            </ul>
            <p><em><?php esc_html_e('Available via REST API and WP-CLI', 'aiseo'); ?></em></p>
        </div>
    </div>
    
</div>
