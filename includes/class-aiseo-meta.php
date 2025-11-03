<?php
/**
 * AISEO Meta Tags Handler
 *
 * Injects SEO meta tags into page <head>
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Meta {
    
    /**
     * Initialize meta tags handler
     */
    public function init() {
        // Hook into wp_head to inject meta tags
        add_action('wp_head', array($this, 'inject_meta_tags'), 1);
        
        // Filter document title
        add_filter('pre_get_document_title', array($this, 'filter_document_title'), 10, 1);
        add_filter('wp_title', array($this, 'filter_wp_title'), 10, 3);
        
        // Remove default WordPress meta tags that we'll replace
        remove_action('wp_head', '_wp_render_title_tag', 1);
        remove_action('wp_head', 'rel_canonical');
        remove_action('wp_head', 'noindex', 1);
    }
    
    /**
     * Inject all meta tags into head
     */
    public function inject_meta_tags() {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Output title tag
        $this->output_title_tag($post);
        
        // Output meta description
        $this->output_meta_description($post);
        
        // Output canonical URL
        $this->output_canonical($post);
        
        // Output robots meta
        $this->output_robots_meta($post);
        
        // Output additional meta tags
        $this->output_additional_meta($post);
    }
    
    /**
     * Output title tag
     *
     * @param WP_Post $post Post object
     */
    private function output_title_tag($post) {
        $title = $this->get_meta_title($post);
        
        if (empty($title)) {
            // Fallback to default WordPress title
            return;
        }
        
        echo '<title>' . esc_html($title) . '</title>' . "\n";
    }
    
    /**
     * Output meta description
     *
     * @param WP_Post $post Post object
     */
    private function output_meta_description($post) {
        $description = $this->get_meta_description($post);
        
        if (empty($description)) {
            return;
        }
        
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    }
    
    /**
     * Output canonical URL
     *
     * @param WP_Post $post Post object
     */
    private function output_canonical($post) {
        // Check if custom canonical is set
        $custom_canonical = get_post_meta($post->ID, '_aiseo_canonical_url', true);
        
        if (!empty($custom_canonical)) {
            $canonical = esc_url($custom_canonical);
        } else {
            $canonical = get_permalink($post->ID);
        }
        
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }
    
    /**
     * Output robots meta tag
     *
     * @param WP_Post $post Post object
     */
    private function output_robots_meta($post) {
        $robots = array();
        
        // Check for custom robots settings
        $noindex = get_post_meta($post->ID, '_aiseo_noindex', true);
        $nofollow = get_post_meta($post->ID, '_aiseo_nofollow', true);
        
        if ($noindex) {
            $robots[] = 'noindex';
        } else {
            $robots[] = 'index';
        }
        
        if ($nofollow) {
            $robots[] = 'nofollow';
        } else {
            $robots[] = 'follow';
        }
        
        // Additional robots directives
        $noarchive = get_post_meta($post->ID, '_aiseo_noarchive', true);
        $nosnippet = get_post_meta($post->ID, '_aiseo_nosnippet', true);
        
        if ($noarchive) {
            $robots[] = 'noarchive';
        }
        
        if ($nosnippet) {
            $robots[] = 'nosnippet';
        }
        
        if (!empty($robots)) {
            echo '<meta name="robots" content="' . esc_attr(implode(', ', $robots)) . '">' . "\n";
        }
    }
    
    /**
     * Output additional meta tags
     *
     * @param WP_Post $post Post object
     */
    private function output_additional_meta($post) {
        // Author meta tag
        $author_id = $post->post_author;
        $author = get_userdata($author_id);
        
        if ($author) {
            echo '<meta name="author" content="' . esc_attr($author->display_name) . '">' . "\n";
        }
        
        // Published time
        echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $post->ID)) . '">' . "\n";
        
        // Modified time
        echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $post->ID)) . '">' . "\n";
        
        // Keywords (if set)
        $keywords = get_post_meta($post->ID, '_aiseo_meta_keywords', true);
        if (!empty($keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }
        
        // Focus keyword as primary keyword
        $focus_keyword = get_post_meta($post->ID, '_aiseo_focus_keyword', true);
        if (!empty($focus_keyword)) {
            echo '<meta name="news_keywords" content="' . esc_attr($focus_keyword) . '">' . "\n";
        }
    }
    
    /**
     * Filter document title
     *
     * @param string $title Document title
     * @return string Filtered title
     */
    public function filter_document_title($title) {
        if (!is_singular()) {
            return $title;
        }
        
        global $post;
        
        if (!$post) {
            return $title;
        }
        
        $meta_title = $this->get_meta_title($post);
        
        if (!empty($meta_title)) {
            return $meta_title;
        }
        
        return $title;
    }
    
    /**
     * Filter wp_title
     *
     * @param string $title Title
     * @param string $sep Separator
     * @param string $seplocation Separator location
     * @return string Filtered title
     */
    public function filter_wp_title($title, $sep, $seplocation) {
        if (!is_singular()) {
            return $title;
        }
        
        global $post;
        
        if (!$post) {
            return $title;
        }
        
        $meta_title = $this->get_meta_title($post);
        
        if (!empty($meta_title)) {
            return $meta_title;
        }
        
        return $title;
    }
    
    /**
     * Get meta title for post
     *
     * @param WP_Post $post Post object
     * @return string Meta title
     */
    private function get_meta_title($post) {
        // Check for custom meta title
        $meta_title = get_post_meta($post->ID, '_aiseo_meta_title', true);
        
        if (!empty($meta_title)) {
            return $meta_title;
        }
        
        // Fallback to post title with site name
        $site_name = get_bloginfo('name');
        return $post->post_title . ' - ' . $site_name;
    }
    
    /**
     * Get meta description for post
     *
     * @param WP_Post $post Post object
     * @return string Meta description
     */
    private function get_meta_description($post) {
        // Check for custom meta description
        $meta_description = get_post_meta($post->ID, '_aiseo_meta_description', true);
        
        if (!empty($meta_description)) {
            return $meta_description;
        }
        
        // Fallback to excerpt
        if (!empty($post->post_excerpt)) {
            return AISEO_Helpers::truncate_text($post->post_excerpt, 160);
        }
        
        // Fallback to content
        return AISEO_Helpers::truncate_text($post->post_content, 160);
    }
    
    /**
     * Get all meta tags for a post (for testing/display)
     *
     * @param int $post_id Post ID
     * @return array Meta tags
     */
    public function get_meta_tags($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return array();
        }
        
        $meta_tags = array(
            'title' => $this->get_meta_title($post),
            'description' => $this->get_meta_description($post),
            'canonical' => get_post_meta($post_id, '_aiseo_canonical_url', true) ?: get_permalink($post_id),
            'robots' => $this->get_robots_directives($post_id),
            'keywords' => get_post_meta($post_id, '_aiseo_meta_keywords', true),
            'focus_keyword' => get_post_meta($post_id, '_aiseo_focus_keyword', true),
            'author' => get_the_author_meta('display_name', $post->post_author),
            'published_time' => get_the_date('c', $post_id),
            'modified_time' => get_the_modified_date('c', $post_id),
        );
        
        return array_filter($meta_tags); // Remove empty values
    }
    
    /**
     * Get robots directives for a post
     *
     * @param int $post_id Post ID
     * @return string Robots directives
     */
    private function get_robots_directives($post_id) {
        $robots = array();
        
        $noindex = get_post_meta($post_id, '_aiseo_noindex', true);
        $nofollow = get_post_meta($post_id, '_aiseo_nofollow', true);
        $noarchive = get_post_meta($post_id, '_aiseo_noarchive', true);
        $nosnippet = get_post_meta($post_id, '_aiseo_nosnippet', true);
        
        $robots[] = $noindex ? 'noindex' : 'index';
        $robots[] = $nofollow ? 'nofollow' : 'follow';
        
        if ($noarchive) {
            $robots[] = 'noarchive';
        }
        
        if ($nosnippet) {
            $robots[] = 'nosnippet';
        }
        
        return implode(', ', $robots);
    }
    
    /**
     * Update meta tags for a post
     *
     * @param int $post_id Post ID
     * @param array $meta_data Meta data to update
     * @return bool Success
     */
    public function update_meta_tags($post_id, $meta_data) {
        $updated = false;
        
        // Update meta title
        if (isset($meta_data['title'])) {
            update_post_meta($post_id, '_aiseo_meta_title', sanitize_text_field($meta_data['title']));
            $updated = true;
        }
        
        // Update meta description
        if (isset($meta_data['description'])) {
            update_post_meta($post_id, '_aiseo_meta_description', sanitize_textarea_field($meta_data['description']));
            $updated = true;
        }
        
        // Update canonical URL
        if (isset($meta_data['canonical'])) {
            update_post_meta($post_id, '_aiseo_canonical_url', esc_url_raw($meta_data['canonical']));
            $updated = true;
        }
        
        // Update robots directives
        if (isset($meta_data['noindex'])) {
            update_post_meta($post_id, '_aiseo_noindex', (bool) $meta_data['noindex']);
            $updated = true;
        }
        
        if (isset($meta_data['nofollow'])) {
            update_post_meta($post_id, '_aiseo_nofollow', (bool) $meta_data['nofollow']);
            $updated = true;
        }
        
        if (isset($meta_data['noarchive'])) {
            update_post_meta($post_id, '_aiseo_noarchive', (bool) $meta_data['noarchive']);
            $updated = true;
        }
        
        if (isset($meta_data['nosnippet'])) {
            update_post_meta($post_id, '_aiseo_nosnippet', (bool) $meta_data['nosnippet']);
            $updated = true;
        }
        
        // Update keywords
        if (isset($meta_data['keywords'])) {
            update_post_meta($post_id, '_aiseo_meta_keywords', sanitize_text_field($meta_data['keywords']));
            $updated = true;
        }
        
        return $updated;
    }
}
