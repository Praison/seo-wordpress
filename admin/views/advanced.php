<?php
/**
 * Advanced Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;

$post_types = get_post_types(array('public' => true), 'objects');
?>

<div class="aiseo-advanced">
    
    <!-- Custom Post Types -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Custom Post Types', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Enable SEO for Custom Post Types', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Select which post types should have SEO features:', 'aiseo'); ?></p>
                <form method="post">
                    <?php wp_nonce_field('aiseo_cpt_settings'); ?>
                    <?php foreach ($post_types as $post_type): ?>
                        <div class="aiseo-form-group">
                            <label>
                                <input type="checkbox" name="aiseo_cpt[]" value="<?php echo esc_attr($post_type->name); ?>" checked>
                                <strong><?php echo esc_html($post_type->label); ?></strong>
                                <span style="color: #666;">(<?php echo esc_html($post_type->name); ?>)</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="button button-primary aiseo-mt-20">
                        <?php esc_html_e('Save Post Type Settings', 'aiseo'); ?>
                    </button>
                </form>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo cpt list</code><br>
                <code>wp aiseo cpt generate --post-type=product --all</code></p>
            </div>
        </div>
    </div>
    
    <!-- Multilingual Support -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Multilingual SEO', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Multilingual Plugin Support', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('AISEO automatically detects and supports:', 'aiseo'); ?></p>
                <div class="aiseo-stat-grid">
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('WPML', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('Polylang', 'aiseo'); ?></div>
                    </div>
                    <div class="aiseo-stat-item">
                        <div class="aiseo-stat-label"><?php esc_html_e('TranslatePress', 'aiseo'); ?></div>
                    </div>
                </div>
                <ul style="list-style: disc; padding-left: 20px; margin-top: 20px;">
                    <li><?php esc_html_e('Automatic language detection', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Per-language meta tags', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Hreflang tag generation', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Language-specific sitemaps', 'aiseo'); ?></li>
                </ul>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo multilingual status</code><br>
                <code>wp aiseo multilingual sync</code></p>
            </div>
        </div>
    </div>
    
    <!-- Unified Reports -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Unified Reports', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Generate SEO Reports', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Generate comprehensive SEO reports combining:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Content analysis results', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Readability scores', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Advanced SEO factors', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Historical data', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Recommendations', 'aiseo'); ?></li>
                </ul>
                <div class="aiseo-button-group aiseo-mt-20">
                    <button type="button" class="button button-primary" id="aiseo-generate-report">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php esc_html_e('Generate Full Report', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary" id="aiseo-download-pdf">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Export Report (PDF)', 'aiseo'); ?>
                    </button>
                </div>
                <div id="aiseo-report-results" style="margin-top:20px;"></div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo report generate --format=json</code><br>
                <code>wp aiseo report export report.pdf</code></p>
            </div>
        </div>
    </div>
    
    <!-- Keyword Research -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Keyword Research', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Research Keywords', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('AI-powered keyword research and suggestions:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Keyword suggestions', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Search volume estimates', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Difficulty scores', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Related keywords', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Long-tail variations', 'aiseo'); ?></li>
                </ul>
                <div class="aiseo-form-group aiseo-mt-20">
                    <input type="text" id="aiseo-keyword-input" class="large-text" placeholder="<?php esc_attr_e('Enter a keyword...', 'aiseo'); ?>">
                    <button type="button" class="button button-primary" style="margin-top: 10px;">
                        <span class="dashicons dashicons-search"></span>
                        <?php esc_html_e('Research Keyword', 'aiseo'); ?>
                    </button>
                </div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo keyword research "wordpress seo"</code><br>
                <code>wp aiseo keyword suggestions "content marketing"</code></p>
                <div class="aiseo-alert aiseo-alert-info aiseo-mt-20">
                    <?php esc_html_e('Note: Requires third-party API integration for volume/difficulty data', 'aiseo'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Briefs -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Content Briefs', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('AI Content Briefs', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Generate detailed content briefs with:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Target keywords', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Recommended word count', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Content structure', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Key topics to cover', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Competitor insights', 'aiseo'); ?></li>
                </ul>
                <div id="aiseo-variations-results" class="aiseo-result-box" style="display:none;">
                    <h4><?php esc_html_e('Variations (with AI scores):', 'aiseo'); ?></h4>
                    <div class="aiseo-result-content"></div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
jQuery(document).ready(function($) {
    // CPT settings form
    $('.aiseo-advanced form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        
        $btn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=aiseo_save_cpt_settings&nonce=<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>',
            success: function(response) {
                if (response.success) {
                    alert('Settings saved successfully!');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php esc_html_e('Save Post Type Settings', 'aiseo'); ?>');
            }
        });
    });
    
    // Generate report
    $('.aiseo-advanced button:contains("Generate Full Report")').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_report',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var report = response.data;
                    var html = '<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:5px;">';
                    html += '<h3 style="margin-top:0;">SEO Report</h3>';
                    html += '<div class="aiseo-stat-grid">';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.generated_at + '</div><div class="aiseo-stat-label">Generated</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.posts_analyzed + '</div><div class="aiseo-stat-label">Posts Analyzed</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.metadata_generated + '</div><div class="aiseo-stat-label">Metadata Generated</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.ai_posts_created + '</div><div class="aiseo-stat-label">AI Posts Created</div></div>';
                    html += '<div class="aiseo-stat-item"><div class="aiseo-stat-value">' + report.api_requests + '</div><div class="aiseo-stat-label">API Requests</div></div>';
                    html += '</div>';
                    
                    if (report.recent_scores) {
                        html += '<h4>Recent Post Scores:</h4><table class="wp-list-table widefat fixed striped"><thead><tr><th>Post Title</th><th>SEO Score</th></tr></thead><tbody>';
                        $.each(report.recent_scores, function(i, item) {
                            html += '<tr><td>' + item.title + '</td><td>' + item.score + '</td></tr>';
                        });
                        html += '</tbody></table>';
                    }
                    html += '</div>';
                    
                    $('#aiseo-report-content').html(html);
                    $('#aiseo-report-section').show();
                    $('html, body').animate({scrollTop: $('#aiseo-report-section').offset().top - 100}, 500);
                    
                    // Store report data for PDF
                    $('#aiseo-download-pdf').data('report', report);
                } else {
                    $('#aiseo-report-content').html('<p style="color:red;">Error: ' + response.data + '</p>');
                    $('#aiseo-report-section').show();
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e('Generate Full Report', 'aiseo'); ?>');
            }
        });
    });
    // Keyword research
    $('.aiseo-advanced button:contains("Research Keyword")').on('click', function() {
        var $btn = $(this);
        var keyword = $('#aiseo-keyword-input').val();
        var $resultDiv = $('<div class="aiseo-result-box" style="margin-top:20px;"></div>').insertAfter($btn.parent());
        
        if (!keyword) {
            $resultDiv.html('<p style="color:red;">Please enter a keyword</p>').show();
            return;
        }
        
        $btn.prop('disabled', true).text('Researching...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_keyword_research',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>',
                keyword: keyword
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div style="background:#f0f0f0;padding:15px;border-radius:5px;"><h4>Keyword Suggestions for: ' + response.data.keyword + '</h4><ul style="list-style:disc;padding-left:20px;">';
                    $.each(response.data.suggestions, function(i, kw) {
                        html += '<li>' + kw + '</li>';
                    });
                    html += '</ul></div>';
                    $resultDiv.html(html).show();
                } else {
                    $resultDiv.html('<p style="color:red;">Error: ' + response.data + '</p>').show();
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-search"></span> <?php esc_html_e('Research Keyword', 'aiseo'); ?>');
            }
        });
    });
    
    // Generate report
    $('#aiseo-generate-report').on('click', function() {
        var $btn = $(this);
        var $resultDiv = $('#aiseo-report-results');
        
        $btn.prop('disabled', true).text('Generating...');
        $resultDiv.html('');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aiseo_generate_report',
                nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div style="background:#f0f0f0;padding:20px;border-radius:5px;">';
                    html += '<h3>SEO Report Generated</h3>';
                    html += '<table class="wp-list-table widefat"><tr><th>Metric</th><th>Value</th></tr>';
                    html += '<tr><td>Posts Analyzed</td><td>' + response.data.posts_analyzed + '</td></tr>';
                    html += '<tr><td>Metadata Generated</td><td>' + response.data.metadata_generated + '</td></tr>';
                    html += '<tr><td>AI Posts Created</td><td>' + response.data.ai_posts_created + '</td></tr>';
                    html += '<tr><td>API Requests</td><td>' + response.data.api_requests + '</td></tr></table>';
                    html += '</div>';
                    $resultDiv.html(html).show();
                    
                    // Store report data for PDF download
                    $('#aiseo-download-pdf').data('report', response.data);
                    $('#aiseo-download-pdf').prop('disabled', false);
                } else {
                    $resultDiv.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + response.data + '</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Report generation error:', xhr, status, error);
                $resultDiv.html('<div class="notice notice-error" style="padding:10px;"><strong>Error:</strong> ' + error + '</div>').show();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e('Generate Full Report', 'aiseo'); ?>');
            }
        });
    });
    
    // PDF Download
    $('#aiseo-download-pdf').on('click', function() {
        var report = $(this).data('report');
        if (!report) {
            $(this).after('<p style="color:red;margin-top:10px;">No report data available</p>');
            return;
        }
        
        // Create PDF-style HTML content
        var pdfContent = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>AISEO Report</title>';
        pdfContent += '<style>body{font-family:Arial,sans-serif;padding:40px;}h1{color:#0073aa;}table{width:100%;border-collapse:collapse;margin:20px 0;}th,td{padding:10px;border:1px solid #ddd;text-align:left;}th{background:#0073aa;color:white;}</style>';
        pdfContent += '</head><body>';
        pdfContent += '<h1>AISEO SEO Report</h1>';
        pdfContent += '<p><strong>Generated:</strong> ' + report.generated_at + '</p>';
        pdfContent += '<table><tr><th>Metric</th><th>Value</th></tr>';
        pdfContent += '<tr><td>Posts Analyzed</td><td>' + report.posts_analyzed + '</td></tr>';
        pdfContent += '<tr><td>Metadata Generated</td><td>' + report.metadata_generated + '</td></tr>';
        pdfContent += '<tr><td>AI Posts Created</td><td>' + report.ai_posts_created + '</td></tr>';
        pdfContent += '<tr><td>API Requests</td><td>' + report.api_requests + '</td></tr></table>';
        
        if (report.recent_scores) {
            pdfContent += '<h2>Recent Post Scores</h2><table><tr><th>Post Title</th><th>SEO Score</th></tr>';
            $.each(report.recent_scores, function(i, item) {
                pdfContent += '<tr><td>' + item.title + '</td><td>' + item.score + '</td></tr>';
            });
            pdfContent += '</table>';
        }
        
        pdfContent += '</body></html>';
        
        // Create blob and download
        var blob = new Blob([pdfContent], {type: 'text/html'});
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'aiseo-report-' + Date.now() + '.html';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        $(this).after('<p style="color:green;margin-top:10px;">✓ Report downloaded! (Open in browser and print to PDF)</p>').next().delay(3000).fadeOut();
    });
});
</script>
