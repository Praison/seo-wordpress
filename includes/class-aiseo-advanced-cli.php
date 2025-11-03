<?php
/**
 * AISEO Advanced Analysis WP-CLI Commands
 *
 * @package AISEO
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Advanced SEO Analysis WP-CLI Commands
 */
class AISEO_Advanced_CLI {
    
    /**
     * Run comprehensive SEO analysis (40+ factors)
     *
     * ## OPTIONS
     *
     * <post-id>
     * : The post ID to analyze
     *
     * [--keyword=<keyword>]
     * : Focus keyword (optional, uses saved keyword if not provided)
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - summary
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo advanced analyze 123
     *     wp aiseo advanced analyze 123 --keyword="wordpress seo"
     *     wp aiseo advanced analyze 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function analyze($args, $assoc_args) {
        $post_id = absint($args[0]);
        $keyword = isset($assoc_args['keyword']) ? $assoc_args['keyword'] : '';
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error('Post not found.');
        }
        
        WP_CLI::log(sprintf('Analyzing post: %s (ID: %d)', $post->post_title, $post_id));
        WP_CLI::log('Running 40+ SEO factor analysis...');
        
        $advanced_analysis = new AISEO_Advanced_Analysis();
        $result = $advanced_analysis->analyze_comprehensive($post_id, $keyword);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($result, JSON_PRETTY_PRINT));
            return;
        }
        
        if ($format === 'summary') {
            $this->display_summary($result);
            return;
        }
        
        // Table format
        $this->display_table($result);
    }
    
    /**
     * Display results in table format
     */
    private function display_table($result) {
        WP_CLI::log('');
        WP_CLI::log('=== SEO Analysis Results ===');
        WP_CLI::log('');
        WP_CLI::log(sprintf('Overall Score: %d/%d (%d%%)', 
            $result['overall_score'], 
            $result['max_score'], 
            $result['percentage']
        ));
        WP_CLI::log(sprintf('Status: %s', strtoupper($result['status'])));
        WP_CLI::log(sprintf('Total Factors Analyzed: %d', $result['total_factors']));
        WP_CLI::log('');
        
        // Group analyses by category
        $categories = [
            'Content Quality' => ['keyword_in_url', 'keyword_in_meta_description', 'keyword_in_first_paragraph', 'keyword_in_subheadings', 'keyword_density', 'content_length'],
            'Readability' => ['flesch_reading_ease', 'passive_voice', 'transition_words', 'sentence_length', 'paragraph_length'],
            'Technical SEO' => ['title_tag_length', 'meta_description_length', 'url_length', 'image_alt_text', 'internal_links', 'external_links', 'schema_markup'],
            'User Experience' => ['table_of_contents', 'multimedia_content', 'list_formatting', 'call_to_action', 'reading_time']
        ];
        
        foreach ($categories as $category => $factors) {
            WP_CLI::log("--- $category ---");
            
            $category_data = [];
            foreach ($factors as $factor) {
                if (isset($result['analyses'][$factor])) {
                    $analysis = $result['analyses'][$factor];
                    $category_data[] = [
                        'Factor' => ucwords(str_replace('_', ' ', $factor)),
                        'Score' => sprintf('%d/%d', $analysis['score'], $analysis['max_score']),
                        'Status' => strtoupper($analysis['status']),
                        'Message' => $analysis['message']
                    ];
                }
            }
            
            if (!empty($category_data)) {
                WP_CLI\Utils\format_items('table', $category_data, ['Factor', 'Score', 'Status', 'Message']);
            }
            WP_CLI::log('');
        }
        
        // Show recommendations
        if (!empty($result['recommendations'])) {
            WP_CLI::log('=== Recommendations ===');
            WP_CLI::log('');
            
            $high_priority = array_filter($result['recommendations'], function($r) {
                return $r['priority'] === 'high';
            });
            
            if (!empty($high_priority)) {
                WP_CLI::warning(sprintf('High Priority Issues: %d', count($high_priority)));
                foreach ($high_priority as $rec) {
                    WP_CLI::log(sprintf('  - %s: %s', ucwords(str_replace('_', ' ', $rec['factor'])), $rec['message']));
                }
                WP_CLI::log('');
            }
        }
    }
    
    /**
     * Display summary format
     */
    private function display_summary($result) {
        WP_CLI::log('');
        WP_CLI::log('=== SEO Analysis Summary ===');
        WP_CLI::log('');
        
        $status_color = $result['status'] === 'good' ? '%G' : ($result['status'] === 'ok' ? '%Y' : '%R');
        WP_CLI::log(WP_CLI::colorize(sprintf(
            '%sOverall Score: %d%% (%s)%%n',
            $status_color,
            $result['percentage'],
            strtoupper($result['status'])
        )));
        
        WP_CLI::log('');
        WP_CLI::log(sprintf('Total Factors: %d', $result['total_factors']));
        WP_CLI::log(sprintf('Focus Keyword: %s', $result['focus_keyword'] ?: 'Not set'));
        
        // Count by status
        $good = $ok = $poor = 0;
        foreach ($result['analyses'] as $analysis) {
            if ($analysis['status'] === 'good') $good++;
            else if ($analysis['status'] === 'ok') $ok++;
            else if ($analysis['status'] === 'poor') $poor++;
        }
        
        WP_CLI::log('');
        WP_CLI::log('Factor Status:');
        WP_CLI::log(WP_CLI::colorize(sprintf('  %GGood: %d%%n', $good)));
        WP_CLI::log(WP_CLI::colorize(sprintf('  %YOK: %d%%n', $ok)));
        WP_CLI::log(WP_CLI::colorize(sprintf('  %RPoor: %d%%n', $poor)));
        
        if (!empty($result['recommendations'])) {
            WP_CLI::log('');
            WP_CLI::log(sprintf('Total Recommendations: %d', count($result['recommendations'])));
            
            $high = array_filter($result['recommendations'], function($r) { return $r['priority'] === 'high'; });
            if (!empty($high)) {
                WP_CLI::warning(sprintf('High Priority: %d', count($high)));
            }
        }
        
        WP_CLI::log('');
    }
    
    /**
     * Bulk analyze multiple posts
     *
     * ## OPTIONS
     *
     * [--post-type=<type>]
     * : Post type to analyze
     * ---
     * default: post
     * ---
     *
     * [--limit=<number>]
     * : Number of posts to analyze
     * ---
     * default: 10
     * ---
     *
     * [--min-score=<score>]
     * : Only show posts with score below this threshold
     * ---
     * default: 100
     * ---
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo advanced bulk --limit=20
     *     wp aiseo advanced bulk --min-score=70 --format=csv
     *     wp aiseo advanced bulk --post-type=page
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 10;
        $min_score = isset($assoc_args['min-score']) ? absint($assoc_args['min-score']) : 100;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => $limit,
            'post_status' => 'publish'
        ]);
        
        if (empty($posts)) {
            WP_CLI::warning('No posts found.');
            return;
        }
        
        WP_CLI::log(sprintf('Analyzing %d %s(s)...', count($posts), $post_type));
        
        $progress = \WP_CLI\Utils\make_progress_bar('Analyzing posts', count($posts));
        
        $results = [];
        $advanced_analysis = new AISEO_Advanced_Analysis();
        
        foreach ($posts as $post) {
            $result = $advanced_analysis->analyze_comprehensive($post->ID);
            
            if (!is_wp_error($result) && $result['percentage'] < $min_score) {
                $results[] = [
                    'ID' => $post->ID,
                    'Title' => $post->post_title,
                    'Score' => $result['percentage'],
                    'Status' => strtoupper($result['status']),
                    'Issues' => count($result['recommendations'])
                ];
            }
            
            $progress->tick();
        }
        
        $progress->finish();
        
        if (empty($results)) {
            WP_CLI::success(sprintf('All posts have scores above %d%%!', $min_score));
            return;
        }
        
        WP_CLI::log('');
        WP_CLI\Utils\format_items($format, $results, ['ID', 'Title', 'Score', 'Status', 'Issues']);
        WP_CLI::log('');
        WP_CLI::log(sprintf('Found %d posts below %d%% score threshold.', count($results), $min_score));
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo advanced', 'AISEO_Advanced_CLI');
