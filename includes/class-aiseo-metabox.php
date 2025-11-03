<?php
/**
 * AISEO Post Editor Metabox
 *
 * Adds SEO controls to the post editor
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Metabox {
    
    /**
     * Initialize metabox
     */
    public function init() {
        // Add metabox
        add_action('add_meta_boxes', array($this, 'add_metabox'));
        
        // Save metabox data
        add_action('save_post', array($this, 'save_metabox'), 10, 2);
        
        // Enqueue metabox scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_metabox_scripts'));
    }
    
    /**
     * Add metabox to post editor
     */
    public function add_metabox() {
        $post_types = array('post', 'page');
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'aiseo_metabox',
                'AISEO - AI SEO Optimization',
                array($this, 'render_metabox'),
                $post_type,
                'normal',
                'high'
            );
        }
    }
    
    /**
     * Render metabox content
     *
     * @param WP_Post $post Post object
     */
    public function render_metabox($post) {
        // Add nonce for security
        wp_nonce_field('aiseo_metabox_nonce', 'aiseo_metabox_nonce');
        
        // Get current values
        $meta_title = get_post_meta($post->ID, '_aiseo_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_aiseo_meta_description', true);
        $focus_keyword = get_post_meta($post->ID, '_aiseo_focus_keyword', true);
        $canonical_url = get_post_meta($post->ID, '_aiseo_canonical_url', true);
        $noindex = get_post_meta($post->ID, '_aiseo_noindex', true);
        $nofollow = get_post_meta($post->ID, '_aiseo_nofollow', true);
        
        // Get SEO score if available
        $analysis = new AISEO_Analysis();
        $score_data = $analysis->analyze_content($post->ID, $focus_keyword);
        $seo_score = isset($score_data['overall_score']) ? $score_data['overall_score'] : 0;
        $seo_status = isset($score_data['status']) ? $score_data['status'] : 'poor';
        
        ?>
        <div class="aiseo-metabox">
            <!-- SEO Score -->
            <div class="aiseo-score-section">
                <h3>SEO Score</h3>
                <div class="aiseo-score-display">
                    <div class="aiseo-score-circle aiseo-score-<?php echo esc_attr($seo_status); ?>">
                        <span class="aiseo-score-number"><?php echo esc_html($seo_score); ?></span>
                        <span class="aiseo-score-label">/100</span>
                    </div>
                    <div class="aiseo-score-status">
                        <strong>Status:</strong> <?php echo esc_html(ucfirst($seo_status)); ?>
                    </div>
                </div>
            </div>
            
            <!-- Focus Keyword -->
            <div class="aiseo-field">
                <label for="aiseo_focus_keyword">
                    <strong>Focus Keyword</strong>
                    <span class="description">Main keyword to optimize for</span>
                </label>
                <input type="text" 
                       id="aiseo_focus_keyword" 
                       name="aiseo_focus_keyword" 
                       value="<?php echo esc_attr($focus_keyword); ?>" 
                       class="widefat" 
                       placeholder="e.g., wordpress seo" />
            </div>
            
            <!-- Meta Title -->
            <div class="aiseo-field">
                <label for="aiseo_meta_title">
                    <strong>SEO Title</strong>
                    <span class="description">Recommended: 50-60 characters</span>
                </label>
                <div class="aiseo-input-group">
                    <input type="text" 
                           id="aiseo_meta_title" 
                           name="aiseo_meta_title" 
                           value="<?php echo esc_attr($meta_title); ?>" 
                           class="widefat aiseo-title-input" 
                           placeholder="Auto-generated if empty"
                           maxlength="70" />
                    <button type="button" class="button aiseo-generate-btn" data-field="title">
                        <span class="dashicons dashicons-admin-generic"></span> Generate with AI
                    </button>
                </div>
                <div class="aiseo-char-count">
                    <span class="aiseo-current-count">0</span> / 60 characters
                </div>
            </div>
            
            <!-- Meta Description -->
            <div class="aiseo-field">
                <label for="aiseo_meta_description">
                    <strong>Meta Description</strong>
                    <span class="description">Recommended: 150-160 characters</span>
                </label>
                <div class="aiseo-input-group">
                    <textarea id="aiseo_meta_description" 
                              name="aiseo_meta_description" 
                              class="widefat aiseo-description-input" 
                              rows="3" 
                              placeholder="Auto-generated if empty"
                              maxlength="200"><?php echo esc_textarea($meta_description); ?></textarea>
                    <button type="button" class="button aiseo-generate-btn" data-field="description">
                        <span class="dashicons dashicons-admin-generic"></span> Generate with AI
                    </button>
                </div>
                <div class="aiseo-char-count">
                    <span class="aiseo-current-count">0</span> / 160 characters
                </div>
            </div>
            
            <!-- Advanced Settings -->
            <div class="aiseo-advanced-section">
                <h3 class="aiseo-toggle-header">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                    Advanced Settings
                </h3>
                <div class="aiseo-advanced-content" style="display: none;">
                    <!-- Canonical URL -->
                    <div class="aiseo-field">
                        <label for="aiseo_canonical_url">
                            <strong>Canonical URL</strong>
                            <span class="description">Leave empty to use default permalink</span>
                        </label>
                        <input type="url" 
                               id="aiseo_canonical_url" 
                               name="aiseo_canonical_url" 
                               value="<?php echo esc_attr($canonical_url); ?>" 
                               class="widefat" 
                               placeholder="<?php echo esc_attr(get_permalink($post->ID)); ?>" />
                    </div>
                    
                    <!-- Robots Meta -->
                    <div class="aiseo-field">
                        <label><strong>Robots Meta</strong></label>
                        <div class="aiseo-checkbox-group">
                            <label>
                                <input type="checkbox" 
                                       name="aiseo_noindex" 
                                       value="1" 
                                       <?php checked($noindex, '1'); ?> />
                                No Index (prevent search engines from indexing)
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" 
                                       name="aiseo_nofollow" 
                                       value="1" 
                                       <?php checked($nofollow, '1'); ?> />
                                No Follow (prevent search engines from following links)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="aiseo-actions">
                <button type="button" class="button button-secondary aiseo-analyze-btn">
                    <span class="dashicons dashicons-chart-bar"></span> Analyze Content
                </button>
                <button type="button" class="button button-secondary aiseo-preview-btn">
                    <span class="dashicons dashicons-visibility"></span> Preview SEO
                </button>
            </div>
            
            <!-- Analysis Results -->
            <div class="aiseo-analysis-results" style="display: none;">
                <h3>Content Analysis</h3>
                <div class="aiseo-analysis-content"></div>
            </div>
        </div>
        
        <style>
            .aiseo-metabox {
                padding: 10px 0;
            }
            .aiseo-score-section {
                background: #f0f0f1;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .aiseo-score-display {
                display: flex;
                align-items: center;
                gap: 20px;
            }
            .aiseo-score-circle {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                border: 4px solid;
            }
            .aiseo-score-good {
                background: #d4edda;
                border-color: #28a745;
                color: #28a745;
            }
            .aiseo-score-ok {
                background: #fff3cd;
                border-color: #ffc107;
                color: #856404;
            }
            .aiseo-score-poor {
                background: #f8d7da;
                border-color: #dc3545;
                color: #dc3545;
            }
            .aiseo-score-number {
                font-size: 24px;
            }
            .aiseo-score-label {
                font-size: 12px;
            }
            .aiseo-field {
                margin-bottom: 20px;
            }
            .aiseo-field label {
                display: block;
                margin-bottom: 5px;
            }
            .aiseo-field .description {
                display: block;
                font-size: 12px;
                color: #646970;
                font-weight: normal;
            }
            .aiseo-input-group {
                display: flex;
                gap: 10px;
                align-items: flex-start;
            }
            .aiseo-input-group input,
            .aiseo-input-group textarea {
                flex: 1;
            }
            .aiseo-generate-btn {
                white-space: nowrap;
            }
            .aiseo-char-count {
                font-size: 12px;
                color: #646970;
                margin-top: 5px;
            }
            .aiseo-advanced-section {
                margin-top: 20px;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .aiseo-toggle-header {
                cursor: pointer;
                user-select: none;
            }
            .aiseo-toggle-header:hover {
                color: #2271b1;
            }
            .aiseo-checkbox-group label {
                display: block;
                margin: 5px 0;
            }
            .aiseo-actions {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
            }
            .aiseo-actions button {
                margin-right: 10px;
            }
            .aiseo-analysis-results {
                margin-top: 20px;
                padding: 15px;
                background: #f0f0f1;
                border-radius: 4px;
            }
        </style>
        <?php
    }
    
    /**
     * Save metabox data
     *
     * @param int $post_id Post ID
     * @param WP_Post $post Post object
     */
    public function save_metabox($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['aiseo_metabox_nonce']) || !wp_verify_nonce($_POST['aiseo_metabox_nonce'], 'aiseo_metabox_nonce')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save focus keyword
        if (isset($_POST['aiseo_focus_keyword'])) {
            update_post_meta($post_id, '_aiseo_focus_keyword', sanitize_text_field($_POST['aiseo_focus_keyword']));
        }
        
        // Save meta title
        if (isset($_POST['aiseo_meta_title'])) {
            update_post_meta($post_id, '_aiseo_meta_title', sanitize_text_field($_POST['aiseo_meta_title']));
        }
        
        // Save meta description
        if (isset($_POST['aiseo_meta_description'])) {
            update_post_meta($post_id, '_aiseo_meta_description', sanitize_textarea_field($_POST['aiseo_meta_description']));
        }
        
        // Save canonical URL
        if (isset($_POST['aiseo_canonical_url'])) {
            update_post_meta($post_id, '_aiseo_canonical_url', esc_url_raw($_POST['aiseo_canonical_url']));
        }
        
        // Save robots meta
        update_post_meta($post_id, '_aiseo_noindex', isset($_POST['aiseo_noindex']) ? '1' : '0');
        update_post_meta($post_id, '_aiseo_nofollow', isset($_POST['aiseo_nofollow']) ? '1' : '0');
    }
    
    /**
     * Enqueue metabox scripts
     */
    public function enqueue_metabox_scripts($hook) {
        // Only load on post editor
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }
        
        // Enqueue WordPress media library
        wp_enqueue_media();
        
        // Add inline script for metabox functionality
        wp_add_inline_script('jquery', $this->get_metabox_script());
    }
    
    /**
     * Get metabox JavaScript
     *
     * @return string JavaScript code
     */
    private function get_metabox_script() {
        return "
        jQuery(document).ready(function($) {
            // Character counter
            function updateCharCount(input, counter) {
                var count = input.val().length;
                counter.find('.aiseo-current-count').text(count);
                
                var maxChars = input.attr('maxlength') || 160;
                if (count > maxChars * 0.9) {
                    counter.css('color', '#dc3545');
                } else {
                    counter.css('color', '#646970');
                }
            }
            
            $('.aiseo-title-input').on('input', function() {
                updateCharCount($(this), $(this).closest('.aiseo-field').find('.aiseo-char-count'));
            }).trigger('input');
            
            $('.aiseo-description-input').on('input', function() {
                updateCharCount($(this), $(this).closest('.aiseo-field').find('.aiseo-char-count'));
            }).trigger('input');
            
            // Toggle advanced settings
            $('.aiseo-toggle-header').on('click', function() {
                var content = $(this).next('.aiseo-advanced-content');
                var icon = $(this).find('.dashicons');
                
                content.slideToggle();
                icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
            });
            
            // Generate with AI button
            $('.aiseo-generate-btn').on('click', function() {
                var btn = $(this);
                var field = btn.data('field');
                var input = field === 'title' ? $('#aiseo_meta_title') : $('#aiseo_meta_description');
                var postId = $('#post_ID').val();
                
                btn.prop('disabled', true).text('Generating...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aiseo_generate_' + field,
                        post_id: postId,
                        nonce: $('#aiseo_metabox_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            input.val(response.data).trigger('input');
                        } else {
                            alert('Error: ' + (response.data || 'Failed to generate'));
                        }
                    },
                    error: function() {
                        alert('Error: Failed to connect to server');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<span class=\"dashicons dashicons-admin-generic\"></span> Generate with AI');
                    }
                });
            });
            
            // Analyze content button
            $('.aiseo-analyze-btn').on('click', function() {
                var btn = $(this);
                var postId = $('#post_ID').val();
                var resultsDiv = $('.aiseo-analysis-results');
                
                btn.prop('disabled', true).text('Analyzing...');
                resultsDiv.hide();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aiseo_analyze_content',
                        post_id: postId,
                        nonce: $('#aiseo_metabox_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            var html = '<ul>';
                            $.each(response.data.analyses, function(key, analysis) {
                                var statusClass = analysis.status === 'good' ? 'green' : (analysis.status === 'ok' ? 'orange' : 'red');
                                html += '<li><strong>' + analysis.label + ':</strong> <span style=\"color:' + statusClass + '\">' + analysis.score + '/10</span> - ' + analysis.recommendation + '</li>';
                            });
                            html += '</ul>';
                            html += '<p><strong>Overall Score: ' + response.data.overall_score + '/100</strong></p>';
                            
                            resultsDiv.find('.aiseo-analysis-content').html(html);
                            resultsDiv.slideDown();
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<span class=\"dashicons dashicons-chart-bar\"></span> Analyze Content');
                    }
                });
            });
        });
        ";
    }
}
