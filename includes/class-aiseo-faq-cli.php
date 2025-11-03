<?php
/**
 * AISEO FAQ Generator WP-CLI Commands
 *
 * @package AISEO
 * @since 1.12.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FAQ Generator CLI Commands
 */
class AISEO_FAQ_CLI {
    
    /**
     * Generate FAQs for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Post ID
     *
     * [--count=<count>]
     * : Number of FAQs to generate
     * ---
     * default: 5
     * ---
     *
     * [--save]
     * : Save FAQs to post meta
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
     *     wp aiseo faq generate 123
     *     wp aiseo faq generate 123 --count=10 --save
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function generate($args, $assoc_args) {
        $post_id = absint($args[0]);
        $count = isset($assoc_args['count']) ? absint($assoc_args['count']) : 5;
        $save = isset($assoc_args['save']);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error("Post {$post_id} not found");
            return;
        }
        
        WP_CLI::line("Generating {$count} FAQs for post {$post_id}...");
        
        $faq = new AISEO_FAQ();
        $result = $faq->generate($post->post_content, $count);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line("\n=== Generated FAQs ===\n");
            foreach ($result['faqs'] as $index => $faq_item) {
                WP_CLI::line("Q" . ($index + 1) . ": " . $faq_item['question']);
                WP_CLI::line("A" . ($index + 1) . ": " . $faq_item['answer']);
                WP_CLI::line("");
            }
        }
        
        if ($save) {
            $faq->save_to_post($post_id, $result['faqs']);
            WP_CLI::success("FAQs saved to post {$post_id}");
        } else {
            WP_CLI::success("Generated {$count} FAQs (use --save to save)");
        }
    }
    
    /**
     * Get saved FAQs for a post
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
     *     wp aiseo faq get 123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function get($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $faq = new AISEO_FAQ();
        $faqs = $faq->get_from_post($post_id);
        
        if (empty($faqs)) {
            WP_CLI::warning("No FAQs found for post {$post_id}");
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($faqs, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line("\n=== Saved FAQs for Post {$post_id} ===\n");
            foreach ($faqs as $index => $faq_item) {
                WP_CLI::line("Q" . ($index + 1) . ": " . $faq_item['question']);
                WP_CLI::line("A" . ($index + 1) . ": " . $faq_item['answer']);
                WP_CLI::line("");
            }
        }
        
        WP_CLI::success("Found " . count($faqs) . " FAQs");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo faq generate', ['AISEO_FAQ_CLI', 'generate']);
    WP_CLI::add_command('aiseo faq get', ['AISEO_FAQ_CLI', 'get']);
}
