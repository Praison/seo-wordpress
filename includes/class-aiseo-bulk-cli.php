<?php
/**
 * AISEO Bulk Editing WP-CLI Commands
 *
 * @package AISEO
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Bulk Editing WP-CLI Commands
 */
class AISEO_Bulk_CLI {
    
    /**
     * List posts for bulk editing
     *
     * ## OPTIONS
     *
     * [--post-type=<type>]
     * : Post type to list
     * ---
     * default: post
     * ---
     *
     * [--limit=<number>]
     * : Number of posts to list
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
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo bulk list
     *     wp aiseo bulk list --post-type=page --limit=100
     *     wp aiseo bulk list --format=json
     *     wp aiseo bulk list --format=ids
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function list($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 50;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $posts = $bulk_edit->get_posts_for_editing([
            'post_type' => $post_type,
            'posts_per_page' => $limit
        ]);
        
        if (empty($posts)) {
            WP_CLI::warning('No posts found.');
            return;
        }
        
        if ($format === 'ids') {
            $ids = array_column($posts, 'ID');
            WP_CLI::log(implode(' ', $ids));
            return;
        }
        
        WP_CLI::log(sprintf('Found %d %s(s)', count($posts), $post_type));
        WP_CLI::log('');
        
        $output = [];
        foreach ($posts as $post) {
            $output[] = [
                'ID' => $post['ID'],
                'Title' => substr($post['title'], 0, 50),
                'Meta_Title' => !empty($post['meta_title']) ? 'Yes' : 'No',
                'Meta_Desc' => !empty($post['meta_description']) ? 'Yes' : 'No',
                'Keyword' => $post['focus_keyword'] ?: '-',
                'Status' => $post['status']
            ];
        }
        
        WP_CLI\Utils\format_items($format, $output, ['ID', 'Title', 'Meta_Title', 'Meta_Desc', 'Keyword', 'Status']);
    }
    
    /**
     * Bulk update metadata for multiple posts
     *
     * ## OPTIONS
     *
     * <post-ids>
     * : Comma-separated list of post IDs
     *
     * [--meta-title=<title>]
     * : Meta title to set for all posts
     *
     * [--meta-description=<description>]
     * : Meta description to set for all posts
     *
     * [--focus-keyword=<keyword>]
     * : Focus keyword to set for all posts
     *
     * [--robots-index=<value>]
     * : Robots index setting (index/noindex)
     *
     * [--robots-follow=<value>]
     * : Robots follow setting (follow/nofollow)
     *
     * ## EXAMPLES
     *
     *     wp aiseo bulk update 123,456,789 --focus-keyword="wordpress seo"
     *     wp aiseo bulk update 123,456 --robots-index=noindex
     *     wp aiseo bulk update 123 --meta-title="New Title" --meta-description="New description"
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function update($args, $assoc_args) {
        $post_ids_str = $args[0];
        $post_ids = array_map('absint', explode(',', $post_ids_str));
        
        if (empty($post_ids)) {
            WP_CLI::error('No post IDs provided.');
        }
        
        // Build updates array
        $updates = [];
        foreach ($post_ids as $post_id) {
            $update = ['post_id' => $post_id];
            
            if (isset($assoc_args['meta-title'])) {
                $update['meta_title'] = $assoc_args['meta-title'];
            }
            if (isset($assoc_args['meta-description'])) {
                $update['meta_description'] = $assoc_args['meta-description'];
            }
            if (isset($assoc_args['focus-keyword'])) {
                $update['focus_keyword'] = $assoc_args['focus-keyword'];
            }
            if (isset($assoc_args['robots-index'])) {
                $update['robots_index'] = $assoc_args['robots-index'];
            }
            if (isset($assoc_args['robots-follow'])) {
                $update['robots_follow'] = $assoc_args['robots-follow'];
            }
            
            $updates[] = $update;
        }
        
        WP_CLI::log(sprintf('Updating %d posts...', count($post_ids)));
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $result = $bulk_edit->bulk_update($updates);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success(sprintf(
            'Updated %d posts successfully. Failed: %d',
            $result['success'],
            $result['failed']
        ));
        
        if (!empty($result['errors'])) {
            WP_CLI::warning('Errors encountered:');
            foreach ($result['errors'] as $error) {
                WP_CLI::log('  - ' . $error);
            }
        }
    }
    
    /**
     * Bulk generate metadata using AI
     *
     * ## OPTIONS
     *
     * <post-ids>
     * : Comma-separated list of post IDs, or --all flag
     *
     * [--all]
     * : Generate for all published posts
     *
     * [--post-type=<type>]
     * : Post type (when using --all)
     * ---
     * default: post
     * ---
     *
     * [--meta-types=<types>]
     * : Comma-separated meta types to generate (title,description)
     * ---
     * default: title,description
     * ---
     *
     * [--overwrite]
     * : Overwrite existing metadata
     *
     * [--limit=<number>]
     * : Limit number of posts (when using --all)
     * ---
     * default: 10
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo bulk generate 123,456,789
     *     wp aiseo bulk generate --all --limit=20
     *     wp aiseo bulk generate 123,456 --meta-types=title --overwrite
     *     wp aiseo bulk generate --all --post-type=page --limit=50
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function generate($args, $assoc_args) {
        $overwrite = isset($assoc_args['overwrite']);
        $meta_types_str = isset($assoc_args['meta-types']) ? $assoc_args['meta-types'] : 'title,description';
        $meta_types = explode(',', $meta_types_str);
        
        // Get post IDs
        if (isset($assoc_args['all'])) {
            $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
            $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 10;
            
            $posts = get_posts([
                'post_type' => $post_type,
                'posts_per_page' => $limit,
                'post_status' => 'publish',
                'fields' => 'ids'
            ]);
            $post_ids = $posts;
        } else {
            if (empty($args[0])) {
                WP_CLI::error('Please provide post IDs or use --all flag.');
            }
            $post_ids = array_map('absint', explode(',', $args[0]));
        }
        
        if (empty($post_ids)) {
            WP_CLI::warning('No posts found to process.');
            return;
        }
        
        WP_CLI::log(sprintf('Generating metadata for %d posts...', count($post_ids)));
        WP_CLI::log(sprintf('Meta types: %s', implode(', ', $meta_types)));
        WP_CLI::log(sprintf('Overwrite: %s', $overwrite ? 'Yes' : 'No'));
        WP_CLI::log('');
        
        $progress = \WP_CLI\Utils\make_progress_bar('Generating metadata', count($post_ids));
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $result = $bulk_edit->bulk_generate($post_ids, [
            'meta_types' => $meta_types,
            'overwrite' => $overwrite
        ]);
        
        $progress->finish();
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::log('');
        WP_CLI::success(sprintf(
            'Generated metadata for %d posts. Skipped: %d, Failed: %d',
            $result['success'],
            $result['skipped'],
            $result['failed']
        ));
        
        if (!empty($result['errors'])) {
            WP_CLI::warning('Errors encountered:');
            foreach (array_slice($result['errors'], 0, 10) as $error) {
                WP_CLI::log('  - ' . $error);
            }
            if (count($result['errors']) > 10) {
                WP_CLI::log(sprintf('  ... and %d more errors', count($result['errors']) - 10));
            }
        }
    }
    
    /**
     * Preview bulk changes without applying them
     *
     * ## OPTIONS
     *
     * <post-ids>
     * : Comma-separated list of post IDs
     *
     * [--meta-title=<title>]
     * : Meta title to preview
     *
     * [--meta-description=<description>]
     * : Meta description to preview
     *
     * [--focus-keyword=<keyword>]
     * : Focus keyword to preview
     *
     * ## EXAMPLES
     *
     *     wp aiseo bulk preview 123,456 --focus-keyword="wordpress seo"
     *     wp aiseo bulk preview 123 --meta-title="New Title"
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function preview($args, $assoc_args) {
        $post_ids_str = $args[0];
        $post_ids = array_map('absint', explode(',', $post_ids_str));
        
        if (empty($post_ids)) {
            WP_CLI::error('No post IDs provided.');
        }
        
        // Build updates array
        $updates = [];
        foreach ($post_ids as $post_id) {
            $update = ['post_id' => $post_id];
            
            if (isset($assoc_args['meta-title'])) {
                $update['meta_title'] = $assoc_args['meta-title'];
            }
            if (isset($assoc_args['meta-description'])) {
                $update['meta_description'] = $assoc_args['meta-description'];
            }
            if (isset($assoc_args['focus-keyword'])) {
                $update['focus_keyword'] = $assoc_args['focus-keyword'];
            }
            
            $updates[] = $update;
        }
        
        $bulk_edit = new AISEO_Bulk_Edit();
        $preview = $bulk_edit->preview_changes($updates);
        
        if (is_wp_error($preview)) {
            WP_CLI::error($preview->get_error_message());
        }
        
        WP_CLI::log('');
        WP_CLI::log('=== Preview of Changes ===');
        WP_CLI::log('');
        
        foreach ($preview as $change) {
            WP_CLI::log(sprintf('Post ID: %d - %s', $change['post_id'], $change['post_title']));
            WP_CLI::log('');
            
            foreach ($change['after'] as $field => $new_value) {
                $old_value = isset($change['before'][$field]) ? $change['before'][$field] : '';
                WP_CLI::log(sprintf('  %s:', ucwords(str_replace('_', ' ', $field))));
                WP_CLI::log(sprintf('    Before: %s', $old_value ?: '(empty)'));
                WP_CLI::log(sprintf('    After:  %s', $new_value));
                WP_CLI::log('');
            }
            
            WP_CLI::log('---');
            WP_CLI::log('');
        }
        
        WP_CLI::success(sprintf('Preview complete for %d posts.', count($preview)));
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo bulk', 'AISEO_Bulk_CLI');
