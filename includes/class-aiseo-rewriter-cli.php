<?php
/**
 * AISEO Smart Content Rewriter WP-CLI Commands
 *
 * @package AISEO
 * @since 1.14.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Smart Content Rewriter CLI Commands
 */
class AISEO_Rewriter_CLI {
    
    /**
     * Rewrite post content
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Post ID
     *
     * [--mode=<mode>]
     * : Rewrite mode
     * ---
     * default: improve
     * options:
     *   - improve
     *   - simplify
     *   - expand
     *   - shorten
     *   - professional
     *   - casual
     * ---
     *
     * [--keyword=<keyword>]
     * : Focus keyword
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
     *     wp aiseo rewrite content 123
     *     wp aiseo rewrite content 123 --mode=simplify --keyword="seo"
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function content($args, $assoc_args) {
        $post_id = absint($args[0]);
        $mode = isset($assoc_args['mode']) ? $assoc_args['mode'] : 'improve';
        $keyword = isset($assoc_args['keyword']) ? $assoc_args['keyword'] : '';
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'text';
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error("Post {$post_id} not found");
            return;
        }
        
        WP_CLI::line("Rewriting content for post {$post_id} (mode: {$mode})...");
        
        $rewriter = new AISEO_Rewriter();
        $result = $rewriter->rewrite($post->post_content, [
            'mode' => $mode,
            'keyword' => $keyword
        ]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line("\n=== Original Content ===");
            WP_CLI::line(wp_trim_words($result['original'], 100));
            WP_CLI::line("\n=== Rewritten Content ===");
            WP_CLI::line($result['rewritten']);
            WP_CLI::line("\n=== Improvements ===");
            WP_CLI::line("Word count change: {$result['improvements']['word_count_change']}");
            WP_CLI::line("Percentage change: {$result['improvements']['word_count_percentage']}%");
        }
        
        WP_CLI::success("Content rewritten successfully");
    }
    
    /**
     * Rewrite a paragraph
     *
     * ## OPTIONS
     *
     * <text>
     * : Paragraph text
     *
     * [--mode=<mode>]
     * : Rewrite mode
     * ---
     * default: improve
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rewrite paragraph "This is a test paragraph."
     *     wp aiseo rewrite paragraph "Complex text here." --mode=simplify
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function paragraph($args, $assoc_args) {
        $text = $args[0];
        $mode = isset($assoc_args['mode']) ? $assoc_args['mode'] : 'improve';
        
        WP_CLI::line("Rewriting paragraph (mode: {$mode})...");
        
        $rewriter = new AISEO_Rewriter();
        $result = $rewriter->rewrite_paragraph($text, ['mode' => $mode]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::line("\n=== Original ===");
        WP_CLI::line($text);
        WP_CLI::line("\n=== Rewritten ===");
        WP_CLI::line($result);
        
        WP_CLI::success("Paragraph rewritten");
    }
    
    /**
     * Rewrite a sentence
     *
     * ## OPTIONS
     *
     * <text>
     * : Sentence text
     *
     * [--mode=<mode>]
     * : Rewrite mode
     * ---
     * default: improve
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo rewrite sentence "This is a test."
     *     wp aiseo rewrite sentence "Simple text." --mode=professional
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function sentence($args, $assoc_args) {
        $text = $args[0];
        $mode = isset($assoc_args['mode']) ? $assoc_args['mode'] : 'improve';
        
        WP_CLI::line("Rewriting sentence (mode: {$mode})...");
        
        $rewriter = new AISEO_Rewriter();
        $result = $rewriter->rewrite_sentence($text, ['mode' => $mode]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::line("\n=== Original ===");
        WP_CLI::line($text);
        WP_CLI::line("\n=== Rewritten ===");
        WP_CLI::line($result);
        
        WP_CLI::success("Sentence rewritten");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo rewrite content', ['AISEO_Rewriter_CLI', 'content']);
    WP_CLI::add_command('aiseo rewrite paragraph', ['AISEO_Rewriter_CLI', 'paragraph']);
    WP_CLI::add_command('aiseo rewrite sentence', ['AISEO_Rewriter_CLI', 'sentence']);
}
