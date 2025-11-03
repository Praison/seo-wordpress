<?php
/**
 * AISEO Internal Linking WP-CLI Commands
 *
 * @package AISEO
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WP-CLI commands for Internal Linking
 */
class AISEO_Internal_Linking_CLI {
    
    /**
     * Get internal linking suggestions for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : The post ID to get suggestions for
     *
     * [--limit=<limit>]
     * : Number of suggestions to return
     * ---
     * default: 5
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo internal-linking suggestions 123
     *     wp aiseo internal-linking suggestions 123 --limit=10
     *     wp aiseo internal-linking suggestions 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function suggestions($args, $assoc_args) {
        $post_id = absint($args[0]);
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 5;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->get_suggestions($post_id, ['limit' => $limit]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success("Found {$result['total']} internal linking suggestions for post {$post_id}");
        
        if (empty($result['suggestions'])) {
            WP_CLI::warning('No suggestions found.');
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
            return;
        }
        
        // Format for table display
        $items = [];
        foreach ($result['suggestions'] as $suggestion) {
            $items[] = [
                'Post ID' => $suggestion['post_id'],
                'Title' => $suggestion['post_title'],
                'Relevance' => round($suggestion['relevance_score'] * 100, 1) . '%',
                'Reason' => $suggestion['reason'],
                'Anchor Suggestions' => implode(', ', array_slice($suggestion['anchor_suggestions'], 0, 2))
            ];
        }
        
        WP_CLI\Utils\format_items($format, $items, ['Post ID', 'Title', 'Relevance', 'Reason', 'Anchor Suggestions']);
    }
    
    /**
     * Detect orphan pages (pages with no internal links)
     *
     * ## OPTIONS
     *
     * [--post-type=<post-type>]
     * : Post type to check
     * ---
     * default: post
     * ---
     *
     * [--limit=<limit>]
     * : Maximum number of posts to check
     * ---
     * default: 50
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo internal-linking orphans
     *     wp aiseo internal-linking orphans --post-type=page
     *     wp aiseo internal-linking orphans --limit=100 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function orphans($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 50;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::line("Checking for orphan pages in post type: {$post_type}...");
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->detect_orphans([
            'post_type' => $post_type,
            'limit' => $limit
        ]);
        
        WP_CLI::success("Checked {$result['checked']} posts, found {$result['total']} orphan pages");
        
        if (empty($result['orphans'])) {
            WP_CLI::line('No orphan pages found. Great job!');
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
            return;
        }
        
        // Format for table display
        $items = [];
        foreach ($result['orphans'] as $orphan) {
            $items[] = [
                'Post ID' => $orphan['post_id'],
                'Title' => $orphan['post_title'],
                'Post Type' => $orphan['post_type'],
                'Date' => $orphan['post_date'],
                'URL' => $orphan['post_url']
            ];
        }
        
        WP_CLI\Utils\format_items($format, $items, ['Post ID', 'Title', 'Post Type', 'Date']);
    }
    
    /**
     * Analyze internal link distribution for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : The post ID to analyze
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
     *     wp aiseo internal-linking distribution 123
     *     wp aiseo internal-linking distribution 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function distribution($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->analyze_distribution($post_id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
            return;
        }
        
        // Display summary
        WP_CLI::line('');
        WP_CLI::line(WP_CLI::colorize('%GInternal Link Distribution Analysis%n'));
        WP_CLI::line(str_repeat('=', 50));
        WP_CLI::line("Post: {$result['post_title']} (ID: {$result['post_id']})");
        WP_CLI::line('');
        WP_CLI::line("Total Internal Links: {$result['total_internal_links']}");
        WP_CLI::line("Unique Destinations: {$result['unique_destinations']}");
        WP_CLI::line("Average Anchor Length: {$result['average_anchor_length']} characters");
        WP_CLI::line('');
        
        // Display recommendations
        WP_CLI::line(WP_CLI::colorize('%YRecommendations:%n'));
        foreach ($result['recommendations'] as $recommendation) {
            WP_CLI::line("  • {$recommendation}");
        }
        WP_CLI::line('');
        
        // Display links if any
        if (!empty($result['links'])) {
            WP_CLI::line(WP_CLI::colorize('%BInternal Links:%n'));
            $items = [];
            foreach (array_slice($result['links'], 0, 10) as $link) {
                $items[] = [
                    'URL' => $link['url'],
                    'Anchor Text' => $link['anchor_text'],
                    'Length' => $link['anchor_length']
                ];
            }
            WP_CLI\Utils\format_items('table', $items, ['URL', 'Anchor Text', 'Length']);
            
            if (count($result['links']) > 10) {
                WP_CLI::line("... and " . (count($result['links']) - 10) . " more links");
            }
        }
    }
    
    /**
     * Get link opportunities for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : The post ID to analyze
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
     *     wp aiseo internal-linking opportunities 123
     *     wp aiseo internal-linking opportunities 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function opportunities($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::line("Analyzing link opportunities for post {$post_id}...");
        
        $linking = new AISEO_Internal_Linking();
        $result = $linking->get_opportunities($post_id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success("Found {$result['total']} link opportunities");
        
        if (empty($result['opportunities'])) {
            WP_CLI::line('No link opportunities found.');
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
            return;
        }
        
        // Format for table display
        $items = [];
        foreach ($result['opportunities'] as $opp) {
            $items[] = [
                'Type' => $opp['type'],
                'Priority' => strtoupper($opp['priority']),
                'Target Post' => $opp['target_post'],
                'Target Title' => $opp['target_title'],
                'Opportunity' => $opp['opportunity']
            ];
        }
        
        WP_CLI\Utils\format_items($format, $items, ['Type', 'Priority', 'Target Post', 'Target Title', 'Opportunity']);
    }
    
    /**
     * Batch process internal linking suggestions (optimized for large sites)
     *
     * ## OPTIONS
     *
     * [--post-type=<post-type>]
     * : Post type to process
     * ---
     * default: post
     * ---
     *
     * [--batch-size=<batch-size>]
     * : Number of posts per batch
     * ---
     * default: 10
     * ---
     *
     * [--offset=<offset>]
     * : Starting offset
     * ---
     * default: 0
     * ---
     *
     * [--total-limit=<total-limit>]
     * : Maximum total posts to process
     * ---
     * default: 100
     * ---
     *
     * [--force-refresh]
     * : Force refresh cache
     *
     * ## EXAMPLES
     *
     *     wp aiseo internal-linking batch-process
     *     wp aiseo internal-linking batch-process --batch-size=20 --total-limit=200
     *     wp aiseo internal-linking batch-process --force-refresh
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function batch_process($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $batch_size = isset($assoc_args['batch-size']) ? absint($assoc_args['batch-size']) : 10;
        $offset = isset($assoc_args['offset']) ? absint($assoc_args['offset']) : 0;
        $total_limit = isset($assoc_args['total-limit']) ? absint($assoc_args['total-limit']) : 100;
        $force_refresh = isset($assoc_args['force-refresh']);
        
        WP_CLI::line("Starting batch processing...");
        WP_CLI::line("Post Type: {$post_type}");
        WP_CLI::line("Batch Size: {$batch_size}");
        WP_CLI::line("Total Limit: {$total_limit}");
        WP_CLI::line('');
        
        $linking = new AISEO_Internal_Linking();
        $total_processed = 0;
        $current_offset = $offset;
        
        do {
            $result = $linking->batch_process_suggestions([
                'post_type' => $post_type,
                'batch_size' => $batch_size,
                'offset' => $current_offset,
                'total_limit' => $total_limit,
                'force_refresh' => $force_refresh
            ]);
            
            $total_processed += $result['processed'];
            
            WP_CLI::line("Batch {$current_offset}-" . ($current_offset + $batch_size) . ": Processed {$result['processed']} posts");
            
            if ($result['has_more']) {
                $current_offset = $result['next_offset'];
            } else {
                break;
            }
            
        } while ($result['has_more']);
        
        WP_CLI::success("Batch processing complete! Total processed: {$total_processed} posts");
    }
    
    /**
     * Clear internal linking caches
     *
     * ## OPTIONS
     *
     * [<post-id>]
     * : Clear cache for specific post ID
     *
     * [--all]
     * : Clear all internal linking caches
     *
     * ## EXAMPLES
     *
     *     wp aiseo internal-linking clear-cache 123
     *     wp aiseo internal-linking clear-cache --all
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function clear_cache($args, $assoc_args) {
        $linking = new AISEO_Internal_Linking();
        
        if (isset($assoc_args['all'])) {
            $linking->clear_all_caches();
            WP_CLI::success('All internal linking caches cleared!');
        } elseif (isset($args[0])) {
            $post_id = absint($args[0]);
            $linking->clear_cache($post_id);
            WP_CLI::success("Cache cleared for post {$post_id}");
        } else {
            WP_CLI::error('Please specify a post ID or use --all flag');
        }
    }
    
    /**
     * Bulk analyze internal linking for multiple posts
     *
     * ## OPTIONS
     *
     * [--post-type=<post-type>]
     * : Post type to analyze
     * ---
     * default: post
     * ---
     *
     * [--limit=<limit>]
     * : Maximum number of posts to analyze
     * ---
     * default: 10
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo internal-linking bulk-analyze
     *     wp aiseo internal-linking bulk-analyze --post-type=page --limit=20
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk_analyze($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 10;
        
        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (empty($posts)) {
            WP_CLI::error("No posts found for post type: {$post_type}");
            return;
        }
        
        WP_CLI::line("Analyzing {$limit} posts...");
        $progress = \WP_CLI\Utils\make_progress_bar('Analyzing posts', count($posts));
        
        $linking = new AISEO_Internal_Linking();
        $results = [];
        
        foreach ($posts as $post) {
            $distribution = $linking->analyze_distribution($post->ID);
            
            if (!is_wp_error($distribution)) {
                $results[] = [
                    'Post ID' => $post->ID,
                    'Title' => $post->post_title,
                    'Internal Links' => $distribution['total_internal_links'],
                    'Unique Destinations' => $distribution['unique_destinations'],
                    'Status' => $distribution['total_internal_links'] >= 3 ? 'Good' : 'Needs More'
                ];
            }
            
            $progress->tick();
        }
        
        $progress->finish();
        
        WP_CLI::line('');
        WP_CLI::success('Bulk analysis complete!');
        WP_CLI::line('');
        
        if (!empty($results)) {
            WP_CLI\Utils\format_items('table', $results, ['Post ID', 'Title', 'Internal Links', 'Unique Destinations', 'Status']);
        }
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo internal-linking suggestions', ['AISEO_Internal_Linking_CLI', 'suggestions']);
    WP_CLI::add_command('aiseo internal-linking orphans', ['AISEO_Internal_Linking_CLI', 'orphans']);
    WP_CLI::add_command('aiseo internal-linking distribution', ['AISEO_Internal_Linking_CLI', 'distribution']);
    WP_CLI::add_command('aiseo internal-linking opportunities', ['AISEO_Internal_Linking_CLI', 'opportunities']);
    WP_CLI::add_command('aiseo internal-linking bulk-analyze', ['AISEO_Internal_Linking_CLI', 'bulk_analyze']);
    WP_CLI::add_command('aiseo internal-linking batch-process', ['AISEO_Internal_Linking_CLI', 'batch_process']);
    WP_CLI::add_command('aiseo internal-linking clear-cache', ['AISEO_Internal_Linking_CLI', 'clear_cache']);
}
