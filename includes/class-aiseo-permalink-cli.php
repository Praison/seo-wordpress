<?php
/**
 * AISEO Permalink Optimization WP-CLI Commands
 *
 * @package AISEO
 * @since 1.10.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Permalink Optimization CLI Commands
 */
class AISEO_Permalink_CLI {
    
    /**
     * Optimize permalink for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Post ID
     *
     * [--apply]
     * : Apply the optimization
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
     *     wp aiseo permalink optimize 123
     *     wp aiseo permalink optimize 123 --apply
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function optimize($args, $assoc_args) {
        $post_id = absint($args[0]);
        $apply = isset($assoc_args['apply']);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error("Post {$post_id} not found");
            return;
        }
        
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        
        $permalink = new AISEO_Permalink();
        $result = $permalink->optimize_permalink($post->post_name, $keyword);
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line("Original: {$result['original']}");
            WP_CLI::line("Optimized: {$result['optimized']}");
            WP_CLI::line("Score: {$result['score']}/100");
            
            if (!empty($result['removed_words'])) {
                WP_CLI::line("Removed words: " . implode(', ', $result['removed_words']));
            }
            
            if (!empty($result['suggestions'])) {
                WP_CLI::line("\nSuggestions:");
                foreach ($result['suggestions'] as $suggestion) {
                    WP_CLI::line("  - {$suggestion}");
                }
            }
        }
        
        if ($apply && $result['optimized'] !== $result['original']) {
            wp_update_post([
                'ID' => $post_id,
                'post_name' => $result['optimized']
            ]);
            WP_CLI::success("Permalink updated for post {$post_id}");
        }
    }
    
    /**
     * Bulk optimize permalinks
     *
     * ## OPTIONS
     *
     * [--post-type=<type>]
     * : Post type
     * ---
     * default: post
     * ---
     *
     * [--limit=<limit>]
     * : Number of posts
     * ---
     * default: 100
     * ---
     *
     * [--apply]
     * : Apply optimizations
     *
     * ## EXAMPLES
     *
     *     wp aiseo permalink bulk
     *     wp aiseo permalink bulk --apply --limit=50
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 100;
        $apply = isset($assoc_args['apply']);
        
        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'fields' => 'ids'
        ]);
        
        if (empty($posts)) {
            WP_CLI::warning('No posts found');
            return;
        }
        
        WP_CLI::line("Processing {$limit} posts...");
        
        $permalink = new AISEO_Permalink();
        $results = $permalink->bulk_optimize($posts, ['auto_update' => $apply]);
        
        $optimized = 0;
        $total_score = 0;
        
        foreach ($results as $post_id => $result) {
            if ($result['optimized'] !== $result['original']) {
                $optimized++;
            }
            $total_score += $result['score'];
        }
        
        $avg_score = count($results) > 0 ? round($total_score / count($results)) : 0;
        
        WP_CLI::success("Processed " . count($results) . " posts");
        WP_CLI::line("Optimized: {$optimized}");
        WP_CLI::line("Average score: {$avg_score}/100");
        
        if ($apply) {
            WP_CLI::success("Permalinks updated");
        } else {
            WP_CLI::line("Use --apply to update permalinks");
        }
    }
    
    /**
     * Analyze site permalink structure
     *
     * ## EXAMPLES
     *
     *     wp aiseo permalink analyze
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function analyze($args, $assoc_args) {
        WP_CLI::line("Analyzing site permalink structure...");
        
        $permalink = new AISEO_Permalink();
        $stats = $permalink->analyze_site_structure();
        
        WP_CLI::line("\n=== Permalink Analysis ===");
        WP_CLI::line("Total posts: {$stats['total']}");
        WP_CLI::line("With stop words: {$stats['with_stop_words']}");
        WP_CLI::line("Too long (>60 chars): {$stats['too_long']}");
        WP_CLI::line("Missing keyword: {$stats['missing_keyword']}");
        WP_CLI::line("Average length: {$stats['avg_length']} chars");
        WP_CLI::line("Average score: {$stats['avg_score']}/100");
        
        WP_CLI::success("Analysis complete");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo permalink optimize', ['AISEO_Permalink_CLI', 'optimize']);
    WP_CLI::add_command('aiseo permalink bulk', ['AISEO_Permalink_CLI', 'bulk']);
    WP_CLI::add_command('aiseo permalink analyze', ['AISEO_Permalink_CLI', 'analyze']);
}
