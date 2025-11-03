<?php
/**
 * AISEO Schema Markup Generator
 *
 * Generates JSON-LD structured data for better search engine understanding
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Schema {
    
    /**
     * Generate schema markup for a post
     *
     * @param int $post_id Post ID
     * @param string $schema_type Schema type (auto, article, blogposting, webpage)
     * @return array|false Schema markup array or false on failure
     */
    public function generate_schema($post_id, $schema_type = 'auto') {
        $post = get_post($post_id);
        
        if (!$post) {
            return false;
        }
        
        // Auto-detect schema type if needed
        if ($schema_type === 'auto') {
            $schema_type = $this->detect_schema_type($post);
        }
        
        // Generate base schema
        $schema = array(
            '@context' => 'https://schema.org',
        );
        
        // Generate specific schema based on type
        switch ($schema_type) {
            case 'article':
                $schema = array_merge($schema, $this->generate_article_schema($post));
                break;
            case 'blogposting':
                $schema = array_merge($schema, $this->generate_blogposting_schema($post));
                break;
            case 'webpage':
                $schema = array_merge($schema, $this->generate_webpage_schema($post));
                break;
            default:
                $schema = array_merge($schema, $this->generate_article_schema($post));
        }
        
        // Add organization schema
        $schema['publisher'] = $this->generate_organization_schema();
        
        // Add breadcrumb schema if applicable
        $breadcrumb = $this->generate_breadcrumb_schema($post);
        
        // Return as array of schemas
        return array(
            'main' => $schema,
            'breadcrumb' => $breadcrumb,
        );
    }
    
    /**
     * Detect appropriate schema type for post
     *
     * @param WP_Post $post Post object
     * @return string Schema type
     */
    private function detect_schema_type($post) {
        // Check post type
        if ($post->post_type === 'page') {
            return 'webpage';
        }
        
        // Check if it's a blog post
        if ($post->post_type === 'post') {
            // Check word count - longer posts are articles
            $word_count = str_word_count(wp_strip_all_tags($post->post_content));
            
            if ($word_count > 1000) {
                return 'article';
            } else {
                return 'blogposting';
            }
        }
        
        return 'article';
    }
    
    /**
     * Generate Article schema
     *
     * @param WP_Post $post Post object
     * @return array Schema data
     */
    private function generate_article_schema($post) {
        $schema = array(
            '@type' => 'Article',
            'headline' => $this->get_seo_title($post),
            'description' => $this->get_seo_description($post),
            'url' => get_permalink($post->ID),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
            'author' => $this->generate_author_schema($post),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink($post->ID),
            ),
        );
        
        // Add image if available
        $image = $this->get_post_image($post->ID);
        if ($image) {
            $schema['image'] = $image;
        }
        
        // Add word count
        $word_count = str_word_count(strip_tags($post->post_content));
        $schema['wordCount'] = $word_count;
        
        // Add article section (category)
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $schema['articleSection'] = $categories[0]->name;
        }
        
        return $schema;
    }
    
    /**
     * Generate BlogPosting schema
     *
     * @param WP_Post $post Post object
     * @return array Schema data
     */
    private function generate_blogposting_schema($post) {
        $schema = array(
            '@type' => 'BlogPosting',
            'headline' => $this->get_seo_title($post),
            'description' => $this->get_seo_description($post),
            'url' => get_permalink($post->ID),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
            'author' => $this->generate_author_schema($post),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink($post->ID),
            ),
        );
        
        // Add image if available
        $image = $this->get_post_image($post->ID);
        if ($image) {
            $schema['image'] = $image;
        }
        
        // Add word count
        $word_count = str_word_count(strip_tags($post->post_content));
        $schema['wordCount'] = $word_count;
        
        return $schema;
    }
    
    /**
     * Generate WebPage schema
     *
     * @param WP_Post $post Post object
     * @return array Schema data
     */
    private function generate_webpage_schema($post) {
        $schema = array(
            '@type' => 'WebPage',
            'name' => $this->get_seo_title($post),
            'description' => $this->get_seo_description($post),
            'url' => get_permalink($post->ID),
            'datePublished' => get_the_date('c', $post->ID),
            'dateModified' => get_the_modified_date('c', $post->ID),
        );
        
        // Add image if available
        $image = $this->get_post_image($post->ID);
        if ($image) {
            $schema['image'] = $image;
        }
        
        return $schema;
    }
    
    /**
     * Generate Organization schema
     *
     * @return array Schema data
     */
    private function generate_organization_schema() {
        $schema = array(
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'url' => get_site_url(),
        );
        
        // Add logo if available
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            if ($logo) {
                $schema['logo'] = array(
                    '@type' => 'ImageObject',
                    'url' => $logo[0],
                    'width' => $logo[1],
                    'height' => $logo[2],
                );
            }
        }
        
        return $schema;
    }
    
    /**
     * Generate Author/Person schema
     *
     * @param WP_Post $post Post object
     * @return array Schema data
     */
    private function generate_author_schema($post) {
        $author_id = $post->post_author;
        $author = get_userdata($author_id);
        
        if (!$author) {
            return array(
                '@type' => 'Person',
                'name' => 'Unknown',
            );
        }
        
        $schema = array(
            '@type' => 'Person',
            'name' => $author->display_name,
            'url' => get_author_posts_url($author_id),
        );
        
        // Add author description if available
        $description = get_user_meta($author_id, 'description', true);
        if ($description) {
            $schema['description'] = $description;
        }
        
        return $schema;
    }
    
    /**
     * Generate BreadcrumbList schema
     *
     * @param WP_Post $post Post object
     * @return array|false Schema data or false
     */
    private function generate_breadcrumb_schema($post) {
        $breadcrumbs = array();
        
        // Home
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => get_site_url(),
        );
        
        $position = 2;
        
        // Add categories for posts
        if ($post->post_type === 'post') {
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $category = $categories[0];
                $breadcrumbs[] = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $category->name,
                    'item' => get_category_link($category->term_id),
                );
            }
        }
        
        // Add parent pages
        if ($post->post_parent) {
            $parent_id = $post->post_parent;
            $parents = array();
            
            while ($parent_id) {
                $parent = get_post($parent_id);
                if ($parent) {
                    $parents[] = $parent;
                    $parent_id = $parent->post_parent;
                } else {
                    break;
                }
            }
            
            // Reverse to get correct order
            $parents = array_reverse($parents);
            
            foreach ($parents as $parent) {
                $breadcrumbs[] = array(
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $parent->post_title,
                    'item' => get_permalink($parent->ID),
                );
            }
        }
        
        // Current page
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $post->post_title,
            'item' => get_permalink($post->ID),
        );
        
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs,
        );
    }
    
    /**
     * Generate FAQ schema
     *
     * @param array $faqs Array of FAQ items (question => answer)
     * @return array Schema data
     */
    public function generate_faq_schema($faqs) {
        if (empty($faqs)) {
            return false;
        }
        
        $questions = array();
        
        foreach ($faqs as $question => $answer) {
            $questions[] = array(
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $answer,
                ),
            );
        }
        
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions,
        );
    }
    
    /**
     * Generate HowTo schema
     *
     * @param string $name How-to name
     * @param array $steps Array of steps
     * @param array $options Additional options
     * @return array Schema data
     */
    public function generate_howto_schema($name, $steps, $options = array()) {
        if (empty($steps)) {
            return false;
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $name,
        );
        
        // Add description if provided
        if (isset($options['description'])) {
            $schema['description'] = $options['description'];
        }
        
        // Add total time if provided
        if (isset($options['total_time'])) {
            $schema['totalTime'] = $options['total_time']; // ISO 8601 duration format
        }
        
        // Add steps
        $step_items = array();
        $position = 1;
        
        foreach ($steps as $step) {
            $step_item = array(
                '@type' => 'HowToStep',
                'position' => $position++,
                'name' => $step['name'],
                'text' => $step['text'],
            );
            
            // Add image if provided
            if (isset($step['image'])) {
                $step_item['image'] = $step['image'];
            }
            
            $step_items[] = $step_item;
        }
        
        $schema['step'] = $step_items;
        
        return $schema;
    }
    
    /**
     * Get SEO title for post
     *
     * @param WP_Post $post Post object
     * @return string SEO title
     */
    private function get_seo_title($post) {
        // Check for custom SEO title
        $seo_title = get_post_meta($post->ID, '_aiseo_meta_title', true);
        
        if (!empty($seo_title)) {
            return $seo_title;
        }
        
        // Fallback to post title
        return $post->post_title;
    }
    
    /**
     * Get SEO description for post
     *
     * @param WP_Post $post Post object
     * @return string SEO description
     */
    private function get_seo_description($post) {
        // Check for custom SEO description
        $seo_description = get_post_meta($post->ID, '_aiseo_meta_description', true);
        
        if (!empty($seo_description)) {
            return $seo_description;
        }
        
        // Fallback to excerpt or content
        if (!empty($post->post_excerpt)) {
            return $post->post_excerpt;
        }
        
        return AISEO_Helpers::truncate_text($post->post_content, 160);
    }
    
    /**
     * Get post featured image
     *
     * @param int $post_id Post ID
     * @return array|false Image data or false
     */
    private function get_post_image($post_id) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        
        if (!$thumbnail_id) {
            return false;
        }
        
        $image = wp_get_attachment_image_src($thumbnail_id, 'full');
        
        if (!$image) {
            return false;
        }
        
        return array(
            '@type' => 'ImageObject',
            'url' => $image[0],
            'width' => $image[1],
            'height' => $image[2],
        );
    }
    
    /**
     * Output schema markup as JSON-LD
     *
     * @param array $schema Schema data
     * @return string JSON-LD script tag
     */
    public function output_schema($schema) {
        if (empty($schema)) {
            return '';
        }
        
        $output = '<script type="application/ld+json">' . "\n";
        $output .= wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $output .= "\n" . '</script>' . "\n";
        
        return $output;
    }
    
    /**
     * Get schema markup for post (for display/testing)
     *
     * @param int $post_id Post ID
     * @param string $schema_type Schema type
     * @return string JSON string
     */
    public function get_schema_json($post_id, $schema_type = 'auto') {
        $schema = $this->generate_schema($post_id, $schema_type);
        
        if (!$schema) {
            return '';
        }
        
        return wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
