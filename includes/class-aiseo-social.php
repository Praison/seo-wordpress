<?php
/**
 * AISEO Social Media Tags Handler
 *
 * Generates Open Graph and Twitter Card meta tags for social media sharing
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Social {
    
    /**
     * Initialize social media tags handler
     */
    public function init() {
        // Hook into wp_head to inject social media tags
        add_action('wp_head', array($this, 'inject_social_tags'), 5);
    }
    
    /**
     * Inject all social media tags into head
     */
    public function inject_social_tags() {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Output Open Graph tags
        $this->output_open_graph_tags($post);
        
        // Output Twitter Card tags
        $this->output_twitter_card_tags($post);
    }
    
    /**
     * Output Open Graph tags
     *
     * @param WP_Post $post Post object
     */
    private function output_open_graph_tags($post) {
        echo "\n<!-- Open Graph Meta Tags -->\n";
        
        // og:type
        $og_type = $this->get_og_type($post);
        echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
        
        // og:title
        $og_title = $this->get_og_title($post);
        echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
        
        // og:description
        $og_description = $this->get_og_description($post);
        if (!empty($og_description)) {
            echo '<meta property="og:description" content="' . esc_attr($og_description) . '">' . "\n";
        }
        
        // og:url
        $og_url = get_permalink($post->ID);
        echo '<meta property="og:url" content="' . esc_url($og_url) . '">' . "\n";
        
        // og:site_name
        $site_name = get_bloginfo('name');
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
        
        // og:image
        $og_image = $this->get_og_image($post);
        if ($og_image) {
            echo '<meta property="og:image" content="' . esc_url($og_image['url']) . '">' . "\n";
            
            if (isset($og_image['width'])) {
                echo '<meta property="og:image:width" content="' . esc_attr($og_image['width']) . '">' . "\n";
            }
            
            if (isset($og_image['height'])) {
                echo '<meta property="og:image:height" content="' . esc_attr($og_image['height']) . '">' . "\n";
            }
            
            if (isset($og_image['alt'])) {
                echo '<meta property="og:image:alt" content="' . esc_attr($og_image['alt']) . '">' . "\n";
            }
        }
        
        // og:locale
        $locale = get_locale();
        echo '<meta property="og:locale" content="' . esc_attr($locale) . '">' . "\n";
        
        // Article-specific tags
        if ($og_type === 'article') {
            // article:published_time
            echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $post->ID)) . '">' . "\n";
            
            // article:modified_time
            echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $post->ID)) . '">' . "\n";
            
            // article:author
            $author_id = $post->post_author;
            $author = get_userdata($author_id);
            if ($author) {
                echo '<meta property="article:author" content="' . esc_attr($author->display_name) . '">' . "\n";
            }
            
            // article:section (category)
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                echo '<meta property="article:section" content="' . esc_attr($categories[0]->name) . '">' . "\n";
            }
            
            // article:tag (tags)
            $tags = get_the_tags($post->ID);
            if ($tags) {
                foreach (array_slice($tags, 0, 5) as $tag) {
                    echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
                }
            }
        }
    }
    
    /**
     * Output Twitter Card tags
     *
     * @param WP_Post $post Post object
     */
    private function output_twitter_card_tags($post) {
        echo "\n<!-- Twitter Card Meta Tags -->\n";
        
        // twitter:card
        $card_type = $this->get_twitter_card_type($post);
        echo '<meta name="twitter:card" content="' . esc_attr($card_type) . '">' . "\n";
        
        // twitter:title
        $twitter_title = $this->get_twitter_title($post);
        echo '<meta name="twitter:title" content="' . esc_attr($twitter_title) . '">' . "\n";
        
        // twitter:description
        $twitter_description = $this->get_twitter_description($post);
        if (!empty($twitter_description)) {
            echo '<meta name="twitter:description" content="' . esc_attr($twitter_description) . '">' . "\n";
        }
        
        // twitter:image
        $twitter_image = $this->get_twitter_image($post);
        if ($twitter_image) {
            echo '<meta name="twitter:image" content="' . esc_url($twitter_image['url']) . '">' . "\n";
            
            if (isset($twitter_image['alt'])) {
                echo '<meta name="twitter:image:alt" content="' . esc_attr($twitter_image['alt']) . '">' . "\n";
            }
        }
        
        // twitter:site (optional - site's Twitter handle)
        $twitter_site = get_option('aiseo_twitter_site');
        if (!empty($twitter_site)) {
            echo '<meta name="twitter:site" content="' . esc_attr($twitter_site) . '">' . "\n";
        }
        
        // twitter:creator (author's Twitter handle)
        $author_id = $post->post_author;
        $twitter_creator = get_user_meta($author_id, 'twitter', true);
        if (!empty($twitter_creator)) {
            // Ensure it starts with @
            if (strpos($twitter_creator, '@') !== 0) {
                $twitter_creator = '@' . $twitter_creator;
            }
            echo '<meta name="twitter:creator" content="' . esc_attr($twitter_creator) . '">' . "\n";
        }
    }
    
    /**
     * Get Open Graph type
     *
     * @param WP_Post $post Post object
     * @return string OG type
     */
    private function get_og_type($post) {
        // Check for custom OG type
        $custom_type = get_post_meta($post->ID, '_aiseo_og_type', true);
        
        if (!empty($custom_type)) {
            return $custom_type;
        }
        
        // Default based on post type
        if ($post->post_type === 'page') {
            return 'website';
        }
        
        return 'article';
    }
    
    /**
     * Get Open Graph title
     *
     * @param WP_Post $post Post object
     * @return string OG title
     */
    private function get_og_title($post) {
        // Check for custom OG title
        $custom_title = get_post_meta($post->ID, '_aiseo_og_title', true);
        
        if (!empty($custom_title)) {
            return $custom_title;
        }
        
        // Use SEO title if available
        $seo_title = get_post_meta($post->ID, '_aiseo_meta_title', true);
        
        if (!empty($seo_title)) {
            return $seo_title;
        }
        
        // Fallback to post title
        return $post->post_title;
    }
    
    /**
     * Get Open Graph description
     *
     * @param WP_Post $post Post object
     * @return string OG description
     */
    private function get_og_description($post) {
        // Check for custom OG description
        $custom_description = get_post_meta($post->ID, '_aiseo_og_description', true);
        
        if (!empty($custom_description)) {
            return $custom_description;
        }
        
        // Use SEO description if available
        $seo_description = get_post_meta($post->ID, '_aiseo_meta_description', true);
        
        if (!empty($seo_description)) {
            return $seo_description;
        }
        
        // Fallback to excerpt or content
        if (!empty($post->post_excerpt)) {
            return AISEO_Helpers::truncate_text($post->post_excerpt, 200);
        }
        
        return AISEO_Helpers::truncate_text($post->post_content, 200);
    }
    
    /**
     * Get Open Graph image
     *
     * @param WP_Post $post Post object
     * @return array|false Image data or false
     */
    private function get_og_image($post) {
        // Check for custom OG image
        $custom_image_id = get_post_meta($post->ID, '_aiseo_og_image', true);
        
        if ($custom_image_id) {
            return $this->get_image_data($custom_image_id);
        }
        
        // Use featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        
        if ($thumbnail_id) {
            return $this->get_image_data($thumbnail_id);
        }
        
        // Fallback to first image in content
        $first_image = $this->get_first_content_image($post->post_content);
        
        if ($first_image) {
            return $first_image;
        }
        
        // Fallback to default site image
        $default_image_id = get_option('aiseo_default_og_image');
        
        if ($default_image_id) {
            return $this->get_image_data($default_image_id);
        }
        
        return false;
    }
    
    /**
     * Get Twitter Card type
     *
     * @param WP_Post $post Post object
     * @return string Card type
     */
    private function get_twitter_card_type($post) {
        // Check for custom card type
        $custom_type = get_post_meta($post->ID, '_aiseo_twitter_card_type', true);
        
        if (!empty($custom_type)) {
            return $custom_type;
        }
        
        // Default to summary_large_image if there's a featured image
        if (has_post_thumbnail($post->ID)) {
            return 'summary_large_image';
        }
        
        return 'summary';
    }
    
    /**
     * Get Twitter title
     *
     * @param WP_Post $post Post object
     * @return string Twitter title
     */
    private function get_twitter_title($post) {
        // Check for custom Twitter title
        $custom_title = get_post_meta($post->ID, '_aiseo_twitter_title', true);
        
        if (!empty($custom_title)) {
            return $custom_title;
        }
        
        // Use OG title
        return $this->get_og_title($post);
    }
    
    /**
     * Get Twitter description
     *
     * @param WP_Post $post Post object
     * @return string Twitter description
     */
    private function get_twitter_description($post) {
        // Check for custom Twitter description
        $custom_description = get_post_meta($post->ID, '_aiseo_twitter_description', true);
        
        if (!empty($custom_description)) {
            return $custom_description;
        }
        
        // Use OG description
        return $this->get_og_description($post);
    }
    
    /**
     * Get Twitter image
     *
     * @param WP_Post $post Post object
     * @return array|false Image data or false
     */
    private function get_twitter_image($post) {
        // Check for custom Twitter image
        $custom_image_id = get_post_meta($post->ID, '_aiseo_twitter_image', true);
        
        if ($custom_image_id) {
            return $this->get_image_data($custom_image_id);
        }
        
        // Use OG image
        return $this->get_og_image($post);
    }
    
    /**
     * Get image data from attachment ID
     *
     * @param int $attachment_id Attachment ID
     * @return array|false Image data or false
     */
    private function get_image_data($attachment_id) {
        $image = wp_get_attachment_image_src($attachment_id, 'full');
        
        if (!$image) {
            return false;
        }
        
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        
        return array(
            'url' => $image[0],
            'width' => $image[1],
            'height' => $image[2],
            'alt' => $alt_text ?: '',
        );
    }
    
    /**
     * Get first image from post content
     *
     * @param string $content Post content
     * @return array|false Image data or false
     */
    private function get_first_content_image($content) {
        // Match first img tag
        preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        
        if (empty($matches[1])) {
            return false;
        }
        
        // Get alt text if available
        preg_match('/<img[^>]+alt=["\']([^"\']*)["\'][^>]*>/i', $content, $alt_matches);
        $alt_text = !empty($alt_matches[1]) ? $alt_matches[1] : '';
        
        return array(
            'url' => $matches[1],
            'alt' => $alt_text,
        );
    }
    
    /**
     * Get all social media tags for a post (for testing/display)
     *
     * @param int $post_id Post ID
     * @return array Social media tags
     */
    public function get_social_tags($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            return array();
        }
        
        $og_image = $this->get_og_image($post);
        $twitter_image = $this->get_twitter_image($post);
        
        return array(
            'open_graph' => array(
                'og:type' => $this->get_og_type($post),
                'og:title' => $this->get_og_title($post),
                'og:description' => $this->get_og_description($post),
                'og:url' => get_permalink($post_id),
                'og:site_name' => get_bloginfo('name'),
                'og:image' => $og_image ? $og_image['url'] : '',
                'og:locale' => get_locale(),
            ),
            'twitter' => array(
                'twitter:card' => $this->get_twitter_card_type($post),
                'twitter:title' => $this->get_twitter_title($post),
                'twitter:description' => $this->get_twitter_description($post),
                'twitter:image' => $twitter_image ? $twitter_image['url'] : '',
                'twitter:site' => get_option('aiseo_twitter_site', ''),
            ),
        );
    }
}
