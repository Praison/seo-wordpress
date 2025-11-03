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

$api_key = '';
if (class_exists('AISEO_Helpers')) {
    $api_key = AISEO_Helpers::get_api_key();
}
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
            <form id="aiseo-rewrite-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Content to Rewrite', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <textarea name="content" rows="6" class="large-text" placeholder="<?php esc_attr_e('Paste your content here...', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>></textarea>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Rewrite Mode', 'aiseo'); ?></label>
                    <select name="mode" class="regular-text" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <option value="improve"><?php esc_html_e('Improve - Enhance quality and clarity', 'aiseo'); ?></option>
                        <option value="simplify"><?php esc_html_e('Simplify - Make it easier to understand', 'aiseo'); ?></option>
                        <option value="expand"><?php esc_html_e('Expand - Add more details and depth', 'aiseo'); ?></option>
                        <option value="shorten"><?php esc_html_e('Shorten - Make it more concise', 'aiseo'); ?></option>
                        <option value="professional"><?php esc_html_e('Professional - Formal business tone', 'aiseo'); ?></option>
                        <option value="casual"><?php esc_html_e('Casual - Friendly conversational tone', 'aiseo'); ?></option>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-rewrite-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Rewrite Content', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-rewrite-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Rewritten Content:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                    <div class="aiseo-button-group aiseo-mt-20">
                        <button type="button" class="button aiseo-copy-result"><?php esc_html_e('Copy to Clipboard', 'aiseo'); ?></button>
                        <select class="aiseo-post-type-select">
                            <option value="post"><?php esc_html_e('Post', 'aiseo'); ?></option>
                            <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                        </select>
                        <button type="button" class="button button-primary aiseo-create-post-from-result" data-source="rewrite">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e('Create Post', 'aiseo'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Content Suggestions -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-lightbulb"></span>
                <?php esc_html_e('Content Suggestions', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-suggestions-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Topic or Niche', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" name="topic" class="regular-text" placeholder="<?php esc_attr_e('e.g., Digital Marketing', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                    <span class="aiseo-form-description"><?php esc_html_e('Get 5 AI-powered content ideas', 'aiseo'); ?></span>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-suggestions-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-lightbulb"></span>
                        <?php esc_html_e('Get Suggestions', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-suggestions-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Content Ideas:', 'aiseo'); ?></h4>
                    <ul class="aiseo-result-list"></ul>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Outline Generator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('Content Outline Generator', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-outline-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Topic', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <input type="text" name="topic" class="regular-text" placeholder="<?php esc_attr_e('e.g., Complete Guide to WordPress SEO', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Focus Keyword (Optional)', 'aiseo'); ?></label>
                    <input type="text" name="keyword" class="regular-text" placeholder="<?php esc_attr_e('e.g., WordPress SEO', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-outline-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('Generate Outline', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-outline-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Content Outline:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                    <div class="aiseo-button-group aiseo-mt-20">
                        <button type="button" class="button aiseo-copy-result"><?php esc_html_e('Copy to Clipboard', 'aiseo'); ?></button>
                        <select class="aiseo-post-type-select">
                            <option value="post"><?php esc_html_e('Post', 'aiseo'); ?></option>
                            <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                        </select>
                        <button type="button" class="button button-primary aiseo-create-post-from-result" data-source="outline">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e('Create Post', 'aiseo'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- FAQ Generator -->
    <div class="aiseo-card">
        <div class="aiseo-card-header">
            <h3 class="aiseo-card-title">
                <span class="dashicons dashicons-editor-help"></span>
                <?php esc_html_e('FAQ Generator', 'aiseo'); ?>
                <span class="aiseo-badge aiseo-badge-ai"><?php esc_html_e('AI-Powered', 'aiseo'); ?></span>
            </h3>
        </div>
        <div class="aiseo-card-body">
            <form id="aiseo-faq-form" class="aiseo-form">
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label">
                        <?php esc_html_e('Content', 'aiseo'); ?>
                        <span class="required">*</span>
                    </label>
                    <textarea name="content" rows="6" class="large-text" placeholder="<?php esc_attr_e('Paste your content to generate FAQs from...', 'aiseo'); ?>" <?php echo empty($api_key) ? 'disabled' : ''; ?>></textarea>
                </div>
                
                <div class="aiseo-form-group">
                    <label class="aiseo-form-label"><?php esc_html_e('Number of FAQs', 'aiseo'); ?></label>
                    <select name="count" class="regular-text" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <option value="3">3</option>
                        <option value="5" selected>5</option>
                        <option value="10">10</option>
                    </select>
                </div>
                
                <div class="aiseo-button-group">
                    <button type="button" class="button button-primary aiseo-faq-btn" <?php echo empty($api_key) ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-editor-help"></span>
                        <?php esc_html_e('Generate FAQs', 'aiseo'); ?>
                    </button>
                </div>
                
                <div id="aiseo-faq-result" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Generated FAQs:', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                    <div class="aiseo-button-group aiseo-mt-20">
                        <button type="button" class="button aiseo-copy-result"><?php esc_html_e('Copy to Clipboard', 'aiseo'); ?></button>
                        <select class="aiseo-post-type-select">
                            <option value="post"><?php esc_html_e('Post', 'aiseo'); ?></option>
                            <option value="page"><?php esc_html_e('Page', 'aiseo'); ?></option>
                        </select>
                        <button type="button" class="button button-primary aiseo-create-post-from-result" data-source="faq">
                            <span class="dashicons dashicons-plus"></span>
                            <?php esc_html_e('Create Post', 'aiseo'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    // Content Rewriter
    $('.aiseo-rewrite-btn').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-rewrite-form');
        var content = $form.find('[name="content"]').val();
        var mode = $form.find('[name="mode"]').val();
        
        if (!content) {
            $('#aiseo-rewrite-result').show().find('.aiseo-result-content').html('<p style="color:red;">Please enter content to rewrite</p>');
            return;
        }
        
        $btn.prop('disabled', true).text('Rewriting...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_rewrite_content',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>',
                content: content,
                mode: mode
            },
            success: function(response) {
                if (response.success) {
                    $('#aiseo-rewrite-result').show().find('.aiseo-result-content').html('<div style="background:#f0f0f0;padding:15px;border-radius:5px;">' + response.data + '</div>');
                } else {
                    $('#aiseo-rewrite-result').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + response.data + '</p>');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php esc_html_e('Rewrite Content', 'aiseo'); ?>');
            }
        });
    });
    
    // Content Suggestions
    $('.aiseo-suggestions-btn').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-suggestions-form');
        var topic = $form.find('[name="topic"]').val();
        
        if (!topic) {
            $('#aiseo-suggestions-result').show().find('.aiseo-result-list').html('<li style="color:red;">Please enter a topic</li>');
            return;
        }
        
        $btn.prop('disabled', true).text('Getting suggestions...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_content_suggestions',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>',
                topic: topic
            },
            success: function(response) {
                if (response.success) {
                    var html = '';
                    $.each(response.data, function(i, suggestion) {
                        html += '<li>' + suggestion + '</li>';
                    });
                    $('#aiseo-suggestions-result').show().find('.aiseo-result-list').html(html);
                } else {
                    $('#aiseo-suggestions-result').show().find('.aiseo-result-list').html('<li style="color:red;">Error: ' + response.data + '</li>');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e('Get Suggestions', 'aiseo'); ?>');
            }
        });
    });
    
    // Outline Generator
    $('.aiseo-outline-btn').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-outline-form');
        var topic = $form.find('[name="topic"]').val();
        var keyword = $form.find('[name="keyword"]').val();
        
        if (!topic) {
            $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<p style="color:red;">Please enter a topic</p>');
            return;
        }
        
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_outline',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>',
                topic: topic,
                keyword: keyword
            },
            success: function(response) {
                console.log('Outline Response:', response);
                if (response.success) {
                    var html = '';
                    if (typeof response.data === 'string') {
                        html = response.data;
                    } else if (typeof response.data === 'object') {
                        // Handle object response
                        if (response.data.outline) {
                            html = response.data.outline;
                        } else if (Array.isArray(response.data)) {
                            html = '<ul>';
                            $.each(response.data, function(i, item) {
                                html += '<li>' + (item.title || item.heading || item) + '</li>';
                            });
                            html += '</ul>';
                        } else {
                            html = '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';
                        }
                    }
                    $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<div style="background:#f0f0f0;padding:15px;border-radius:5px;">' + html + '</div>');
                } else {
                    $('#aiseo-outline-result').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + response.data + '</p>');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-list-view"></span> <?php esc_html_e('Generate Outline', 'aiseo'); ?>');
            }
        });
    });
    
    // FAQ Generator
    $('.aiseo-faq-btn').on('click', function() {
        var $btn = $(this);
        var $form = $('#aiseo-faq-form');
        var content = $form.find('[name="content"]').val();
        var count = $form.find('[name="count"]').val();
        
        if (!content) {
            $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<p style="color:red;">Please enter content</p>');
            return;
        }
        
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_faq',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>',
                content: content,
                count: count
            },
            success: function(response) {
                console.log('FAQ Response:', response);
                if (response.success) {
                    var html = '';
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        $.each(response.data, function(i, faq) {
                            var question = faq.question || faq.q || 'No question';
                            var answer = faq.answer || faq.a || 'No answer';
                            html += '<div class="faq-item" style="margin-bottom:15px;padding:10px;background:white;border-left:3px solid #0073aa;">';
                            html += '<strong>Q: ' + question + '</strong>';
                            html += '<p style="margin:5px 0 0 0;">A: ' + answer + '</p></div>';
                        });
                        $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<div style="background:#f0f0f0;padding:15px;border-radius:5px;">' + html + '</div>');
                    } else {
                        $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<p style="color:red;">Error: Invalid response format</p>');
                    }
                } else {
                    $('#aiseo-faq-result').show().find('.aiseo-result-content').html('<p style="color:red;">Error: ' + response.data + '</p>');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-editor-help"></span> <?php esc_html_e('Generate FAQs', 'aiseo'); ?>');
            }
        });
    });
    
    // Copy to clipboard
    $(document).on('click', '.aiseo-copy-result', function() {
        var $btn = $(this);
        var text = $btn.closest('.aiseo-result-box').find('.aiseo-result-content').text();
        navigator.clipboard.writeText(text).then(function() {
            var originalText = $btn.text();
            $btn.text('✓ Copied!').css('color', 'green');
            setTimeout(function() {
                $btn.text(originalText).css('color', '');
            }, 2000);
        });
    });
    
    // Create post from result
    $(document).on('click', '.aiseo-create-post-from-result', function() {
        var $btn = $(this);
        var $resultBox = $btn.closest('.aiseo-result-box');
        var content = $resultBox.find('.aiseo-result-content').html();
        var postType = $resultBox.find('.aiseo-post-type-select').val();
        var source = $btn.data('source');
        
        $btn.prop('disabled', true).text('Creating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout: 30000, // 30 second timeout
            data: {
                action: 'aiseo_create_post',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>',
                topic: 'Generated from ' + source,
                content: content,
                post_type: postType,
                length: 'medium'
            },
            success: function(response) {
                console.log('Create Post Response:', response);
                if (response.success) {
                    $resultBox.prepend('<div class="notice notice-success" style="margin:10px 0;padding:10px;"><strong>Success!</strong> Post created! <a href="' + response.data.edit_url + '" target="_blank">Edit post</a></div>');
                    setTimeout(function() {
                        $resultBox.find('.notice-success').fadeOut();
                    }, 5000);
                } else {
                    $resultBox.prepend('<div class="notice notice-error" style="margin:10px 0;padding:10px;"><strong>Error:</strong> ' + response.data + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Create Post Error:', xhr, status, error);
                var errorMsg = 'Connection error';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out (30s). The post may still be creating in the background.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error (500). Check if the post was created anyway.';
                }
                $resultBox.prepend('<div class="notice notice-error" style="margin:10px 0;padding:10px;"><strong>Error:</strong> ' + errorMsg + '</div>');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-plus"></span> <?php esc_html_e('Create Post', 'aiseo'); ?>');
            }
        });
    });
});
</script>
