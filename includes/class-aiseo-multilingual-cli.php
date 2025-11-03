<?php
/**
 * AISEO Multilingual WP-CLI Commands
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
 * Multilingual WP-CLI Commands
 */
class AISEO_Multilingual_CLI {
    
    /**
     * Get active multilingual plugin
     *
     * ## EXAMPLES
     *
     *     wp aiseo multilingual plugin
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function plugin($args, $assoc_args) {
        $multilingual = new AISEO_Multilingual();
        $plugin = $multilingual->get_active_plugin();
        
        if ($plugin) {
            WP_CLI::success(sprintf('Active multilingual plugin: %s', $plugin));
        } else {
            WP_CLI::warning('No multilingual plugin detected');
            WP_CLI::log('Supported plugins: WPML, Polylang, TranslatePress');
        }
    }
    
    /**
     * List available languages
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
     *   - ids
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo multilingual languages
     *     wp aiseo multilingual languages --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function languages($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $multilingual = new AISEO_Multilingual();
        $plugin = $multilingual->get_active_plugin();
        
        if (!$plugin) {
            WP_CLI::error('No multilingual plugin detected');
        }
        
        $languages = $multilingual->get_languages();
        
        if (empty($languages)) {
            WP_CLI::warning('No languages found');
            return;
        }
        
        WP_CLI::log(sprintf('Plugin: %s', $plugin));
        WP_CLI::log(sprintf('Languages: %d', count($languages)));
        WP_CLI::log('');
        
        if ($format === 'ids') {
            $codes = array_column($languages, 'code');
            WP_CLI::log(implode(' ', $codes));
        } else {
            WP_CLI\Utils\format_items($format, $languages, ['code', 'name', 'locale', 'active']);
        }
    }
    
    /**
     * Get translations for a post
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo multilingual translations 123
     *     wp aiseo multilingual translations 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function translations($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (!$post_id) {
            WP_CLI::error('Invalid post ID');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error(sprintf('Post %d not found', $post_id));
        }
        
        $multilingual = new AISEO_Multilingual();
        $translations = $multilingual->get_post_translations($post_id);
        
        if (empty($translations)) {
            WP_CLI::warning(sprintf('No translations found for post %d', $post_id));
            return;
        }
        
        WP_CLI::log(sprintf('Post: %s (ID: %d)', $post->post_title, $post_id));
        WP_CLI::log(sprintf('Translations: %d', count($translations)));
        WP_CLI::log('');
        
        WP_CLI\Utils\format_items($format, $translations, ['language', 'post_id', 'title', 'url']);
    }
    
    /**
     * Get hreflang tags for a post
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
     *   - html
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo multilingual hreflang 123
     *     wp aiseo multilingual hreflang 123 --format=html
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function hreflang($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (!$post_id) {
            WP_CLI::error('Invalid post ID');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error(sprintf('Post %d not found', $post_id));
        }
        
        $multilingual = new AISEO_Multilingual();
        $hreflang_tags = $multilingual->generate_hreflang_tags($post_id);
        
        if (empty($hreflang_tags)) {
            WP_CLI::warning(sprintf('No hreflang tags for post %d', $post_id));
            return;
        }
        
        WP_CLI::log(sprintf('Post: %s (ID: %d)', $post->post_title, $post_id));
        WP_CLI::log(sprintf('Hreflang tags: %d', count($hreflang_tags)));
        WP_CLI::log('');
        
        if ($format === 'html') {
            foreach ($hreflang_tags as $tag) {
                WP_CLI::log(sprintf('<link rel="alternate" hreflang="%s" href="%s" />', 
                    $tag['hreflang'], $tag['href']));
            }
        } else {
            WP_CLI\Utils\format_items($format, $hreflang_tags, ['hreflang', 'href']);
        }
    }
    
    /**
     * Sync SEO metadata across translations
     *
     * ## OPTIONS
     *
     * <post-id>
     * : Source post ID
     *
     * [--overwrite]
     * : Overwrite existing metadata in translations
     *
     * [--dry-run]
     * : Preview changes without applying
     *
     * ## EXAMPLES
     *
     *     wp aiseo multilingual sync 123
     *     wp aiseo multilingual sync 123 --overwrite
     *     wp aiseo multilingual sync 123 --dry-run
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function sync($args, $assoc_args) {
        $post_id = absint($args[0]);
        $overwrite = isset($assoc_args['overwrite']);
        $dry_run = isset($assoc_args['dry-run']);
        
        if (!$post_id) {
            WP_CLI::error('Invalid post ID');
        }
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error(sprintf('Post %d not found', $post_id));
        }
        
        $multilingual = new AISEO_Multilingual();
        $plugin = $multilingual->get_active_plugin();
        
        if (!$plugin) {
            WP_CLI::error('No multilingual plugin detected');
        }
        
        WP_CLI::log(sprintf('Source post: %s (ID: %d)', $post->post_title, $post_id));
        WP_CLI::log(sprintf('Multilingual plugin: %s', $plugin));
        WP_CLI::log(sprintf('Overwrite: %s', $overwrite ? 'Yes' : 'No'));
        
        if ($dry_run) {
            WP_CLI::log('');
            WP_CLI::warning('DRY RUN - No changes will be made');
        }
        
        WP_CLI::log('');
        WP_CLI::log('Syncing metadata...');
        
        if (!$dry_run) {
            $result = $multilingual->sync_metadata_across_translations($post_id, $overwrite);
            
            WP_CLI::log('');
            WP_CLI::success(sprintf(
                'Synced metadata to %d language(s)',
                $result['synced_languages']
            ));
            
            if (!empty($result['results'])) {
                foreach ($result['results'] as $lang => $lang_result) {
                    WP_CLI::log(sprintf(
                        '  %s: Copied %d, Skipped %d',
                        $lang,
                        $lang_result['copied'],
                        $lang_result['skipped']
                    ));
                }
            }
        } else {
            $translations = $multilingual->get_post_translations($post_id);
            WP_CLI::log(sprintf('Would sync to %d translation(s)', count($translations) - 1));
            
            foreach ($translations as $translation) {
                if ($translation['post_id'] != $post_id) {
                    WP_CLI::log(sprintf('  - %s (ID: %d)', $translation['language'], $translation['post_id']));
                }
            }
        }
    }
    
    /**
     * Bulk sync metadata for multiple posts
     *
     * ## OPTIONS
     *
     * [--post-type=<type>]
     * : Post type to sync
     * ---
     * default: post
     * ---
     *
     * [--limit=<number>]
     * : Limit number of posts
     * ---
     * default: -1
     * ---
     *
     * [--overwrite]
     * : Overwrite existing metadata
     *
     * ## EXAMPLES
     *
     *     wp aiseo multilingual bulk-sync
     *     wp aiseo multilingual bulk-sync --post-type=page --limit=50
     *     wp aiseo multilingual bulk-sync --overwrite
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk_sync($args, $assoc_args) {
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : -1;
        $overwrite = isset($assoc_args['overwrite']);
        
        $multilingual = new AISEO_Multilingual();
        $plugin = $multilingual->get_active_plugin();
        
        if (!$plugin) {
            WP_CLI::error('No multilingual plugin detected');
        }
        
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => $limit,
            'post_status' => 'publish'
        ]);
        
        if (empty($posts)) {
            WP_CLI::warning(sprintf('No %s posts found', $post_type));
            return;
        }
        
        WP_CLI::log(sprintf('Multilingual plugin: %s', $plugin));
        WP_CLI::log(sprintf('Post type: %s', $post_type));
        WP_CLI::log(sprintf('Posts to sync: %d', count($posts)));
        WP_CLI::log(sprintf('Overwrite: %s', $overwrite ? 'Yes' : 'No'));
        WP_CLI::log('');
        
        $progress = \WP_CLI\Utils\make_progress_bar('Syncing metadata', count($posts));
        
        $total_synced = 0;
        $total_copied = 0;
        $total_skipped = 0;
        
        foreach ($posts as $post) {
            $result = $multilingual->sync_metadata_across_translations($post->ID, $overwrite);
            
            if ($result['success'] && $result['synced_languages'] > 0) {
                $total_synced++;
                
                foreach ($result['results'] as $lang_result) {
                    $total_copied += $lang_result['copied'];
                    $total_skipped += $lang_result['skipped'];
                }
            }
            
            $progress->tick();
        }
        
        $progress->finish();
        
        WP_CLI::log('');
        WP_CLI::success(sprintf(
            'Bulk sync complete. Posts: %d, Copied: %d, Skipped: %d',
            $total_synced,
            $total_copied,
            $total_skipped
        ));
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo multilingual plugin', ['AISEO_Multilingual_CLI', 'plugin']);
WP_CLI::add_command('aiseo multilingual languages', ['AISEO_Multilingual_CLI', 'languages']);
WP_CLI::add_command('aiseo multilingual translations', ['AISEO_Multilingual_CLI', 'translations']);
WP_CLI::add_command('aiseo multilingual hreflang', ['AISEO_Multilingual_CLI', 'hreflang']);
WP_CLI::add_command('aiseo multilingual sync', ['AISEO_Multilingual_CLI', 'sync']);
WP_CLI::add_command('aiseo multilingual bulk-sync', ['AISEO_Multilingual_CLI', 'bulk_sync']);
