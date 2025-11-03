/**
 * AISEO Metabox JavaScript
 * Handles AI generation buttons and content analysis
 */

(function($) {
    'use strict';
    
    // Wait for DOM ready
    $(document).ready(function() {
        console.log('AISEO Metabox: Script loaded');
        
        // Check if we're on the post editor
        if (!$('.aiseo-metabox').length) {
            console.log('AISEO Metabox: Not on post editor page');
            return;
        }
        
        // Character counter
        function updateCharCount(input, counter) {
            var count = input.val().length;
            counter.find('.aiseo-current-count').text(count);
            
            var maxChars = input.attr('maxlength') || 160;
            if (count > maxChars * 0.9) {
                counter.addClass('warning');
            } else {
                counter.removeClass('warning');
            }
        }
        
        // Initialize character counters
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
            $(this).toggleClass('open');
        });
        
        // Generate with AI button
        $('.aiseo-generate-btn').on('click', function(e) {
            e.preventDefault();
            
            var btn = $(this);
            var field = btn.data('field');
            var input = field === 'title' ? $('#aiseo_meta_title') : $('#aiseo_meta_description');
            var postId = $('#post_ID').val();
            
            if (!postId) {
                alert('Please save the post first before generating AI content.');
                return;
            }
            
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
                error: function(xhr, status, error) {
                    console.error('AISEO Generate Error:', error);
                    alert('Error: Failed to connect to server');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-generic"></span> Generate with AI');
                }
            });
        });
        
        // Analyze content button
        $('.aiseo-analyze-btn').on('click', function(e) {
            e.preventDefault();
            
            var btn = $(this);
            var postId = $('#post_ID').val();
            var resultsDiv = $('.aiseo-analysis-results');
            
            if (!postId) {
                alert('Please save the post first before analyzing content.');
                return;
            }
            
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
                        
                        // Map of analysis keys to readable labels
                        var labels = {
                            'keyword_density': 'Keyword Density',
                            'readability': 'Readability',
                            'paragraph_structure': 'Paragraph Structure',
                            'sentence_length': 'Sentence Length',
                            'content_length': 'Content Length',
                            'keyword_in_title': 'Keyword in Title',
                            'keyword_in_headings': 'Keyword in Headings',
                            'keyword_in_intro': 'Keyword in Introduction',
                            'internal_links': 'Internal Links',
                            'external_links': 'External Links',
                            'images': 'Images'
                        };
                        
                        $.each(response.data.analyses, function(key, analysis) {
                            var label = labels[key] || key;
                            var statusClass = analysis.status === 'good' ? 'green' : (analysis.status === 'warning' ? 'orange' : 'red');
                            html += '<li><strong>' + label + ':</strong> <span style="color:' + statusClass + '">' + analysis.score + '/100</span> - ' + analysis.message + '</li>';
                        });
                        html += '</ul>';
                        html += '<p><strong>Overall Score: ' + response.data.overall_score + '/100</strong></p>';
                        
                        resultsDiv.find('.aiseo-analysis-content').html(html);
                        resultsDiv.slideDown();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to analyze'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AISEO Analyze Error:', error);
                    alert('Error: Failed to connect to server');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<span class="dashicons dashicons-chart-bar"></span> Analyze Content');
                }
            });
        });
        
        // Preview SEO button
        $('.aiseo-preview-btn').on('click', function(e) {
            e.preventDefault();
            
            var title = $('#aiseo_meta_title').val() || $('.editor-post-title__input').val() || $('input[name="post_title"]').val() || 'Your Page Title';
            var description = $('#aiseo_meta_description').val() || 'Your meta description will appear here. Add a compelling description to improve click-through rates.';
            var url = $('#aiseo_canonical_url').val() || window.location.href;
            
            // Extract domain from URL
            var domain = url.replace(/^https?:\/\//, '').split('/')[0];
            
            // Create preview modal
            var modalHtml = '<div class="aiseo-preview-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 999999; display: flex; align-items: center; justify-content: center;">';
            modalHtml += '<div style="background: white; padding: 30px; border-radius: 8px; max-width: 600px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">';
            modalHtml += '<h2 style="margin-top: 0; margin-bottom: 20px; font-size: 18px;">SEO Preview - Google Search Result</h2>';
            modalHtml += '<div style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #fff;">';
            modalHtml += '<div style="color: #1a0dab; font-size: 20px; font-family: arial, sans-serif; line-height: 1.3; margin-bottom: 5px; cursor: pointer;">' + title + '</div>';
            modalHtml += '<div style="color: #006621; font-size: 14px; font-family: arial, sans-serif; margin-bottom: 5px;">' + domain + '</div>';
            modalHtml += '<div style="color: #545454; font-size: 14px; font-family: arial, sans-serif; line-height: 1.5;">' + description + '</div>';
            modalHtml += '</div>';
            modalHtml += '<div style="margin-top: 20px; text-align: right;">';
            modalHtml += '<button class="button button-primary aiseo-close-preview" style="margin-left: 10px;">Close</button>';
            modalHtml += '</div>';
            modalHtml += '</div>';
            modalHtml += '</div>';
            
            // Add modal to page
            $('body').append(modalHtml);
            
            // Close modal handler
            $('.aiseo-close-preview, .aiseo-preview-modal').on('click', function(e) {
                if (e.target === this) {
                    $('.aiseo-preview-modal').remove();
                }
            });
        });
        
        console.log('AISEO Metabox: All event handlers initialized');
    });
    
})(jQuery);
