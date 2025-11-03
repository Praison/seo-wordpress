<?php
/**
 * AISEO Meta Description Variations WP-CLI Commands
 *
 * @package AISEO
 * @since 1.15.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Meta Description Variations CLI Commands
 */
class AISEO_Meta_Variations_CLI {
    
    /**
     * Generate meta description variations
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Post ID
     *
     * [--count=<count>]
     * : Number of variations to generate
     * ---
     * default: 5
     * ---
     *
     * [--save]
     * : Save variations to post meta
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
     *     wp aiseo meta variations 123
     *     wp aiseo meta variations 123 --count=10 --save
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function variations($args, $assoc_args) {
        $post_id = absint($args[0]);
        $count = isset($assoc_args['count']) ? absint($assoc_args['count']) : 5;
        $save = isset($assoc_args['save']);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error("Post {$post_id} not found");
            return;
        }
        
        WP_CLI::line("Generating {$count} meta description variations for post {$post_id}...");
        
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);
        $meta_variations = new AISEO_Meta_Variations();
        $result = $meta_variations->generate($post->post_content, $keyword, $count);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $table_data = [];
            foreach ($result['variations'] as $index => $variation) {
                $table_data[] = [
                    'No' => $index + 1,
                    'Description' => substr($variation['description'], 0, 80) . '...',
                    'Length' => $variation['length'],
                    'CTA' => $variation['cta_type'],
                    'Score' => $variation['score']
                ];
            }
            
            WP_CLI\Utils\format_items('table', $table_data, ['No', 'Description', 'Length', 'CTA', 'Score']);
            
            if (!empty($result['best'])) {
                WP_CLI::line("\n=== Best Variation (Score: {$result['best']['score']}) ===");
                WP_CLI::line($result['best']['description']);
            }
        }
        
        if ($save) {
            $meta_variations->save_to_post($post_id, $result['variations']);
            WP_CLI::success("Variations saved to post {$post_id}");
        } else {
            WP_CLI::success("Generated {$count} variations (use --save to save)");
        }
    }
    
    /**
     * Get saved variations for a post
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo meta variations-get 123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function variations_get($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $meta_variations = new AISEO_Meta_Variations();
        $variations = $meta_variations->get_from_post($post_id);
        
        if (empty($variations)) {
            WP_CLI::warning("No variations found for post {$post_id}");
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($variations, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line("\n=== Saved Variations for Post {$post_id} ===\n");
            foreach ($variations as $index => $variation) {
                WP_CLI::line(($index + 1) . ". " . $variation['description']);
                WP_CLI::line("   Length: {$variation['length']} | CTA: {$variation['cta_type']} | Score: {$variation['score']}");
                WP_CLI::line("");
            }
        }
        
        WP_CLI::success("Found " . count($variations) . " variations");
    }
    
    /**
     * Start A/B test for meta variations
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Post ID
     *
     * ## EXAMPLES
     *
     *     wp aiseo meta variations-test 123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function variations_test($args, $assoc_args) {
        $post_id = absint($args[0]);
        
        $meta_variations = new AISEO_Meta_Variations();
        $variations = $meta_variations->get_from_post($post_id);
        
        if (empty($variations)) {
            WP_CLI::error("No variations found for post {$post_id}. Generate variations first.");
            return;
        }
        
        $test_data = $meta_variations->ab_test($post_id, $variations);
        
        WP_CLI::success("A/B test started for post {$post_id}");
        WP_CLI::line("Testing " . count($variations) . " variations");
        WP_CLI::line("Started at: {$test_data['started_at']}");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    // Changed from 'aiseo meta variations' to 'aiseo meta-variations' to avoid conflict with 'wp aiseo meta' command
    WP_CLI::add_command('aiseo meta-variations', ['AISEO_Meta_Variations_CLI', 'variations']);
    WP_CLI::add_command('aiseo meta-variations-get', ['AISEO_Meta_Variations_CLI', 'variations_get']);
    WP_CLI::add_command('aiseo meta-variations-test', ['AISEO_Meta_Variations_CLI', 'variations_test']);
}
