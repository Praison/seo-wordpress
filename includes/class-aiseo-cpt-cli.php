<?php
/**
 * AISEO Custom Post Type WP-CLI Commands
 *
 * @package AISEO
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Custom Post Type WP-CLI Commands
 */
class AISEO_CPT_CLI {
    
    /**
     * List all custom post types
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
     *   - csv
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo cpt list
     *     wp aiseo cpt list --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function list($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $cpt = new AISEO_CPT();
        $post_types = $cpt->get_custom_post_types();
        
        if (empty($post_types)) {
            WP_CLI::warning('No custom post types found');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($post_types));
            return;
        }
        
        WP_CLI::log(sprintf('Found %d custom post type(s)', count($post_types)));
        WP_CLI::log('');
        
        WP_CLI\Utils\format_items($format, $post_types, ['name', 'label', 'public', 'hierarchical', 'count']);
    }
    
    /**
     * List supported post types
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: list
     * options:
     *   - list
     *   - json
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo cpt supported
     *     wp aiseo cpt supported --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function supported($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'list';
        
        $cpt = new AISEO_CPT();
        $supported = $cpt->get_supported_post_types();
        
        if ($format === 'count') {
            WP_CLI::log(count($supported));
            return;
        }
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($supported, JSON_PRETTY_PRINT));
            return;
        }
        
        WP_CLI::log(sprintf('SEO enabled for %d post type(s):', count($supported)));
        foreach ($supported as $post_type) {
            WP_CLI::log('  - ' . $post_type);
        }
    }
    
    /**
     * Enable SEO for a custom post type
     *
     * ## OPTIONS
     *
     * <post-type>
     * : Post type name
     *
     * ## EXAMPLES
     *
     *     wp aiseo cpt enable product
     *     wp aiseo cpt enable portfolio
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function enable($args, $assoc_args) {
        $post_type = $args[0];
        
        if (empty($post_type)) {
            WP_CLI::error('Post type name is required');
        }
        
        // Check if post type exists
        if (!post_type_exists($post_type)) {
            WP_CLI::error(sprintf('Post type "%s" does not exist', $post_type));
        }
        
        $cpt = new AISEO_CPT();
        $result = $cpt->enable_post_type($post_type);
        
        if ($result) {
            WP_CLI::success(sprintf('SEO enabled for post type: %s', $post_type));
        } else {
            WP_CLI::warning(sprintf('Post type "%s" is already enabled', $post_type));
        }
    }
    
    /**
     * Disable SEO for a custom post type
     *
     * ## OPTIONS
     *
     * <post-type>
     * : Post type name
     *
     * ## EXAMPLES
     *
     *     wp aiseo cpt disable product
     *     wp aiseo cpt disable portfolio
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function disable($args, $assoc_args) {
        $post_type = $args[0];
        
        if (empty($post_type)) {
            WP_CLI::error('Post type name is required');
        }
        
        $cpt = new AISEO_CPT();
        $result = $cpt->disable_post_type($post_type);
        
        if ($result) {
            WP_CLI::success(sprintf('SEO disabled for post type: %s', $post_type));
        } else {
            WP_CLI::error(sprintf('Post type "%s" not found in supported list', $post_type));
        }
    }
    
    /**
     * Get posts from a custom post type
     *
     * ## OPTIONS
     *
     * <post-type>
     * : Post type name
     *
     * [--limit=<number>]
     * : Number of posts to retrieve
     * ---
     * default: 20
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
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo cpt posts product
     *     wp aiseo cpt posts product --limit=50
     *     wp aiseo cpt posts product --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function posts($args, $assoc_args) {
        $post_type = $args[0];
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 20;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (!post_type_exists($post_type)) {
            WP_CLI::error(sprintf('Post type "%s" does not exist', $post_type));
        }
        
        $cpt = new AISEO_CPT();
        $posts = $cpt->get_posts_by_type($post_type, ['posts_per_page' => $limit]);
        
        if (empty($posts)) {
            WP_CLI::warning(sprintf('No posts found for post type: %s', $post_type));
            return;
        }
        
        WP_CLI::log(sprintf('Found %d post(s) for type: %s', count($posts), $post_type));
        WP_CLI::log('');
        
        if ($format === 'ids') {
            $ids = array_column($posts, 'ID');
            WP_CLI::log(implode(' ', $ids));
        } else {
            WP_CLI\Utils\format_items($format, $posts, ['ID', 'title', 'status', 'meta_title', 'seo_score']);
        }
    }
    
    /**
     * Get SEO statistics for a custom post type
     *
     * ## OPTIONS
     *
     * <post-type>
     * : Post type name
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
     *     wp aiseo cpt stats product
     *     wp aiseo cpt stats product --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function stats($args, $assoc_args) {
        $post_type = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (!post_type_exists($post_type)) {
            WP_CLI::error(sprintf('Post type "%s" does not exist', $post_type));
        }
        
        $cpt = new AISEO_CPT();
        $stats = $cpt->get_post_type_stats($post_type);
        
        WP_CLI::log(sprintf('SEO Statistics for: %s', $post_type));
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($stats, JSON_PRETTY_PRINT));
        } else {
            $stats_table = [];
            foreach ($stats as $key => $value) {
                $stats_table[] = [
                    'metric' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $value
                ];
            }
            WP_CLI\Utils\format_items('table', $stats_table, ['metric', 'value']);
        }
    }
    
    /**
     * Bulk generate SEO metadata for custom post type
     *
     * ## OPTIONS
     *
     * <post-type>
     * : Post type name
     *
     * [--limit=<number>]
     * : Number of posts to process
     * ---
     * default: -1
     * ---
     *
     * [--overwrite]
     * : Overwrite existing metadata
     *
     * [--meta-types=<types>]
     * : Comma-separated list of metadata types to generate
     * ---
     * default: title,description
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo cpt bulk-generate product
     *     wp aiseo cpt bulk-generate product --limit=50
     *     wp aiseo cpt bulk-generate product --overwrite
     *     wp aiseo cpt bulk-generate product --meta-types=title
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk_generate($args, $assoc_args) {
        $post_type = $args[0];
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : -1;
        $overwrite = isset($assoc_args['overwrite']);
        $meta_types = isset($assoc_args['meta-types']) ? explode(',', $assoc_args['meta-types']) : ['title', 'description'];
        
        if (!post_type_exists($post_type)) {
            WP_CLI::error(sprintf('Post type "%s" does not exist', $post_type));
        }
        
        WP_CLI::log(sprintf('Post type: %s', $post_type));
        WP_CLI::log(sprintf('Limit: %s', $limit === -1 ? 'All' : $limit));
        WP_CLI::log(sprintf('Overwrite: %s', $overwrite ? 'Yes' : 'No'));
        WP_CLI::log(sprintf('Meta types: %s', implode(', ', $meta_types)));
        WP_CLI::log('');
        
        $cpt = new AISEO_CPT();
        $result = $cpt->bulk_generate_for_type($post_type, [
            'limit' => $limit,
            'overwrite' => $overwrite,
            'meta_types' => $meta_types
        ]);
        
        WP_CLI::log('');
        WP_CLI::success(sprintf(
            'Bulk generation complete. Total: %d, Generated: %d, Skipped: %d, Failed: %d',
            $result['total'],
            $result['generated'],
            $result['skipped'],
            $result['failed']
        ));
    }
    
    /**
     * Export SEO data for custom post type
     *
     * ## OPTIONS
     *
     * <post-type>
     * : Post type name
     *
     * [--format=<format>]
     * : Export format
     * ---
     * default: json
     * options:
     *   - json
     *   - csv
     * ---
     *
     * [--output=<file>]
     * : Output file path
     *
     * ## EXAMPLES
     *
     *     wp aiseo cpt export product
     *     wp aiseo cpt export product --format=csv --output=/tmp/product-seo.csv
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function export($args, $assoc_args) {
        $post_type = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'json';
        $output = isset($assoc_args['output']) ? $assoc_args['output'] : null;
        
        if (!post_type_exists($post_type)) {
            WP_CLI::error(sprintf('Post type "%s" does not exist', $post_type));
        }
        
        $cpt = new AISEO_CPT();
        $data = $cpt->export_post_type_data($post_type, $format);
        
        if ($output) {
            if ($format === 'json') {
                file_put_contents($output, json_encode($data, JSON_PRETTY_PRINT));
            } else {
                file_put_contents($output, $data);
            }
            WP_CLI::success(sprintf('Exported to: %s', $output));
        } else {
            if ($format === 'json') {
                WP_CLI::log(json_encode($data, JSON_PRETTY_PRINT));
            } else {
                WP_CLI::log($data);
            }
        }
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo cpt list', ['AISEO_CPT_CLI', 'list']);
WP_CLI::add_command('aiseo cpt supported', ['AISEO_CPT_CLI', 'supported']);
WP_CLI::add_command('aiseo cpt enable', ['AISEO_CPT_CLI', 'enable']);
WP_CLI::add_command('aiseo cpt disable', ['AISEO_CPT_CLI', 'disable']);
WP_CLI::add_command('aiseo cpt posts', ['AISEO_CPT_CLI', 'posts']);
WP_CLI::add_command('aiseo cpt stats', ['AISEO_CPT_CLI', 'stats']);
WP_CLI::add_command('aiseo cpt bulk-generate', ['AISEO_CPT_CLI', 'bulk_generate']);
WP_CLI::add_command('aiseo cpt export', ['AISEO_CPT_CLI', 'export']);
