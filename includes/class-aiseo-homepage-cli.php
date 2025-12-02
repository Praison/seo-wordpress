<?php
/**
 * WP-CLI Commands for AISEO Homepage SEO
 *
 * Provides CLI commands to manage homepage and blog page SEO settings.
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manage homepage and blog page SEO settings.
 *
 * ## EXAMPLES
 *
 *     # Get all homepage SEO settings
 *     wp aiseo homepage get
 *
 *     # Set homepage title
 *     wp aiseo homepage set --home-title="My Site | Best SEO Plugin"
 *
 *     # Set blog page description
 *     wp aiseo homepage set --blog-description="Read our latest blog posts"
 *
 * @package AISEO
 */
class AISEO_Homepage_CLI_Command {

    /**
     * Get homepage SEO settings.
     *
     * ## OPTIONS
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
     *     # Get all settings as table
     *     wp aiseo homepage get
     *
     *     # Get settings as JSON
     *     wp aiseo homepage get --format=json
     *
     * @when after_wp_load
     */
    public function get($args, $assoc_args) {
        $homepage_seo = new AISEO_Homepage_SEO();
        $settings = $homepage_seo->get_settings();
        
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($settings, JSON_PRETTY_PRINT));
            return;
        }
        
        $items = array();
        foreach ($settings as $key => $value) {
            $items[] = array(
                'setting' => $key,
                'value' => $value ?: '(not set)',
            );
        }
        
        WP_CLI\Utils\format_items($format, $items, array('setting', 'value'));
    }

    /**
     * Set homepage SEO settings.
     *
     * ## OPTIONS
     *
     * [--home-title=<title>]
     * : Homepage title.
     *
     * [--home-description=<description>]
     * : Homepage meta description.
     *
     * [--home-keywords=<keywords>]
     * : Homepage meta keywords.
     *
     * [--blog-title=<title>]
     * : Blog page title.
     *
     * [--blog-description=<description>]
     * : Blog page meta description.
     *
     * [--blog-keywords=<keywords>]
     * : Blog page meta keywords.
     *
     * ## EXAMPLES
     *
     *     # Set homepage title
     *     wp aiseo homepage set --home-title="My Site | Best SEO Plugin"
     *
     *     # Set multiple settings
     *     wp aiseo homepage set --home-title="My Site" --home-description="Welcome to my site"
     *
     *     # Set blog page settings
     *     wp aiseo homepage set --blog-title="Blog" --blog-description="Read our latest posts"
     *
     * @when after_wp_load
     */
    public function set($args, $assoc_args) {
        $homepage_seo = new AISEO_Homepage_SEO();
        
        $settings = array();
        $updated_count = 0;
        
        if (isset($assoc_args['home-title'])) {
            $settings['home_title'] = $assoc_args['home-title'];
            $updated_count++;
        }
        
        if (isset($assoc_args['home-description'])) {
            $settings['home_description'] = $assoc_args['home-description'];
            $updated_count++;
        }
        
        if (isset($assoc_args['home-keywords'])) {
            $settings['home_keywords'] = $assoc_args['home-keywords'];
            $updated_count++;
        }
        
        if (isset($assoc_args['blog-title'])) {
            $settings['blog_title'] = $assoc_args['blog-title'];
            $updated_count++;
        }
        
        if (isset($assoc_args['blog-description'])) {
            $settings['blog_description'] = $assoc_args['blog-description'];
            $updated_count++;
        }
        
        if (isset($assoc_args['blog-keywords'])) {
            $settings['blog_keywords'] = $assoc_args['blog-keywords'];
            $updated_count++;
        }
        
        if (empty($settings)) {
            WP_CLI::error('No settings provided. Use --home-title, --home-description, etc.');
        }
        
        $homepage_seo->update_settings($settings);
        
        WP_CLI::success("Updated {$updated_count} homepage SEO setting(s).");
        
        // Show updated settings
        $this->get($args, array('format' => 'table'));
    }

    /**
     * Clear homepage SEO settings.
     *
     * ## OPTIONS
     *
     * [--all]
     * : Clear all homepage SEO settings.
     *
     * [--home]
     * : Clear only homepage settings.
     *
     * [--blog]
     * : Clear only blog page settings.
     *
     * ## EXAMPLES
     *
     *     # Clear all settings
     *     wp aiseo homepage clear --all
     *
     *     # Clear only homepage settings
     *     wp aiseo homepage clear --home
     *
     * @when after_wp_load
     */
    public function clear($args, $assoc_args) {
        $homepage_seo = new AISEO_Homepage_SEO();
        
        $clear_all = WP_CLI\Utils\get_flag_value($assoc_args, 'all', false);
        $clear_home = WP_CLI\Utils\get_flag_value($assoc_args, 'home', false);
        $clear_blog = WP_CLI\Utils\get_flag_value($assoc_args, 'blog', false);
        
        if (!$clear_all && !$clear_home && !$clear_blog) {
            WP_CLI::error('Please specify --all, --home, or --blog.');
        }
        
        $settings = array();
        
        if ($clear_all || $clear_home) {
            $settings['home_title'] = '';
            $settings['home_description'] = '';
            $settings['home_keywords'] = '';
        }
        
        if ($clear_all || $clear_blog) {
            $settings['blog_title'] = '';
            $settings['blog_description'] = '';
            $settings['blog_keywords'] = '';
        }
        
        $homepage_seo->update_settings($settings);
        
        WP_CLI::success('Homepage SEO settings cleared.');
    }

    /**
     * Generate AI-powered homepage SEO suggestions.
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Type of content to generate.
     * ---
     * default: all
     * options:
     *   - title
     *   - description
     *   - all
     * ---
     *
     * [--apply]
     * : Apply the generated suggestions immediately.
     *
     * ## EXAMPLES
     *
     *     # Generate suggestions
     *     wp aiseo homepage generate
     *
     *     # Generate and apply title
     *     wp aiseo homepage generate --type=title --apply
     *
     * @when after_wp_load
     */
    public function generate($args, $assoc_args) {
        // Check API key
        $api_key = AISEO_Helpers::get_api_key();
        if (empty($api_key)) {
            WP_CLI::error('OpenAI API key not configured. Please set it in Settings → AISEO.');
        }
        
        $type = WP_CLI\Utils\get_flag_value($assoc_args, 'type', 'all');
        $apply = WP_CLI\Utils\get_flag_value($assoc_args, 'apply', false);
        
        $site_name = get_bloginfo('name');
        $site_description = get_bloginfo('description');
        
        WP_CLI::log("Generating SEO suggestions for: {$site_name}");
        
        $api = new AISEO_API();
        $suggestions = array();
        
        if ($type === 'all' || $type === 'title') {
            WP_CLI::log('Generating homepage title...');
            $title_result = $api->generate_title("{$site_name} - {$site_description}", $site_name);
            if (!is_wp_error($title_result) && !empty($title_result['title'])) {
                $suggestions['home_title'] = $title_result['title'];
                WP_CLI::log(WP_CLI::colorize("%GTitle:%n {$title_result['title']}"));
            }
        }
        
        if ($type === 'all' || $type === 'description') {
            WP_CLI::log('Generating homepage description...');
            $desc_result = $api->generate_description("{$site_name} - {$site_description}", $site_name);
            if (!is_wp_error($desc_result) && !empty($desc_result['description'])) {
                $suggestions['home_description'] = $desc_result['description'];
                WP_CLI::log(WP_CLI::colorize("%GDescription:%n {$desc_result['description']}"));
            }
        }
        
        if ($apply && !empty($suggestions)) {
            $homepage_seo = new AISEO_Homepage_SEO();
            $homepage_seo->update_settings($suggestions);
            WP_CLI::success('Applied generated suggestions.');
        } elseif (!empty($suggestions)) {
            WP_CLI::log('');
            WP_CLI::log('Use --apply flag to save these suggestions.');
        }
    }
}

// Register the command
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo homepage', 'AISEO_Homepage_CLI_Command');
}
