<?php
/**
 * AISEO Global Robots Settings
 *
 * Handles global robots meta settings for different page types
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Robots {
    
    /**
     * Option key for robots settings
     */
    const OPTION_KEY = 'aiseo_robots_settings';
    
    /**
     * Default settings
     */
    private $defaults = array(
        // Archive types
        'noindex_categories' => false,
        'noindex_tags' => false,
        'noindex_author_archives' => false,
        'noindex_date_archives' => true,
        'noindex_search_results' => true,
        'noindex_paginated' => false,
        
        // NoFollow settings
        'nofollow_categories' => false,
        'nofollow_tags' => false,
        'nofollow_author_archives' => false,
        'nofollow_date_archives' => false,
        'nofollow_external_links' => false,
        
        // Other settings
        'noindex_empty_taxonomies' => true,
        'noindex_attachment_pages' => true,
        'add_noodp' => false,
        'add_noydir' => false,
    );
    
    /**
     * Initialize robots settings
     */
    public function init() {
        // Output robots meta tag
        add_action('wp_head', array($this, 'output_robots_meta'), 1);
        
        // Filter external links
        add_filter('the_content', array($this, 'filter_external_links'), 99);
        add_filter('comment_text', array($this, 'filter_external_links'), 99);
    }
    
    /**
     * Get all settings
     *
     * @return array Settings
     */
    public function get_settings() {
        $settings = get_option(self::OPTION_KEY, array());
        return wp_parse_args($settings, $this->defaults);
    }
    
    /**
     * Update settings
     *
     * @param array $settings Settings to update
     * @return bool Success
     */
    public function update_settings($settings) {
        $current = $this->get_settings();
        
        // Sanitize and merge
        foreach ($settings as $key => $value) {
            if (array_key_exists($key, $this->defaults)) {
                $current[$key] = (bool) $value;
            }
        }
        
        return update_option(self::OPTION_KEY, $current);
    }
    
    /**
     * Output robots meta tag
     */
    public function output_robots_meta() {
        // Skip if singular (handled by per-post settings)
        if (is_singular() && !is_attachment()) {
            return;
        }
        
        $settings = $this->get_settings();
        $robots = array();
        
        // Check page type and apply settings
        if (is_category()) {
            if ($settings['noindex_categories']) {
                $robots[] = 'noindex';
            }
            if ($settings['nofollow_categories']) {
                $robots[] = 'nofollow';
            }
            
            // Check for empty taxonomy
            if ($settings['noindex_empty_taxonomies']) {
                $term = get_queried_object();
                if ($term && $term->count === 0) {
                    $robots[] = 'noindex';
                }
            }
        }
        
        if (is_tag()) {
            if ($settings['noindex_tags']) {
                $robots[] = 'noindex';
            }
            if ($settings['nofollow_tags']) {
                $robots[] = 'nofollow';
            }
            
            // Check for empty taxonomy
            if ($settings['noindex_empty_taxonomies']) {
                $term = get_queried_object();
                if ($term && $term->count === 0) {
                    $robots[] = 'noindex';
                }
            }
        }
        
        if (is_author()) {
            if ($settings['noindex_author_archives']) {
                $robots[] = 'noindex';
            }
            if ($settings['nofollow_author_archives']) {
                $robots[] = 'nofollow';
            }
        }
        
        if (is_date()) {
            if ($settings['noindex_date_archives']) {
                $robots[] = 'noindex';
            }
            if ($settings['nofollow_date_archives']) {
                $robots[] = 'nofollow';
            }
        }
        
        if (is_search()) {
            if ($settings['noindex_search_results']) {
                $robots[] = 'noindex';
            }
        }
        
        if (is_attachment()) {
            if ($settings['noindex_attachment_pages']) {
                $robots[] = 'noindex';
            }
        }
        
        // Check for paginated pages
        if (is_paged() && $settings['noindex_paginated']) {
            $robots[] = 'noindex';
        }
        
        // Add deprecated directives if enabled
        if ($settings['add_noodp']) {
            $robots[] = 'noodp';
        }
        if ($settings['add_noydir']) {
            $robots[] = 'noydir';
        }
        
        // Remove duplicates and output
        $robots = array_unique($robots);
        
        if (!empty($robots)) {
            // Add index/follow defaults if not set
            if (!in_array('noindex', $robots)) {
                array_unshift($robots, 'index');
            }
            if (!in_array('nofollow', $robots)) {
                $robots[] = 'follow';
            }
            
            echo '<meta name="robots" content="' . esc_attr(implode(', ', $robots)) . '">' . "\n";
        }
    }
    
    /**
     * Filter external links to add nofollow
     *
     * @param string $content Content
     * @return string Modified content
     */
    public function filter_external_links($content) {
        $settings = $this->get_settings();
        
        if (!$settings['nofollow_external_links']) {
            return $content;
        }
        
        // Get site URL for comparison
        $site_url = home_url();
        $site_host = wp_parse_url($site_url, PHP_URL_HOST);
        
        // Find all links
        $pattern = '/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>/i';
        
        $content = preg_replace_callback($pattern, function($matches) use ($site_host) {
            $full_tag = $matches[0];
            $attributes = $matches[1];
            $href = $matches[2];
            
            // Parse the URL
            $link_host = wp_parse_url($href, PHP_URL_HOST);
            
            // Skip internal links and anchors
            if (empty($link_host) || $link_host === $site_host) {
                return $full_tag;
            }
            
            // Skip if already has rel attribute with nofollow
            if (preg_match('/rel=["\'][^"\']*nofollow[^"\']*["\']/', $attributes)) {
                return $full_tag;
            }
            
            // Add or modify rel attribute
            if (preg_match('/rel=["\']([^"\']*)["\']/', $attributes)) {
                // Append nofollow to existing rel
                $new_tag = preg_replace(
                    '/rel=["\']([^"\']*)["\']/',
                    'rel="$1 nofollow noopener"',
                    $full_tag
                );
            } else {
                // Add new rel attribute
                $new_tag = str_replace('<a ', '<a rel="nofollow noopener" ', $full_tag);
            }
            
            return $new_tag;
        }, $content);
        
        return $content;
    }
}
