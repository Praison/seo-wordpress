<?php
/**
 * AISEO Enhanced Readability WP-CLI Commands
 *
 * @package AISEO
 * @since 1.11.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Readability CLI Commands
 */
class AISEO_Readability_CLI {
    
    /**
     * Analyze readability for a post
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
     *   - summary
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo readability analyze 123
     *     wp aiseo readability analyze 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function analyze($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error("Post {$post_id} not found");
            return;
        }
        
        $readability = new AISEO_Readability();
        $analysis = $readability->analyze($post->post_content);
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($analysis, JSON_PRETTY_PRINT));
        } elseif ($format === 'summary') {
            WP_CLI::line("=== Readability Analysis for Post {$post_id} ===");
            WP_CLI::line("Flesch Reading Ease: {$analysis['flesch_reading_ease']}");
            WP_CLI::line("Flesch-Kincaid Grade: {$analysis['flesch_kincaid_grade']}");
            WP_CLI::line("Gunning Fog Index: {$analysis['gunning_fog_index']}");
            WP_CLI::line("Passive Voice: {$analysis['passive_voice_percentage']}%");
            WP_CLI::line("Transition Words: {$analysis['transition_words_percentage']}%");
        } else {
            $data = [
                ['Metric', 'Value'],
                ['Flesch Reading Ease', $analysis['flesch_reading_ease']],
                ['Flesch-Kincaid Grade', $analysis['flesch_kincaid_grade']],
                ['Gunning Fog Index', $analysis['gunning_fog_index']],
                ['SMOG Index', $analysis['smog_index']],
                ['Coleman-Liau Index', $analysis['coleman_liau_index']],
                ['ARI', $analysis['automated_readability_index']],
                ['Passive Voice %', $analysis['passive_voice_percentage']],
                ['Transition Words %', $analysis['transition_words_percentage']],
            ];
            
            WP_CLI\Utils\format_items('table', $data, ['Metric', 'Value']);
        }
        
        WP_CLI::success("Analysis complete");
    }
    
    /**
     * Bulk analyze readability
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
     *     wp aiseo readability bulk
     *     wp aiseo readability bulk --limit=50 --format=csv
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 100;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
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
        
        WP_CLI::line("Analyzing {$limit} posts...");
        
        $readability = new AISEO_Readability();
        $results = [];
        
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            $analysis = $readability->analyze($post->post_content);
            
            $results[] = [
                'ID' => $post_id,
                'Title' => get_the_title($post_id),
                'Flesch' => $analysis['flesch_reading_ease'],
                'Grade' => $analysis['flesch_kincaid_grade'],
                'Passive%' => $analysis['passive_voice_percentage'],
                'Transition%' => $analysis['transition_words_percentage']
            ];
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($results, JSON_PRETTY_PRINT));
        } else {
            WP_CLI\Utils\format_items($format, $results, ['ID', 'Title', 'Flesch', 'Grade', 'Passive%', 'Transition%']);
        }
        
        WP_CLI::success("Analyzed " . count($results) . " posts");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo readability analyze', ['AISEO_Readability_CLI', 'analyze']);
    WP_CLI::add_command('aiseo readability bulk', ['AISEO_Readability_CLI', 'bulk']);
}
