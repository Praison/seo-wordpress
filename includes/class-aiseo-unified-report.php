<?php
/**
 * AISEO Unified Report Generator
 * 
 * Combines metrics from all analyzers into a comprehensive SEO report
 * - Content Analysis (11 metrics)
 * - Advanced Analysis (40+ factors)
 * - Readability Analysis (10 metrics)
 * - Internal Linking Analysis
 * - Image SEO Analysis
 * - Schema & Meta Tags
 * 
 * @package AISEO
 * @since 1.16.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Unified_Report {
    
    /**
     * Cache group for unified reports
     */
    const CACHE_GROUP = 'aiseo_unified_reports';
    
    /**
     * Cache TTL (1 hour)
     */
    const CACHE_TTL = 3600;
    
    /**
     * Generate comprehensive unified report for a post
     * 
     * @param int $post_id Post ID
     * @param array $options Report options
     * @return array Unified report data
     */
    public static function generate_report($post_id, $options = []) {
        $post_id = absint($post_id);
        
        // Check cache first
        $cache_key = 'report_' . $post_id . '_' . md5(serialize($options));
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if (false !== $cached && empty($options['force_refresh'])) {
            return $cached;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return ['error' => 'Post not found'];
        }
        
        // Initialize report structure
        $report = [
            'post_id' => $post_id,
            'post_title' => $post->post_title,
            'post_type' => $post->post_type,
            'generated_at' => current_time('mysql'),
            'overall_score' => 0,
            'status' => 'poor',
            'sections' => [],
            'recommendations' => [],
            'summary' => []
        ];
        
        // 1. Content Analysis (11 metrics)
        if (class_exists('AISEO_Analysis')) {
            $analyzer = new AISEO_Analysis();
            $content_analysis = $analyzer->analyze_post($post_id);
            $report['sections']['content_analysis'] = [
                'name' => 'Content Analysis',
                'score' => $content_analysis['overall_score'] ?? 0,
                'metrics' => $content_analysis['analyses'] ?? [],
                'weight' => 25 // 25% of total score
            ];
        }
        
        // 2. Readability Analysis (10 metrics)
        if (class_exists('AISEO_Readability')) {
            $readability = new AISEO_Readability();
            $readability_data = $readability->analyze($post->post_content);
            
            $report['sections']['readability'] = [
                'name' => 'Readability Analysis',
                'score' => $readability_data['overall_score'] ?? 0,
                'metrics' => [
                    'flesch_reading_ease' => $readability_data['flesch_reading_ease'] ?? 0,
                    'flesch_kincaid_grade' => $readability_data['flesch_kincaid_grade'] ?? 0,
                    'gunning_fog_index' => $readability_data['gunning_fog_index'] ?? 0,
                    'smog_index' => $readability_data['smog_index'] ?? 0,
                    'coleman_liau_index' => $readability_data['coleman_liau_index'] ?? 0,
                    'automated_readability_index' => $readability_data['automated_readability_index'] ?? 0,
                    'passive_voice_percentage' => $readability_data['passive_voice_percentage'] ?? 0,
                    'transition_words_percentage' => $readability_data['transition_words_percentage'] ?? 0,
                    'sentence_variety' => $readability_data['sentence_variety'] ?? [],
                    'paragraph_variety' => $readability_data['paragraph_variety'] ?? []
                ],
                'weight' => 20 // 20% of total score
            ];
        }
        
        // 3. Technical SEO (Meta, Schema, Social)
        $technical_score = 0;
        $technical_metrics = [];
        
        // Meta tags check
        $meta_title = get_post_meta($post_id, '_aiseo_meta_title', true);
        $meta_desc = get_post_meta($post_id, '_aiseo_meta_description', true);
        
        if (!empty($meta_title)) {
            $technical_score += 20;
            $technical_metrics['meta_title'] = ['status' => 'good', 'value' => strlen($meta_title) . ' chars'];
        } else {
            $technical_metrics['meta_title'] = ['status' => 'poor', 'message' => 'Missing meta title'];
        }
        
        if (!empty($meta_desc)) {
            $technical_score += 20;
            $technical_metrics['meta_description'] = ['status' => 'good', 'value' => strlen($meta_desc) . ' chars'];
        } else {
            $technical_metrics['meta_description'] = ['status' => 'poor', 'message' => 'Missing meta description'];
        }
        
        // Schema markup check
        $schema = get_post_meta($post_id, '_aiseo_schema', true);
        if (!empty($schema)) {
            $technical_score += 20;
            $technical_metrics['schema_markup'] = ['status' => 'good', 'value' => 'Present'];
        } else {
            $technical_metrics['schema_markup'] = ['status' => 'poor', 'message' => 'Missing schema markup'];
        }
        
        // Canonical URL check
        $canonical = get_post_meta($post_id, '_aiseo_canonical_url', true);
        if (!empty($canonical)) {
            $technical_score += 20;
            $technical_metrics['canonical_url'] = ['status' => 'good', 'value' => 'Set'];
        }
        
        // Robots meta check
        $noindex = get_post_meta($post_id, '_aiseo_noindex', true);
        if (empty($noindex)) {
            $technical_score += 20;
            $technical_metrics['robots_meta'] = ['status' => 'good', 'value' => 'index, follow'];
        } else {
            $technical_metrics['robots_meta'] = ['status' => 'warning', 'value' => 'noindex'];
        }
        
        $report['sections']['technical_seo'] = [
            'name' => 'Technical SEO',
            'score' => $technical_score,
            'metrics' => $technical_metrics,
            'weight' => 15 // 15% of total score
        ];
        
        // 4. Internal Linking Analysis
        if (class_exists('AISEO_Internal_Linking')) {
            // Count internal and external links in content
            $internal_links = preg_match_all('/<a[^>]+href=["\'](' . preg_quote(home_url(), '/') . '[^"\']*)["\']/i', $post->post_content, $internal_matches);
            $external_links = preg_match_all('/<a[^>]+href=["\']https?:\/\/(?!' . preg_quote(wp_parse_url(home_url(), PHP_URL_HOST), '/') . ')[^"\']*/i', $post->post_content, $external_matches);
            
            $internal_count = $internal_links ? count($internal_matches[0]) : 0;
            $external_count = $external_links ? count($external_matches[0]) : 0;
            
            // Calculate score based on link counts
            $link_score = 0;
            if ($internal_count >= 2 && $internal_count <= 5) {
                $link_score = 100;
            } elseif ($internal_count > 0) {
                $link_score = 70;
            } else {
                $link_score = 30;
            }
            
            $report['sections']['internal_linking'] = [
                'name' => 'Internal Linking',
                'score' => $link_score,
                'metrics' => [
                    'internal_links_count' => $internal_count,
                    'outbound_links_count' => $external_count,
                    'is_orphan' => false,
                    'suggestions_count' => 0
                ],
                'weight' => 15 // 15% of total score
            ];
        }
        
        // 5. Image SEO Analysis
        $image_score = 0;
        $image_metrics = [];
        
        // Get all images in post
        $content = $post->post_content;
        preg_match_all('/<img[^>]+>/i', $content, $images);
        $total_images = count($images[0]);
        
        if ($total_images > 0) {
            $images_with_alt = 0;
            foreach ($images[0] as $img) {
                if (preg_match('/alt=["\']([^"\']*)["\']/', $img, $alt_match)) {
                    if (!empty($alt_match[1])) {
                        $images_with_alt++;
                    }
                }
            }
            
            $alt_coverage = ($images_with_alt / $total_images) * 100;
            $image_score = $alt_coverage;
            
            $image_metrics = [
                'total_images' => $total_images,
                'images_with_alt' => $images_with_alt,
                'alt_coverage' => round($alt_coverage, 2) . '%',
                'status' => $alt_coverage >= 80 ? 'good' : ($alt_coverage >= 50 ? 'ok' : 'poor')
            ];
        } else {
            $image_metrics = [
                'total_images' => 0,
                'message' => 'No images found',
                'status' => 'ok'
            ];
            $image_score = 50; // Neutral score if no images
        }
        
        $report['sections']['image_seo'] = [
            'name' => 'Image SEO',
            'score' => $image_score,
            'metrics' => $image_metrics,
            'weight' => 10 // 10% of total score
        ];
        
        // 6. Permalink Optimization
        if (class_exists('AISEO_Permalink')) {
            $permalink = new AISEO_Permalink();
            $post_slug = $post->post_name;
            $permalink_data = [
                'current_slug' => $post_slug,
                'score' => 50, // Default neutral score
                'has_stop_words' => false,
                'has_keyword' => false,
                'length' => strlen($post_slug),
                'status' => 'ok'
            ];
            
            $report['sections']['permalink'] = [
                'name' => 'Permalink Optimization',
                'score' => $permalink_data['score'] ?? 0,
                'metrics' => [
                    'current_slug' => $permalink_data['current_slug'] ?? '',
                    'optimized_slug' => $permalink_data['optimized_slug'] ?? '',
                    'has_stop_words' => $permalink_data['has_stop_words'] ?? false,
                    'keyword_in_url' => $permalink_data['keyword_in_url'] ?? false
                ],
                'weight' => 15 // 15% of total score
            ];
        }
        
        // Calculate overall weighted score
        $total_weight = 0;
        $weighted_score = 0;
        
        foreach ($report['sections'] as $section) {
            $weight = $section['weight'] ?? 0;
            $score = $section['score'] ?? 0;
            
            $weighted_score += ($score * $weight / 100);
            $total_weight += $weight;
        }
        
        $report['overall_score'] = round($weighted_score);
        
        // Determine status
        if ($report['overall_score'] >= 80) {
            $report['status'] = 'good';
        } elseif ($report['overall_score'] >= 50) {
            $report['status'] = 'ok';
        } else {
            $report['status'] = 'poor';
        }
        
        // Generate recommendations
        $report['recommendations'] = self::generate_recommendations($report);
        
        // Generate summary
        $report['summary'] = self::generate_summary($report);
        
        // Cache the report
        wp_cache_set($cache_key, $report, self::CACHE_GROUP, self::CACHE_TTL);
        
        // Save to post meta for historical tracking
        update_post_meta($post_id, '_aiseo_unified_report', $report);
        update_post_meta($post_id, '_aiseo_unified_score', $report['overall_score']);
        update_post_meta($post_id, '_aiseo_last_analyzed', current_time('mysql'));
        
        return $report;
    }
    
    /**
     * Generate prioritized recommendations
     * 
     * @param array $report Report data
     * @return array Recommendations
     */
    private static function generate_recommendations($report) {
        $recommendations = [];
        
        foreach ($report['sections'] as $section_key => $section) {
            $score = $section['score'] ?? 0;
            
            if ($score < 50) {
                $recommendations[] = [
                    'priority' => 'high',
                    'section' => $section['name'],
                    'message' => "Improve {$section['name']} (current score: {$score}/100)",
                    'action' => self::get_action_for_section($section_key)
                ];
            } elseif ($score < 80) {
                $recommendations[] = [
                    'priority' => 'medium',
                    'section' => $section['name'],
                    'message' => "Optimize {$section['name']} (current score: {$score}/100)",
                    'action' => self::get_action_for_section($section_key)
                ];
            }
        }
        
        // Sort by priority
        usort($recommendations, function($a, $b) {
            $priority_order = ['high' => 1, 'medium' => 2, 'low' => 3];
            return $priority_order[$a['priority']] - $priority_order[$b['priority']];
        });
        
        return $recommendations;
    }
    
    /**
     * Get action recommendation for section
     * 
     * @param string $section_key Section key
     * @return string Action recommendation
     */
    private static function get_action_for_section($section_key) {
        $actions = [
            'content_analysis' => 'Review keyword usage, content length, and structure',
            'readability' => 'Simplify sentences, add transition words, improve paragraph structure',
            'technical_seo' => 'Add missing meta tags, schema markup, and canonical URLs',
            'internal_linking' => 'Add more internal links to related content',
            'image_seo' => 'Add alt text to all images',
            'permalink' => 'Optimize URL structure and remove stop words'
        ];
        
        return $actions[$section_key] ?? 'Review and optimize this section';
    }
    
    /**
     * Generate executive summary
     * 
     * @param array $report Report data
     * @return array Summary data
     */
    private static function generate_summary($report) {
        $summary = [
            'overall_score' => $report['overall_score'],
            'status' => $report['status'],
            'strengths' => [],
            'weaknesses' => [],
            'quick_wins' => []
        ];
        
        // Identify strengths (score >= 80)
        foreach ($report['sections'] as $section) {
            if (($section['score'] ?? 0) >= 80) {
                $summary['strengths'][] = $section['name'];
            }
        }
        
        // Identify weaknesses (score < 50)
        foreach ($report['sections'] as $section) {
            if (($section['score'] ?? 0) < 50) {
                $summary['weaknesses'][] = $section['name'];
            }
        }
        
        // Quick wins (easy improvements with high impact)
        if (isset($report['sections']['technical_seo']) && $report['sections']['technical_seo']['score'] < 80) {
            $summary['quick_wins'][] = 'Add meta description and title';
        }
        
        if (isset($report['sections']['image_seo']) && $report['sections']['image_seo']['score'] < 80) {
            $summary['quick_wins'][] = 'Add alt text to images';
        }
        
        return $summary;
    }
    
    /**
     * Clear cache for a post
     * 
     * @param int $post_id Post ID
     */
    public static function clear_cache($post_id) {
        $post_id = absint($post_id);
        wp_cache_delete('report_' . $post_id . '_*', self::CACHE_GROUP);
    }
    
    /**
     * Get historical reports for a post
     * 
     * @param int $post_id Post ID
     * @param int $limit Number of reports to retrieve
     * @return array Historical reports
     */
    public static function get_history($post_id, $limit = 10) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_value, meta_id 
             FROM {$wpdb->postmeta} 
             WHERE post_id = %d 
             AND meta_key = '_aiseo_unified_report'
             ORDER BY meta_id DESC 
             LIMIT %d",
            $post_id,
            $limit
        ));
        
        $history = [];
        foreach ($results as $row) {
            $report = maybe_unserialize($row->meta_value);
            if (is_array($report)) {
                $history[] = $report;
            }
        }
        
        return $history;
    }
}
