<?php
/**
 * WP-CLI Commands for AISEO Taxonomy SEO
 *
 * Provides CLI commands to manage taxonomy SEO settings.
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manage taxonomy SEO settings for categories, tags, and custom taxonomies.
 *
 * ## EXAMPLES
 *
 *     # Get SEO settings for a category
 *     wp aiseo taxonomy get category 1
 *
 *     # Set SEO title for a tag
 *     wp aiseo taxonomy set post_tag 5 --title="My Tag SEO Title"
 *
 *     # List all categories with SEO settings
 *     wp aiseo taxonomy list category
 *
 * @package AISEO
 */
class AISEO_Taxonomy_CLI_Command {

    /**
     * Get SEO settings for a taxonomy term.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : The taxonomy name (category, post_tag, or custom taxonomy).
     *
     * <term_id>
     * : The term ID.
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     # Get SEO settings for category ID 1
     *     wp aiseo taxonomy get category 1
     *
     *     # Get settings as JSON
     *     wp aiseo taxonomy get post_tag 5 --format=json
     *
     * @when after_wp_load
     */
    public function get($args, $assoc_args) {
        list($taxonomy, $term_id) = $args;
        
        // Verify taxonomy
        if (!taxonomy_exists($taxonomy)) {
            WP_CLI::error("Taxonomy '{$taxonomy}' does not exist.");
        }
        
        // Verify term
        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) {
            WP_CLI::error("Term ID {$term_id} not found in taxonomy '{$taxonomy}'.");
        }
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        $meta = $taxonomy_seo->get_term_meta($term_id, $taxonomy);
        
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
        
        WP_CLI::log("Term: {$term->name} (ID: {$term_id})");
        WP_CLI::log("Taxonomy: {$taxonomy}");
        WP_CLI::log("URL: " . get_term_link($term));
        WP_CLI::log("");
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($meta, JSON_PRETTY_PRINT));
            return;
        }
        
        $items = array();
        foreach ($meta as $key => $value) {
            $display_value = is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?: '(not set)');
            $items[] = array(
                'setting' => $key,
                'value' => $display_value,
            );
        }
        
        WP_CLI\Utils\format_items($format, $items, array('setting', 'value'));
    }

    /**
     * Set SEO settings for a taxonomy term.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : The taxonomy name (category, post_tag, or custom taxonomy).
     *
     * <term_id>
     * : The term ID.
     *
     * [--title=<title>]
     * : SEO title for the term.
     *
     * [--description=<description>]
     * : Meta description for the term.
     *
     * [--keywords=<keywords>]
     * : Meta keywords for the term.
     *
     * [--canonical=<url>]
     * : Canonical URL for the term.
     *
     * [--noindex]
     * : Set noindex for the term.
     *
     * [--nofollow]
     * : Set nofollow for the term.
     *
     * [--index]
     * : Remove noindex (allow indexing).
     *
     * [--follow]
     * : Remove nofollow (allow following).
     *
     * ## EXAMPLES
     *
     *     # Set SEO title for a category
     *     wp aiseo taxonomy set category 1 --title="My Category | Site Name"
     *
     *     # Set multiple settings
     *     wp aiseo taxonomy set post_tag 5 --title="Tag Title" --description="Tag description"
     *
     *     # Set noindex for a category
     *     wp aiseo taxonomy set category 1 --noindex
     *
     * @when after_wp_load
     */
    public function set($args, $assoc_args) {
        list($taxonomy, $term_id) = $args;
        
        // Verify taxonomy
        if (!taxonomy_exists($taxonomy)) {
            WP_CLI::error("Taxonomy '{$taxonomy}' does not exist.");
        }
        
        // Verify term
        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) {
            WP_CLI::error("Term ID {$term_id} not found in taxonomy '{$taxonomy}'.");
        }
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        
        $meta = array();
        $updated_count = 0;
        
        if (isset($assoc_args['title'])) {
            $meta['title'] = $assoc_args['title'];
            $updated_count++;
        }
        
        if (isset($assoc_args['description'])) {
            $meta['description'] = $assoc_args['description'];
            $updated_count++;
        }
        
        if (isset($assoc_args['keywords'])) {
            $meta['keywords'] = $assoc_args['keywords'];
            $updated_count++;
        }
        
        if (isset($assoc_args['canonical'])) {
            $meta['canonical'] = $assoc_args['canonical'];
            $updated_count++;
        }
        
        if (WP_CLI\Utils\get_flag_value($assoc_args, 'noindex', false)) {
            $meta['noindex'] = true;
            $updated_count++;
        } elseif (WP_CLI\Utils\get_flag_value($assoc_args, 'index', false)) {
            $meta['noindex'] = false;
            $updated_count++;
        }
        
        if (WP_CLI\Utils\get_flag_value($assoc_args, 'nofollow', false)) {
            $meta['nofollow'] = true;
            $updated_count++;
        } elseif (WP_CLI\Utils\get_flag_value($assoc_args, 'follow', false)) {
            $meta['nofollow'] = false;
            $updated_count++;
        }
        
        if (empty($meta)) {
            WP_CLI::error('No settings provided. Use --title, --description, --noindex, etc.');
        }
        
        $taxonomy_seo->update_term_meta($term_id, $taxonomy, $meta);
        
        WP_CLI::success("Updated {$updated_count} SEO setting(s) for {$term->name}.");
        
        // Show updated settings
        $this->get($args, array('format' => 'table'));
    }

    /**
     * List all terms in a taxonomy with their SEO settings.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : The taxonomy name (category, post_tag, or custom taxonomy).
     *
     * [--format=<format>]
     * : Output format.
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
     *     # List all categories with SEO
     *     wp aiseo taxonomy list category
     *
     *     # Export as JSON
     *     wp aiseo taxonomy list post_tag --format=json
     *
     * @when after_wp_load
     */
    public function list_($args, $assoc_args) {
        list($taxonomy) = $args;
        
        // Verify taxonomy
        if (!taxonomy_exists($taxonomy)) {
            WP_CLI::error("Taxonomy '{$taxonomy}' does not exist.");
        }
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        $terms = $taxonomy_seo->get_taxonomy_terms_with_meta($taxonomy);
        
        if (empty($terms)) {
            WP_CLI::warning("No terms found in taxonomy '{$taxonomy}'.");
            return;
        }
        
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($terms, JSON_PRETTY_PRINT));
            return;
        }
        
        // Flatten for table display
        $items = array();
        foreach ($terms as $term) {
            $items[] = array(
                'ID' => $term['term_id'],
                'Name' => $term['name'],
                'Slug' => $term['slug'],
                'Posts' => $term['count'],
                'SEO Title' => $term['seo']['title'] ?: '-',
                'NoIndex' => $term['seo']['noindex'] ? 'Yes' : 'No',
            );
        }
        
        WP_CLI\Utils\format_items($format, $items, array('ID', 'Name', 'Slug', 'Posts', 'SEO Title', 'NoIndex'));
    }

    /**
     * Clear SEO settings for a taxonomy term.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : The taxonomy name.
     *
     * <term_id>
     * : The term ID.
     *
     * [--yes]
     * : Skip confirmation.
     *
     * ## EXAMPLES
     *
     *     # Clear SEO settings for a category
     *     wp aiseo taxonomy clear category 1
     *
     * @when after_wp_load
     */
    public function clear($args, $assoc_args) {
        list($taxonomy, $term_id) = $args;
        
        // Verify taxonomy
        if (!taxonomy_exists($taxonomy)) {
            WP_CLI::error("Taxonomy '{$taxonomy}' does not exist.");
        }
        
        // Verify term
        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) {
            WP_CLI::error("Term ID {$term_id} not found in taxonomy '{$taxonomy}'.");
        }
        
        WP_CLI::confirm("Clear all SEO settings for '{$term->name}'?", $assoc_args);
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        $taxonomy_seo->update_term_meta($term_id, $taxonomy, array(
            'title' => '',
            'description' => '',
            'keywords' => '',
            'canonical' => '',
            'noindex' => false,
            'nofollow' => false,
        ));
        
        WP_CLI::success("Cleared SEO settings for '{$term->name}'.");
    }

    /**
     * Bulk update SEO settings for all terms in a taxonomy.
     *
     * ## OPTIONS
     *
     * <taxonomy>
     * : The taxonomy name.
     *
     * [--noindex]
     * : Set noindex for all terms.
     *
     * [--nofollow]
     * : Set nofollow for all terms.
     *
     * [--index]
     * : Remove noindex from all terms.
     *
     * [--follow]
     * : Remove nofollow from all terms.
     *
     * [--yes]
     * : Skip confirmation.
     *
     * ## EXAMPLES
     *
     *     # Set noindex for all tags
     *     wp aiseo taxonomy bulk post_tag --noindex
     *
     * @when after_wp_load
     */
    public function bulk($args, $assoc_args) {
        list($taxonomy) = $args;
        
        // Verify taxonomy
        if (!taxonomy_exists($taxonomy)) {
            WP_CLI::error("Taxonomy '{$taxonomy}' does not exist.");
        }
        
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (is_wp_error($terms) || empty($terms)) {
            WP_CLI::error("No terms found in taxonomy '{$taxonomy}'.");
        }
        
        $meta = array();
        
        if (WP_CLI\Utils\get_flag_value($assoc_args, 'noindex', false)) {
            $meta['noindex'] = true;
        } elseif (WP_CLI\Utils\get_flag_value($assoc_args, 'index', false)) {
            $meta['noindex'] = false;
        }
        
        if (WP_CLI\Utils\get_flag_value($assoc_args, 'nofollow', false)) {
            $meta['nofollow'] = true;
        } elseif (WP_CLI\Utils\get_flag_value($assoc_args, 'follow', false)) {
            $meta['nofollow'] = false;
        }
        
        if (empty($meta)) {
            WP_CLI::error('No settings provided. Use --noindex, --nofollow, --index, or --follow.');
        }
        
        $count = count($terms);
        WP_CLI::confirm("Update SEO settings for {$count} terms in '{$taxonomy}'?", $assoc_args);
        
        $taxonomy_seo = new AISEO_Taxonomy_SEO();
        $progress = \WP_CLI\Utils\make_progress_bar("Updating terms", $count);
        
        foreach ($terms as $term) {
            $taxonomy_seo->update_term_meta($term->term_id, $taxonomy, $meta);
            $progress->tick();
        }
        
        $progress->finish();
        WP_CLI::success("Updated {$count} terms in '{$taxonomy}'.");
    }
}

// Register the command
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo taxonomy', 'AISEO_Taxonomy_CLI_Command');
}
