<?php
/**
 * WP-CLI Commands for AISEO Plugin
 *
 * Provides CLI commands to generate and manage SEO metadata via command line.
 * All metadata that gets generated on page load can be generated via CLI.
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AISEO WP-CLI Command Class
 */
class AISEO_CLI_Command {

    /**
     * Generate SEO metadata for posts/pages by slug or ID
     *
     * ## OPTIONS
     *
     * [<slug>]
     * : Post slug to generate metadata for. Can also use post ID.
     *
     * [--id=<id>]
     * : Post ID(s) to generate metadata for. Comma-separated for multiple posts.
     *
     * [--slug=<slug>]
     * : Post slug(s) to generate metadata for. Comma-separated for multiple posts.
     *
     * [--post-type=<post-type>]
     * : Post type to filter by. Default: post,page
     *
     * [--all]
     * : Generate metadata for all published posts and pages.
     *
     * [--meta=<meta>]
     * : Specific metadata to generate. Options: title, description, schema, social, analysis, all
     * ---
     * default: all
     * options:
     *   - title
     *   - description
     *   - schema
     *   - social
     *   - analysis
     *   - all
     * ---
     *
     * [--force]
     * : Force regeneration even if metadata already exists.
     *
     * [--dry-run]
     * : Preview what would be generated without saving.
     *
     * ## EXAMPLES
     *
     *     # Generate all metadata for a post by slug
     *     wp aiseo generate my-post-slug
     *
     *     # Generate all metadata for a post by ID
     *     wp aiseo generate --id=123
     *
     *     # Generate only title and description for multiple posts
     *     wp aiseo generate --id=1,2,3 --meta=title,description
     *
     *     # Generate metadata for all posts
     *     wp aiseo generate --all --post-type=post
     *
     *     # Dry run to preview what would be generated
     *     wp aiseo generate --slug=my-post --dry-run
     *
     * @when after_wp_load
     */
    public function generate($args, $assoc_args) {
        // Check if API key is configured
        if (!$this->check_api_key()) {
            WP_CLI::error('OpenAI API key not configured. Please set it in Settings → AISEO.');
        }

        $post_ids = $this->get_post_ids($args, $assoc_args);

        if (empty($post_ids)) {
            WP_CLI::error('No posts found matching the criteria.');
        }

        $meta_types = $this->parse_meta_types($assoc_args);
        $force = WP_CLI\Utils\get_flag_value($assoc_args, 'force', false);
        $dry_run = WP_CLI\Utils\get_flag_value($assoc_args, 'dry-run', false);

        if ($dry_run) {
            WP_CLI::log(WP_CLI::colorize('%YDry run mode - no data will be saved%n'));
        }

        $progress = \WP_CLI\Utils\make_progress_bar(
            'Generating metadata',
            count($post_ids)
        );

        $success_count = 0;
        $error_count = 0;
        $skipped_count = 0;

        foreach ($post_ids as $post_id) {
            $result = $this->generate_post_metadata(
                $post_id,
                $meta_types,
                $force,
                $dry_run
            );

            if ($result['status'] === 'success') {
                $success_count++;
            } elseif ($result['status'] === 'skipped') {
                $skipped_count++;
            } else {
                $error_count++;
                WP_CLI::warning("Post ID {$post_id}: {$result['message']}");
            }

            $progress->tick();
        }

        $progress->finish();

        // Summary
        WP_CLI::log('');
        WP_CLI::success(sprintf(
            'Processed %d posts: %d successful, %d skipped, %d errors',
            count($post_ids),
            $success_count,
            $skipped_count,
            $error_count
        ));
    }

    /**
     * Analyze SEO score for posts/pages
     *
     * ## OPTIONS
     *
     * [<slug>]
     * : Post slug to analyze.
     *
     * [--id=<id>]
     * : Post ID(s) to analyze. Comma-separated for multiple posts.
     *
     * [--slug=<slug>]
     * : Post slug(s) to analyze. Comma-separated for multiple posts.
     *
     * [--all]
     * : Analyze all published posts and pages.
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     # Analyze a single post
     *     wp aiseo analyze my-post-slug
     *
     *     # Analyze multiple posts and output as JSON
     *     wp aiseo analyze --id=1,2,3 --format=json
     *
     *     # Analyze all posts
     *     wp aiseo analyze --all
     *
     * @when after_wp_load
     */
    public function analyze($args, $assoc_args) {
        $post_ids = $this->get_post_ids($args, $assoc_args);

        if (empty($post_ids)) {
            WP_CLI::error('No posts found matching the criteria.');
        }

        $results = [];

        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post) {
                continue;
            }

            $analysis = $this->perform_analysis($post_id);
            
            $results[] = [
                'ID' => $post_id,
                'Title' => get_the_title($post_id),
                'Slug' => $post->post_name,
                'SEO Score' => $analysis['overall_score'] ?? 0,
                'Status' => $this->get_score_status($analysis['overall_score'] ?? 0),
            ];
        }

        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
        WP_CLI\Utils\format_items($format, $results, ['ID', 'Title', 'Slug', 'SEO Score', 'Status']);
    }

    /**
     * Get or update SEO metadata for a post
     *
     * ## OPTIONS
     *
     * <action>
     * : Action to perform: get, update, delete
     *
     * <post-id>
     * : Post ID
     *
     * <meta-key>
     * : Meta key (e.g., focus_keyword, meta_title, meta_description)
     *
     * [<meta-value>]
     * : Meta value (required for update action)
     *
     * ## EXAMPLES
     *
     *     # Get focus keyword
     *     wp aiseo meta get 123 focus_keyword
     *
     *     # Update meta title
     *     wp aiseo meta update 123 meta_title "New SEO Title"
     *
     *     # Delete meta description
     *     wp aiseo meta delete 123 meta_description
     *
     * @when after_wp_load
     */
    public function meta($args, $assoc_args) {
        list($action, $post_id, $meta_key) = array_pad($args, 3, null);
        $meta_value = isset($args[3]) ? $args[3] : null;

        if (!in_array($action, ['get', 'update', 'delete'])) {
            WP_CLI::error('Invalid action. Use: get, update, or delete');
        }

        if (!$post_id || !is_numeric($post_id)) {
            WP_CLI::error('Invalid post ID');
        }

        if (!$meta_key) {
            WP_CLI::error('Meta key is required');
        }

        // Convert short key to full meta key
        $full_meta_key = $this->get_full_meta_key($meta_key);

        switch ($action) {
            case 'get':
                $value = get_post_meta($post_id, $full_meta_key, true);
                if ($value) {
                    WP_CLI::log($value);
                } else {
                    WP_CLI::warning('Meta key not found or empty');
                }
                break;

            case 'update':
                if ($meta_value === null) {
                    WP_CLI::error('Meta value is required for update action');
                }
                update_post_meta($post_id, $full_meta_key, sanitize_text_field($meta_value));
                WP_CLI::success("Updated {$meta_key} for post {$post_id}");
                break;

            case 'delete':
                delete_post_meta($post_id, $full_meta_key);
                WP_CLI::success("Deleted {$meta_key} for post {$post_id}");
                break;
        }
    }

    /**
     * Clear AISEO cache
     *
     * ## OPTIONS
     *
     * [--post-id=<id>]
     * : Clear cache for specific post ID
     *
     * [--all]
     * : Clear all AISEO caches
     *
     * ## EXAMPLES
     *
     *     # Clear cache for specific post
     *     wp aiseo cache clear --post-id=123
     *
     *     # Clear all caches
     *     wp aiseo cache clear --all
     *
     * @when after_wp_load
     */
    public function cache($args, $assoc_args) {
        $action = isset($args[0]) ? $args[0] : 'clear';

        if ($action !== 'clear') {
            WP_CLI::error('Only "clear" action is supported');
        }

        $post_id = WP_CLI\Utils\get_flag_value($assoc_args, 'post-id', null);
        $all = WP_CLI\Utils\get_flag_value($assoc_args, 'all', false);

        if ($post_id) {
            $this->clear_post_cache($post_id);
            WP_CLI::success("Cleared cache for post {$post_id}");
        } elseif ($all) {
            $this->clear_all_cache();
            WP_CLI::success('Cleared all AISEO caches');
        } else {
            WP_CLI::error('Please specify --post-id or --all');
        }
    }

    /**
     * Export SEO metadata to JSON
     *
     * ## OPTIONS
     *
     * [--post-id=<id>]
     * : Export specific post ID(s). Comma-separated for multiple.
     *
     * [--all]
     * : Export all posts with SEO metadata
     *
     * [--file=<file>]
     * : Output file path. Default: wp-content/uploads/aiseo-export.json
     *
     * ## EXAMPLES
     *
     *     # Export specific posts
     *     wp aiseo export --post-id=1,2,3
     *
     *     # Export all posts to custom file
     *     wp aiseo export --all --file=/path/to/export.json
     *
     * @when after_wp_load
     */
    public function export($args, $assoc_args) {
        $post_ids = [];
        
        if ($id_flag = WP_CLI\Utils\get_flag_value($assoc_args, 'post-id', null)) {
            $post_ids = array_map('intval', explode(',', $id_flag));
        } elseif (WP_CLI\Utils\get_flag_value($assoc_args, 'all', false)) {
            $post_ids = get_posts([
                'post_type' => ['post', 'page'],
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids',
            ]);
        }

        if (empty($post_ids)) {
            WP_CLI::error('No posts to export');
        }

        $export_data = $this->prepare_export_data($post_ids);
        
        $file = WP_CLI\Utils\get_flag_value(
            $assoc_args,
            'file',
            WP_CONTENT_DIR . '/uploads/aiseo-export.json'
        );

        $result = file_put_contents($file, json_encode($export_data, JSON_PRETTY_PRINT));

        if ($result === false) {
            WP_CLI::error("Failed to write to {$file}");
        }

        WP_CLI::success("Exported " . count($post_ids) . " posts to {$file}");
    }

    /**
     * Get post IDs from arguments
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return array Array of post IDs
     */
    private function get_post_ids($args, $assoc_args) {
        $post_ids = [];

        // Check for positional slug argument
        if (!empty($args[0])) {
            $post = get_page_by_path($args[0], OBJECT, ['post', 'page']);
            if ($post) {
                $post_ids[] = $post->ID;
            }
        }

        // Check for --id flag
        if ($id_flag = WP_CLI\Utils\get_flag_value($assoc_args, 'id', null)) {
            $ids = array_map('intval', explode(',', $id_flag));
            $post_ids = array_merge($post_ids, $ids);
        }

        // Check for --slug flag
        if ($slug_flag = WP_CLI\Utils\get_flag_value($assoc_args, 'slug', null)) {
            $slugs = explode(',', $slug_flag);
            foreach ($slugs as $slug) {
                $post = get_page_by_path(trim($slug), OBJECT, ['post', 'page']);
                if ($post) {
                    $post_ids[] = $post->ID;
                }
            }
        }

        // Check for --all flag
        if (WP_CLI\Utils\get_flag_value($assoc_args, 'all', false)) {
            $post_type = WP_CLI\Utils\get_flag_value($assoc_args, 'post-type', 'post,page');
            $post_types = explode(',', $post_type);

            $post_ids = get_posts([
                'post_type' => $post_types,
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids',
            ]);
        }

        return array_unique($post_ids);
    }

    /**
     * Parse meta types from arguments
     *
     * @param array $assoc_args Associative arguments
     * @return array Array of meta types to generate
     */
    private function parse_meta_types($assoc_args) {
        $meta_flag = WP_CLI\Utils\get_flag_value($assoc_args, 'meta', 'all');
        
        if ($meta_flag === 'all') {
            return ['title', 'description', 'schema', 'social', 'analysis'];
        }

        return explode(',', $meta_flag);
    }

    /**
     * Generate metadata for a single post
     *
     * @param int $post_id Post ID
     * @param array $meta_types Types of metadata to generate
     * @param bool $force Force regeneration
     * @param bool $dry_run Dry run mode
     * @return array Result status and message
     */
    private function generate_post_metadata($post_id, $meta_types, $force, $dry_run) {
        $post = get_post($post_id);

        if (!$post) {
            return [
                'status' => 'error',
                'message' => 'Post not found'
            ];
        }

        // Check if metadata already exists and force is not set
        if (!$force && $this->has_metadata($post_id) && !in_array('analysis', $meta_types)) {
            return [
                'status' => 'skipped',
                'message' => 'Metadata already exists (use --force to regenerate)'
            ];
        }

        $content = $post->post_content;
        $focus_keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);

        // If no focus keyword, try to extract from title
        if (empty($focus_keyword)) {
            $focus_keyword = $this->extract_keyword_from_title($post->post_title);
        }

        $generated = [];

        try {
            // Generate meta title
            if (in_array('title', $meta_types)) {
                $title = $this->generate_meta_title($content, $focus_keyword, $post->post_title);
                if (!$dry_run && $title) {
                    update_post_meta($post_id, '_aiseo_meta_title', $title);
                    update_post_meta($post_id, '_aiseo_ai_generated_title', true);
                    update_post_meta($post_id, '_aiseo_generation_timestamp', current_time('mysql'));
                }
                $generated['title'] = $title;
            }

            // Generate meta description
            if (in_array('description', $meta_types)) {
                $description = $this->generate_meta_description($content, $focus_keyword);
                if (!$dry_run && $description) {
                    update_post_meta($post_id, '_aiseo_meta_description', $description);
                    update_post_meta($post_id, '_aiseo_ai_generated_desc', true);
                }
                $generated['description'] = $description;
            }

            // Generate schema markup
            if (in_array('schema', $meta_types)) {
                $schema = $this->generate_schema_markup($post);
                if (!$dry_run && $schema) {
                    update_post_meta($post_id, '_aiseo_schema_type', $post->post_type === 'post' ? 'Article' : 'WebPage');
                }
                $generated['schema'] = 'Generated';
            }

            // Generate social media tags
            if (in_array('social', $meta_types)) {
                $og_title = $generated['title'] ?? get_the_title($post_id);
                $og_description = $generated['description'] ?? wp_trim_words($content, 20);
                
                if (!$dry_run) {
                    update_post_meta($post_id, '_aiseo_og_title', $og_title);
                    update_post_meta($post_id, '_aiseo_og_description', $og_description);
                    update_post_meta($post_id, '_aiseo_twitter_title', $og_title);
                    update_post_meta($post_id, '_aiseo_twitter_description', $og_description);
                }
                $generated['social'] = 'Generated';
            }

            // Perform SEO analysis
            if (in_array('analysis', $meta_types)) {
                $analysis = $this->perform_analysis($post_id);
                if (!$dry_run && $analysis) {
                    update_post_meta($post_id, '_aiseo_seo_score', $analysis['overall_score']);
                    update_post_meta($post_id, '_aiseo_analysis_data', serialize($analysis));
                    update_post_meta($post_id, '_aiseo_last_analyzed', current_time('mysql'));
                }
                $generated['analysis'] = $analysis['overall_score'] ?? 0;
            }

            return [
                'status' => 'success',
                'message' => 'Metadata generated successfully',
                'data' => $generated
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate meta title using AI
     *
     * @param string $content Post content
     * @param string $keyword Focus keyword
     * @param string $original_title Original post title
     * @return string|false Generated title or false on failure
     */
    private function generate_meta_title($content, $keyword, $original_title) {
        if (!class_exists('AISEO_API')) {
            return false;
        }

        $api = new AISEO_API();
        $result = $api->generate_title($content, $keyword);

        if (is_wp_error($result)) {
            WP_CLI::debug('API Error: ' . $result->get_error_message());
            return false;
        }

        return $result;
    }

    /**
     * Generate meta description using AI
     *
     * @param string $content Post content
     * @param string $keyword Focus keyword
     * @return string|false Generated description or false on failure
     */
    private function generate_meta_description($content, $keyword) {
        if (!class_exists('AISEO_API')) {
            return false;
        }

        $api = new AISEO_API();
        $result = $api->generate_meta_description($content, $keyword);

        if (is_wp_error($result)) {
            WP_CLI::debug('API Error: ' . $result->get_error_message());
            return false;
        }

        return $result;
    }

    /**
     * Generate schema markup
     *
     * @param WP_Post $post Post object
     * @return bool Success status
     */
    private function generate_schema_markup($post) {
        if (!class_exists('AISEO_Schema')) {
            return false;
        }

        $schema = new AISEO_Schema();
        
        if ($post->post_type === 'post') {
            $schema->generate_article_schema($post);
        } else {
            $schema->generate_webpage_schema($post);
        }

        return true;
    }

    /**
     * Perform SEO analysis
     *
     * @param int $post_id Post ID
     * @return array Analysis results
     */
    private function perform_analysis($post_id) {
        if (!class_exists('AISEO_Analysis')) {
            return ['overall_score' => 0];
        }

        $post = get_post($post_id);
        $content = $post->post_content;
        $keyword = get_post_meta($post_id, '_aiseo_focus_keyword', true);

        $analysis = new AISEO_Analysis();
        
        $results = [
            'keyword_density' => $analysis->analyze_keyword_density($content, $keyword),
            'readability' => $analysis->analyze_readability($content),
            'paragraph_structure' => $analysis->analyze_paragraph_structure($content),
            'sentence_length' => $analysis->analyze_sentence_length($content),
            'content_length' => $analysis->analyze_content_length($content),
        ];

        $overall_score = $analysis->generate_seo_score($results);
        $results['overall_score'] = $overall_score;

        return $results;
    }

    /**
     * Check if post has existing metadata
     *
     * @param int $post_id Post ID
     * @return bool True if metadata exists
     */
    private function has_metadata($post_id) {
        $title = get_post_meta($post_id, '_aiseo_meta_title', true);
        $description = get_post_meta($post_id, '_aiseo_meta_description', true);

        return !empty($title) || !empty($description);
    }

    /**
     * Extract keyword from title
     *
     * @param string $title Post title
     * @return string Extracted keyword
     */
    private function extract_keyword_from_title($title) {
        // Remove common stop words and get first meaningful words
        $stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for'];
        $words = explode(' ', strtolower($title));
        $words = array_diff($words, $stop_words);
        
        return implode(' ', array_slice($words, 0, 3));
    }

    /**
     * Get full meta key from short key
     *
     * @param string $short_key Short meta key
     * @return string Full meta key with prefix
     */
    private function get_full_meta_key($short_key) {
        $key_map = [
            'focus_keyword' => '_aiseo_focus_keyword',
            'meta_title' => '_aiseo_meta_title',
            'meta_description' => '_aiseo_meta_description',
            'canonical_url' => '_aiseo_canonical_url',
            'robots_index' => '_aiseo_robots_index',
            'robots_follow' => '_aiseo_robots_follow',
            'og_title' => '_aiseo_og_title',
            'og_description' => '_aiseo_og_description',
            'twitter_title' => '_aiseo_twitter_title',
            'twitter_description' => '_aiseo_twitter_description',
            'schema_type' => '_aiseo_schema_type',
            'seo_score' => '_aiseo_seo_score',
        ];

        return isset($key_map[$short_key]) ? $key_map[$short_key] : '_aiseo_' . $short_key;
    }

    /**
     * Get score status label
     *
     * @param int $score SEO score
     * @return string Status label
     */
    private function get_score_status($score) {
        if ($score >= 80) {
            return 'Good';
        } elseif ($score >= 50) {
            return 'Needs Improvement';
        } else {
            return 'Poor';
        }
    }

    /**
     * Clear cache for specific post
     *
     * @param int $post_id Post ID
     */
    private function clear_post_cache($post_id) {
        $cache_keys = [
            'aiseo_meta_desc_' . $post_id,
            'aiseo_meta_title_' . $post_id,
            'aiseo_analysis_' . $post_id,
            'aiseo_schema_' . $post_id,
        ];

        foreach ($cache_keys as $key) {
            delete_transient($key);
            wp_cache_delete($key, 'aiseo_api');
        }
    }

    /**
     * Clear all AISEO caches
     */
    private function clear_all_cache() {
        global $wpdb;

        // Delete all AISEO transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_aiseo_%' 
             OR option_name LIKE '_transient_timeout_aiseo_%'"
        );

        // Clear object cache if available
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('aiseo_api');
            wp_cache_flush_group('aiseo_analysis');
            wp_cache_flush_group('aiseo_schema');
        }
    }

    /**
     * Prepare export data
     *
     * @param array $post_ids Post IDs to export
     * @return array Export data
     */
    private function prepare_export_data($post_ids) {
        $export = [
            'version' => '1.0.0',
            'exported_at' => current_time('mysql'),
            'posts' => []
        ];

        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post) {
                continue;
            }

            $meta_keys = [
                '_aiseo_focus_keyword',
                '_aiseo_meta_title',
                '_aiseo_meta_description',
                '_aiseo_canonical_url',
                '_aiseo_robots_index',
                '_aiseo_robots_follow',
                '_aiseo_og_title',
                '_aiseo_og_description',
                '_aiseo_twitter_title',
                '_aiseo_twitter_description',
                '_aiseo_schema_type',
                '_aiseo_seo_score',
            ];

            $post_data = [
                'id' => $post_id,
                'title' => $post->post_title,
                'slug' => $post->post_name,
                'post_type' => $post->post_type,
                'metadata' => []
            ];

            foreach ($meta_keys as $key) {
                $value = get_post_meta($post_id, $key, true);
                if (!empty($value)) {
                    $post_data['metadata'][$key] = $value;
                }
            }

            if (!empty($post_data['metadata'])) {
                $export['posts'][] = $post_data;
            }
        }

        return $export;
    }

    /**
     * Check if API key is configured
     *
     * @return bool True if API key exists
     */
    private function check_api_key() {
        $api_key = AISEO_Helpers::get_api_key();
        return !empty($api_key);
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo', 'AISEO_CLI_Command');
}
