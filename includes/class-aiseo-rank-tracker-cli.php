<?php
/**
 * AISEO Rank Tracking WP-CLI Commands
 *
 * @package AISEO
 * @since 1.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Rank Tracking WP-CLI Commands
 */
class AISEO_Rank_Tracker_CLI {
    
    /**
     * Track keyword rank
     *
     * ## OPTIONS
     *
     * <keyword>
     * : Keyword to track
     *
     * [--post-id=<id>]
     * : Post ID to associate with keyword
     *
     * [--location=<location>]
     * : Location code (e.g., US, UK, CA)
     * ---
     * default: US
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank track "seo tips"
     *     wp aiseo rank track "wordpress seo" --post-id=123
     *     wp aiseo rank track "local seo" --location=UK
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function track($args, $assoc_args) {
        $keyword = $args[0];
        $post_id = isset($assoc_args['post-id']) ? absint($assoc_args['post-id']) : 0;
        $location = isset($assoc_args['location']) ? $assoc_args['location'] : 'US';
        
        WP_CLI::log(sprintf('Tracking keyword: "%s"', $keyword));
        if ($post_id) {
            WP_CLI::log(sprintf('Post ID: %d', $post_id));
        }
        WP_CLI::log(sprintf('Location: %s', $location));
        WP_CLI::log('');
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->track_keyword($keyword, $post_id, $location);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Keyword tracked successfully!');
        WP_CLI::log('');
        WP_CLI::log(sprintf('Position: %d', $result['position']));
        WP_CLI::log(sprintf('URL: %s', $result['url']));
        WP_CLI::log(sprintf('Tracked at: %s', $result['tracked_at']));
        
        if (!empty($result['serp_features'])) {
            WP_CLI::log('');
            WP_CLI::log('SERP Features:');
            foreach ($result['serp_features'] as $feature) {
                WP_CLI::log('  • ' . $feature);
            }
        }
    }
    
    /**
     * Get position history for a keyword
     *
     * ## OPTIONS
     *
     * <keyword>
     * : Keyword to get history for
     *
     * [--days=<days>]
     * : Number of days to look back
     * ---
     * default: 30
     * ---
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank history "seo tips"
     *     wp aiseo rank history "wordpress seo" --days=60
     *     wp aiseo rank history "local seo" --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function history($args, $assoc_args) {
        $keyword = $args[0];
        $days = isset($assoc_args['days']) ? absint($assoc_args['days']) : 30;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log(sprintf('Getting position history for: "%s"', $keyword));
        WP_CLI::log(sprintf('Period: Last %d days', $days));
        WP_CLI::log('');
        
        $tracker = new AISEO_Rank_Tracker();
        $history = $tracker->get_position_history($keyword, $days);
        
        if (empty($history)) {
            WP_CLI::warning('No history found for this keyword');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($history));
            return;
        }
        
        WP_CLI::success(sprintf('Found %d tracking records', count($history)));
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($history, JSON_PRETTY_PRINT));
        } else {
            $display_data = [];
            foreach ($history as $record) {
                $display_data[] = [
                    'date' => $record['date'],
                    'position' => $record['position'],
                    'url' => $record['url'],
                    'location' => $record['location']
                ];
            }
            
            WP_CLI\Utils\format_items($format, $display_data, 
                ['date', 'position', 'url', 'location']);
        }
    }
    
    /**
     * Get ranking keywords for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Post ID
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank keywords 123
     *     wp aiseo rank keywords 456 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function keywords($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log(sprintf('Getting ranking keywords for post ID: %d', $post_id));
        WP_CLI::log('');
        
        $tracker = new AISEO_Rank_Tracker();
        $keywords = $tracker->get_ranking_keywords($post_id);
        
        if (empty($keywords)) {
            WP_CLI::warning('No keywords found for this post');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($keywords));
            return;
        }
        
        WP_CLI::success(sprintf('Found %d keywords', count($keywords)));
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($keywords, JSON_PRETTY_PRINT));
        } else {
            WP_CLI\Utils\format_items($format, $keywords, 
                ['keyword', 'current_position', 'last_checked']);
        }
    }
    
    /**
     * Compare ranking with competitor
     *
     * ## OPTIONS
     *
     * <keyword>
     * : Keyword to compare
     *
     * <competitor-url>
     * : Competitor URL
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank compare "seo tips" "https://competitor.com"
     *     wp aiseo rank compare "wordpress seo" "https://example.com" --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function compare($args, $assoc_args) {
        $keyword = $args[0];
        $competitor_url = $args[1];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log(sprintf('Comparing rankings for: "%s"', $keyword));
        WP_CLI::log(sprintf('Competitor: %s', $competitor_url));
        WP_CLI::log('');
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->compare_with_competitor($keyword, $competitor_url);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Comparison complete!');
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Ranking Comparison:');
            WP_CLI::log(sprintf('  Our Position: %s', $result['our_position'] ?: 'Not ranked'));
            WP_CLI::log(sprintf('  Competitor Position: %d', $result['competitor_position']));
            WP_CLI::log(sprintf('  Difference: %+d', $result['difference']));
            WP_CLI::log(sprintf('  Status: %s', ucfirst($result['status'])));
            
            if ($result['status'] === 'ahead') {
                WP_CLI::log('  ✓ We are ranking higher!');
            } elseif ($result['status'] === 'behind') {
                WP_CLI::log('  ⚠ Competitor is ranking higher');
            } else {
                WP_CLI::log('  = Same position');
            }
        }
    }
    
    /**
     * Detect SERP features for a keyword
     *
     * ## OPTIONS
     *
     * <keyword>
     * : Keyword to analyze
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank serp-features "seo tips"
     *     wp aiseo rank serp-features "wordpress seo" --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function serp_features($args, $assoc_args) {
        $keyword = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log(sprintf('Analyzing SERP features for: "%s"', $keyword));
        WP_CLI::log('');
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->detect_serp_features($keyword);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Analysis complete!');
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            if (!empty($result['features'])) {
                WP_CLI::log('SERP Features:');
                foreach ($result['features'] as $feature) {
                    WP_CLI::log('  • ' . $feature);
                }
            }
            
            if (!empty($result['opportunities'])) {
                WP_CLI::log('');
                WP_CLI::log('Opportunities:');
                foreach ($result['opportunities'] as $opportunity) {
                    WP_CLI::log('  ✓ ' . $opportunity);
                }
            }
            
            if (isset($result['difficulty'])) {
                WP_CLI::log('');
                WP_CLI::log(sprintf('Difficulty: %s', ucfirst($result['difficulty'])));
            }
        }
    }
    
    /**
     * List all tracked keywords
     *
     * ## OPTIONS
     *
     * [--post-id=<id>]
     * : Filter by post ID
     *
     * [--location=<location>]
     * : Filter by location
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank list
     *     wp aiseo rank list --post-id=123
     *     wp aiseo rank list --location=US --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function list($args, $assoc_args) {
        $post_id = isset($assoc_args['post-id']) ? absint($assoc_args['post-id']) : 0;
        $location = isset($assoc_args['location']) ? $assoc_args['location'] : '';
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $filters = [];
        if ($post_id) {
            $filters['post_id'] = $post_id;
        }
        if ($location) {
            $filters['location'] = $location;
        }
        
        WP_CLI::log('Getting tracked keywords...');
        WP_CLI::log('');
        
        $tracker = new AISEO_Rank_Tracker();
        $keywords = $tracker->get_all_keywords($filters);
        
        if (empty($keywords)) {
            WP_CLI::warning('No tracked keywords found');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($keywords));
            return;
        }
        
        WP_CLI::success(sprintf('Found %d tracked keywords', count($keywords)));
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($keywords, JSON_PRETTY_PRINT));
        } else {
            WP_CLI\Utils\format_items($format, $keywords, 
                ['keyword', 'current_position', 'last_checked', 'tracking_count']);
        }
    }
    
    /**
     * Delete keyword tracking
     *
     * ## OPTIONS
     *
     * <keyword>
     * : Keyword to delete
     *
     * [--post-id=<id>]
     * : Delete only for specific post
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank delete "seo tips"
     *     wp aiseo rank delete "wordpress seo" --post-id=123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function delete($args, $assoc_args) {
        $keyword = $args[0];
        $post_id = isset($assoc_args['post-id']) ? absint($assoc_args['post-id']) : 0;
        
        WP_CLI::log(sprintf('Deleting tracking for: "%s"', $keyword));
        if ($post_id) {
            WP_CLI::log(sprintf('Post ID: %d', $post_id));
        }
        WP_CLI::log('');
        
        $tracker = new AISEO_Rank_Tracker();
        $result = $tracker->delete_keyword($keyword, $post_id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Keyword tracking deleted successfully!');
    }
    
    /**
     * Get rank tracking summary
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rank summary
     *     wp aiseo rank summary --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function summary($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $tracker = new AISEO_Rank_Tracker();
        $summary = $tracker->get_summary();
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($summary, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Rank Tracking Summary:');
            WP_CLI::log('');
            WP_CLI::log(sprintf('Total Keywords: %d', $summary['total_keywords']));
            WP_CLI::log(sprintf('Total Tracking Records: %d', $summary['total_tracking_records']));
            WP_CLI::log(sprintf('Average Position: %.1f', $summary['average_position']));
            WP_CLI::log(sprintf('Top 10 Keywords: %d', $summary['top_10_keywords']));
            WP_CLI::log(sprintf('Top 3 Keywords: %d', $summary['top_3_keywords']));
            WP_CLI::log(sprintf('Position #1 Keywords: %d', $summary['position_1_keywords']));
            WP_CLI::log(sprintf('Tracked Posts: %d', $summary['tracked_posts']));
            
            if (!empty($summary['locations'])) {
                WP_CLI::log('');
                WP_CLI::log('Locations: ' . implode(', ', $summary['locations']));
            }
        }
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo rank track', ['AISEO_Rank_Tracker_CLI', 'track']);
WP_CLI::add_command('aiseo rank history', ['AISEO_Rank_Tracker_CLI', 'history']);
WP_CLI::add_command('aiseo rank keywords', ['AISEO_Rank_Tracker_CLI', 'keywords']);
WP_CLI::add_command('aiseo rank compare', ['AISEO_Rank_Tracker_CLI', 'compare']);
WP_CLI::add_command('aiseo rank serp-features', ['AISEO_Rank_Tracker_CLI', 'serp_features']);
WP_CLI::add_command('aiseo rank list', ['AISEO_Rank_Tracker_CLI', 'list']);
WP_CLI::add_command('aiseo rank delete', ['AISEO_Rank_Tracker_CLI', 'delete']);
WP_CLI::add_command('aiseo rank summary', ['AISEO_Rank_Tracker_CLI', 'summary']);
