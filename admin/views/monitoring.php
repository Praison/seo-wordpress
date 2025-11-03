<?php
/**
 * Monitoring Tab View
 *
 * @package AISEO
 * @since 1.0.0
 */

if (!defined('ABSPATH')) exit;
?>

<div class="aiseo-monitoring">
    
    <!-- Rank Tracking -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Rank Tracking', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Track Keyword Rankings', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Monitor your keyword rankings over time:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Track multiple keywords', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Historical ranking data', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Position changes tracking', 'aiseo'); ?></li>
                    <li><?php esc_html_e('SERP feature detection', 'aiseo'); ?></li>
                </ul>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo rank-tracker add "keyword" --url="/page"</code><br>
                <code>wp aiseo rank-tracker check</code><br>
                <code>wp aiseo rank-tracker list</code></p>
                <div class="aiseo-alert aiseo-alert-info aiseo-mt-20">
                    <?php esc_html_e('Note: Requires third-party API integration (Google Search Console, SEMrush, etc.)', 'aiseo'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Backlink Monitor -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Backlink Monitoring', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Monitor Backlinks', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Track your site\'s backlink profile:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('New backlinks detection', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Lost backlinks alerts', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Domain authority tracking', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Anchor text analysis', 'aiseo'); ?></li>
                </ul>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo backlink check --url="https://example.com"</code><br>
                <code>wp aiseo backlink bulk-check urls.txt</code></p>
                <div class="aiseo-alert aiseo-alert-info aiseo-mt-20">
                    <?php esc_html_e('Note: Requires third-party API integration (Ahrefs, Moz, etc.)', 'aiseo'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 404 Monitor -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('404 Error Monitor', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('404 Error Log', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Monitor and fix 404 errors:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Automatic 404 detection', 'aiseo'); ?></li>
                    <li><?php esc_html_e('AI-powered redirect suggestions', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Hit count tracking', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Referrer information', 'aiseo'); ?></li>
                </ul>
                <div class="aiseo-button-group aiseo-mt-20">
                    <button type="button" class="button button-secondary">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e('View 404 Log', 'aiseo'); ?>
                    </button>
                    <button type="button" class="button button-secondary">
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php esc_html_e('Auto-Fix Common Errors', 'aiseo'); ?>
                    </button>
                </div>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo 404 list</code><br>
                <code>wp aiseo 404 suggest-redirects</code></p>
            </div>
        </div>
    </div>
    
    <!-- Competitor Analysis -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Competitor Analysis', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-card-header">
                <h3 class="aiseo-card-title"><?php esc_html_e('Analyze Competitors', 'aiseo'); ?></h3>
            </div>
            <div class="aiseo-card-body">
                <p><?php esc_html_e('Compare your site with competitors:', 'aiseo'); ?></p>
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('Keyword gap analysis', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Content comparison', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Backlink comparison', 'aiseo'); ?></li>
                    <li><?php esc_html_e('Traffic estimates', 'aiseo'); ?></li>
                </ul>
                <p class="aiseo-mt-20"><strong><?php esc_html_e('CLI Commands:', 'aiseo'); ?></strong><br>
                <code>wp aiseo competitor analyze "https://competitor.com"</code><br>
                <code>wp aiseo competitor compare --format=json</code></p>
                <div class="aiseo-alert aiseo-alert-info aiseo-mt-20">
                    <?php esc_html_e('Note: Requires third-party API integration (SEMrush, Ahrefs, etc.)', 'aiseo'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Overview -->
    <div class="aiseo-form-section">
        <h2 class="aiseo-section-title"><?php esc_html_e('Monitoring Statistics', 'aiseo'); ?></h2>
        
        <div class="aiseo-card">
            <div class="aiseo-stat-grid">
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value">0</div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Keywords Tracked', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value">0</div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Backlinks Found', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value">0</div>
                    <div class="aiseo-stat-label"><?php esc_html_e('404 Errors', 'aiseo'); ?></div>
                </div>
                <div class="aiseo-stat-item">
                    <div class="aiseo-stat-value">0</div>
                    <div class="aiseo-stat-label"><?php esc_html_e('Competitors', 'aiseo'); ?></div>
                </div>
            </div>
        </div>
    </div>
    
</div>
