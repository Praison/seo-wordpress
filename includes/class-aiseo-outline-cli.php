<?php
/**
 * AISEO Content Outline Generator WP-CLI Commands
 *
 * @package AISEO
 * @since 1.13.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Outline CLI Commands
 */
class AISEO_Outline_CLI {
    
    /**
     * Generate content outline
     *
     * ## OPTIONS
     *
     * <topic>
     * : Topic or title
     *
     * [--keyword=<keyword>]
     * : Focus keyword
     *
     * [--word-count=<count>]
     * : Target word count
     * ---
     * default: 1500
     * ---
     *
     * [--save]
     * : Save outline to post
     *
     * [--post-id=<id>]
     * : Post ID to save to
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: text
     * options:
     *   - text
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo outline generate "WordPress SEO Best Practices"
     *     wp aiseo outline generate "SEO Guide" --keyword="seo" --word-count=2000 --save --post-id=123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function generate($args, $assoc_args) {
        $topic = $args[0];
        $keyword = isset($assoc_args['keyword']) ? $assoc_args['keyword'] : '';
        $word_count = isset($assoc_args['word-count']) ? absint($assoc_args['word-count']) : 1500;
        $save = isset($assoc_args['save']);
        $post_id = isset($assoc_args['post-id']) ? absint($assoc_args['post-id']) : 0;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'text';
        
        WP_CLI::line("Generating outline for: {$topic}");
        if (!empty($keyword)) {
            WP_CLI::line("Focus keyword: {$keyword}");
        }
        WP_CLI::line("Target word count: {$word_count}");
        
        $outline = new AISEO_Outline();
        $result = $outline->generate($topic, $keyword, ['word_count' => $word_count]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line("\n=== Content Outline ===\n");
            WP_CLI::line("Topic: {$result['topic']}");
            if (!empty($result['keyword'])) {
                WP_CLI::line("Keyword: {$result['keyword']}");
            }
            WP_CLI::line("Estimated Word Count: {$result['estimated_word_count']}");
            WP_CLI::line("\n" . $result['html']);
        }
        
        if ($save && $post_id) {
            $outline->save_to_post($post_id, $result['outline']);
            WP_CLI::success("Outline saved to post {$post_id}");
        } else {
            WP_CLI::success("Outline generated (use --save --post-id=<id> to save)");
        }
    }
    
    /**
     * Get saved outline for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Post ID
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: text
     * options:
     *   - text
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo outline get 123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function get($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'text';
        
        $outline = new AISEO_Outline();
        $saved_outline = $outline->get_from_post($post_id);
        
        if (empty($saved_outline)) {
            WP_CLI::warning("No outline found for post {$post_id}");
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($saved_outline, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line("\n=== Saved Outline for Post {$post_id} ===\n");
            WP_CLI::success('Outline saved successfully.');
        }
        
        WP_CLI::success("Outline retrieved");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo outline generate', ['AISEO_Outline_CLI', 'generate']);
    WP_CLI::add_command('aiseo outline get', ['AISEO_Outline_CLI', 'get']);
}
