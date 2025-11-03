<?php
/**
 * AISEO XML Sitemap Generator
 *
 * Generates and manages XML sitemaps for search engines
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Sitemap {
    
    /**
     * Sitemap cache key
     */
    const CACHE_KEY = 'aiseo_sitemap_cache';
    
    /**
     * Cache duration (12 hours)
     */
    const CACHE_DURATION = 43200;
    
    /**
     * Initialize sitemap generator
     */
    public function init() {
        // Add rewrite rules for sitemap
        add_action('init', array($this, 'add_rewrite_rules'));
        
        // Handle sitemap requests
        add_action('template_redirect', array($this, 'handle_sitemap_request'));
        
        // Clear cache on post save/delete
        add_action('save_post', array($this, 'clear_cache'));
        add_action('delete_post', array($this, 'clear_cache'));
        
        // Add sitemap to robots.txt
        add_filter('robots_txt', array($this, 'add_sitemap_to_robots'), 10, 2);
    }
    
    /**
     * Add rewrite rules for sitemap
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?aiseo_sitemap=1', 'top');
        add_rewrite_rule('^sitemap-([^/]+)\.xml$', 'index.php?aiseo_sitemap=$matches[1]', 'top');
        
        // Add query vars
        add_filter('query_vars', function($vars) {
            $vars[] = 'aiseo_sitemap';
            return $vars;
        });
    }
    
    /**
     * Handle sitemap requests
     */
    public function handle_sitemap_request() {
        $sitemap = get_query_var('aiseo_sitemap');
        
        if (empty($sitemap)) {
            return;
        }
        
        // Generate sitemap
        if ($sitemap === '1' || $sitemap === 'index') {
            $this->output_sitemap_index();
        } else {
            $this->output_sitemap($sitemap);
        }
        
        exit;
    }
    
    /**
     * Output sitemap index
     */
    private function output_sitemap_index() {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Get enabled post types
        $post_types = $this->get_enabled_post_types();
        
        foreach ($post_types as $post_type) {
            $lastmod = $this->get_post_type_lastmod($post_type);
            
            echo "\t<sitemap>\n";
            echo "\t\t<loc>" . esc_url(home_url("/sitemap-{$post_type}.xml")) . "</loc>\n";
            if ($lastmod) {
                echo "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
            }
            echo "\t</sitemap>\n";
        }
        
        echo '</sitemapindex>';
    }
    
    /**
     * Output sitemap for specific post type
     *
     * @param string $post_type Post type
     */
    private function output_sitemap($post_type) {
        // Check cache
        $cache_key = self::CACHE_KEY . '_' . $post_type;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            header('Content-Type: application/xml; charset=utf-8');
            header('X-Robots-Tag: noindex, follow');
            echo $cached;
            return;
        }
        
        // Generate sitemap
        $sitemap = $this->generate_sitemap($post_type);
        
        // Cache it
        set_transient($cache_key, $sitemap, self::CACHE_DURATION);
        
        // Output
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex, follow');
        echo $sitemap;
    }
    
    /**
     * Generate sitemap XML for post type
     *
     * @param string $post_type Post type
     * @return string Sitemap XML
     */
    public function generate_sitemap($post_type) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        
        // Get posts
        $posts = $this->get_posts_for_sitemap($post_type);
        
        foreach ($posts as $post) {
            $xml .= $this->generate_url_entry($post);
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Generate URL entry for post
     *
     * @param WP_Post $post Post object
     * @return string URL entry XML
     */
    private function generate_url_entry($post) {
        $xml = "\t<url>\n";
        
        // Location
        $xml .= "\t\t<loc>" . esc_url(get_permalink($post->ID)) . "</loc>\n";
        
        // Last modified
        $lastmod = get_post_modified_time('c', false, $post);
        $xml .= "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
        
        // Change frequency
        $changefreq = $this->get_changefreq($post);
        $xml .= "\t\t<changefreq>" . esc_html($changefreq) . "</changefreq>\n";
        
        // Priority
        $priority = $this->get_priority($post);
        $xml .= "\t\t<priority>" . esc_html($priority) . "</priority>\n";
        
        // Images
        $images = $this->get_post_images($post);
        foreach ($images as $image) {
            $xml .= "\t\t<image:image>\n";
            $xml .= "\t\t\t<image:loc>" . esc_url($image['url']) . "</image:loc>\n";
            if (!empty($image['title'])) {
                $xml .= "\t\t\t<image:title>" . esc_html($image['title']) . "</image:title>\n";
            }
            if (!empty($image['caption'])) {
                $xml .= "\t\t\t<image:caption>" . esc_html($image['caption']) . "</image:caption>\n";
            }
            $xml .= "\t\t</image:image>\n";
        }
        
        $xml .= "\t</url>\n";
        
        return $xml;
    }
    
    /**
     * Get posts for sitemap
     *
     * @param string $post_type Post type
     * @return array Posts
     */
    private function get_posts_for_sitemap($post_type) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );
        
        // Exclude noindex posts
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => '_aiseo_noindex',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key' => '_aiseo_noindex',
                'value' => '0',
            ),
        );
        
        $query = new WP_Query($args);
        
        return $query->posts;
    }
    
    /**
     * Get change frequency for post
     *
     * @param WP_Post $post Post object
     * @return string Change frequency
     */
    private function get_changefreq($post) {
        // Check for custom changefreq
        $custom = get_post_meta($post->ID, '_aiseo_sitemap_changefreq', true);
        
        if (!empty($custom)) {
            return $custom;
        }
        
        // Calculate based on post age
        $post_age_days = (time() - strtotime($post->post_date)) / DAY_IN_SECONDS;
        
        if ($post_age_days < 7) {
            return 'daily';
        } elseif ($post_age_days < 30) {
            return 'weekly';
        } elseif ($post_age_days < 365) {
            return 'monthly';
        } else {
            return 'yearly';
        }
    }
    
    /**
     * Get priority for post
     *
     * @param WP_Post $post Post object
     * @return string Priority (0.0 - 1.0)
     */
    private function get_priority($post) {
        // Check for custom priority
        $custom = get_post_meta($post->ID, '_aiseo_sitemap_priority', true);
        
        if (!empty($custom)) {
            return $custom;
        }
        
        // Calculate based on post type and homepage
        if ($post->post_type === 'page') {
            // Homepage gets highest priority
            if (get_option('page_on_front') == $post->ID) {
                return '1.0';
            }
            return '0.8';
        }
        
        // Posts get medium-high priority
        if ($post->post_type === 'post') {
            return '0.6';
        }
        
        // Other post types get medium priority
        return '0.5';
    }
    
    /**
     * Get images from post
     *
     * @param WP_Post $post Post object
     * @return array Images
     */
    private function get_post_images($post) {
        $images = array();
        
        // Featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'full');
            $image_title = get_the_title($thumbnail_id);
            $image_caption = wp_get_attachment_caption($thumbnail_id);
            
            if ($image_url) {
                $images[] = array(
                    'url' => $image_url,
                    'title' => $image_title,
                    'caption' => $image_caption,
                );
            }
        }
        
        // Images from content (limit to 5 total)
        if (count($images) < 5) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches);
            
            if (!empty($matches[1])) {
                foreach (array_slice($matches[1], 0, 5 - count($images)) as $img_url) {
                    $images[] = array(
                        'url' => $img_url,
                        'title' => '',
                        'caption' => '',
                    );
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Get enabled post types for sitemap
     *
     * @return array Post types
     */
    private function get_enabled_post_types() {
        $default_types = array('post', 'page');
        $enabled_types = get_option('aiseo_sitemap_post_types', $default_types);
        
        // Filter out non-public post types
        $public_types = get_post_types(array('public' => true));
        
        return array_intersect($enabled_types, $public_types);
    }
    
    /**
     * Get last modified date for post type
     *
     * @param string $post_type Post type
     * @return string|false Last modified date or false
     */
    private function get_post_type_lastmod($post_type) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'fields' => 'ids',
        );
        
        $query = new WP_Query($args);
        
        if (!empty($query->posts)) {
            $post = get_post($query->posts[0]);
            return get_post_modified_time('c', false, $post);
        }
        
        return false;
    }
    
    /**
     * Clear sitemap cache
     */
    public function clear_cache() {
        $post_types = $this->get_enabled_post_types();
        
        foreach ($post_types as $post_type) {
            delete_transient(self::CACHE_KEY . '_' . $post_type);
        }
    }
    
    /**
     * Add sitemap to robots.txt
     *
     * @param string $output Robots.txt output
     * @param bool $public Whether site is public
     * @return string Modified output
     */
    public function add_sitemap_to_robots($output, $public) {
        if ($public) {
            $output .= "\n# AISEO Sitemap\n";
            $output .= "Sitemap: " . home_url('/sitemap.xml') . "\n";
        }
        
        return $output;
    }
    
    /**
     * Ping search engines about sitemap update
     */
    public function ping_search_engines() {
        $sitemap_url = urlencode(home_url('/sitemap.xml'));
        
        // Google
        wp_remote_get("https://www.google.com/ping?sitemap={$sitemap_url}");
        
        // Bing
        wp_remote_get("https://www.bing.com/ping?sitemap={$sitemap_url}");
    }
    
    /**
     * Get sitemap statistics
     *
     * @return array Statistics
     */
    public function get_sitemap_stats() {
        $stats = array(
            'post_types' => array(),
            'total_urls' => 0,
            'last_generated' => '',
        );
        
        $post_types = $this->get_enabled_post_types();
        
        foreach ($post_types as $post_type) {
            $posts = $this->get_posts_for_sitemap($post_type);
            $count = count($posts);
            
            $stats['post_types'][$post_type] = $count;
            $stats['total_urls'] += $count;
        }
        
        // Check if cached
        $cache_key = self::CACHE_KEY . '_' . $post_types[0];
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            $stats['last_generated'] = 'Cached';
        } else {
            $stats['last_generated'] = 'Not cached';
        }
        
        return $stats;
    }
}
