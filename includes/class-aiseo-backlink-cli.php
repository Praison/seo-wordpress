<?php
/**
 * AISEO Backlink Monitoring WP-CLI Commands
 *
 * @package AISEO
 * @since 1.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Backlink Monitoring WP-CLI Commands
 */
class AISEO_Backlink_CLI {
    
    /**
     * List all backlinks
     *
     * ## OPTIONS
     *
     * [--status=<status>]
     * : Filter by status (active, lost, broken)
     *
     * [--target=<url>]
     * : Filter by target URL
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
     *     wp aiseo backlink list
     *     wp aiseo backlink list --status=active
     *     wp aiseo backlink list --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function list($args, $assoc_args) {
        $status = isset($assoc_args['status']) ? $assoc_args['status'] : '';
        $target = isset($assoc_args['target']) ? $assoc_args['target'] : '';
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $filters = [];
        if ($status) {
            $filters['status'] = $status;
        }
        if ($target) {
            $filters['target_url'] = $target;
        }
        
        $backlink = new AISEO_Backlink();
        $backlinks = $backlink->get_backlinks($filters);
        
        if (empty($backlinks)) {
            WP_CLI::warning('No backlinks found');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($backlinks));
            return;
        }
        
        WP_CLI::success(sprintf('Found %d backlinks', count($backlinks)));
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($backlinks, JSON_PRETTY_PRINT));
        } else {
            $display_data = [];
            foreach ($backlinks as $bl) {
                $display_data[] = [
                    'id' => $bl['id'],
                    'source_url' => $bl['source_url'],
                    'target_url' => $bl['target_url'],
                    'status' => $bl['status'],
                    'follow' => $bl['follow'] ? 'Yes' : 'No',
                    'discovered' => $bl['discovered_at']
                ];
            }
            
            WP_CLI\Utils\format_items($format, $display_data, 
                ['id', 'source_url', 'target_url', 'status', 'follow', 'discovered']);
        }
    }
    
    /**
     * Add a backlink
     *
     * ## OPTIONS
     *
     * <source_url>
     * : Source URL (where the link is from)
     *
     * <target_url>
     * : Target URL (where the link points to)
     *
     * [--anchor=<text>]
     * : Anchor text
     *
     * [--nofollow]
     * : Mark as nofollow link
     *
     * ## EXAMPLES
     *
     *     wp aiseo backlink add https://example.com https://mysite.com
     *     wp aiseo backlink add https://example.com https://mysite.com --anchor="My Site"
     *     wp aiseo backlink add https://example.com https://mysite.com --nofollow
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function add($args, $assoc_args) {
        $source_url = $args[0];
        $target_url = $args[1];
        $anchor_text = isset($assoc_args['anchor']) ? $assoc_args['anchor'] : '';
        $follow = !isset($assoc_args['nofollow']);
        
        WP_CLI::log(sprintf('Adding backlink from %s to %s...', $source_url, $target_url));
        
        $options = [];
        if ($anchor_text) {
            $options['anchor_text'] = $anchor_text;
        }
        $options['follow'] = $follow;
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->add_backlink($source_url, $target_url, $options);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Backlink added successfully!');
        WP_CLI::log('');
        WP_CLI::log(sprintf('ID: %s', $result['id']));
        WP_CLI::log(sprintf('Status: %s', $result['status']));
        WP_CLI::log(sprintf('Follow: %s', $result['follow'] ? 'Yes' : 'No'));
    }
    
    /**
     * Remove a backlink
     *
     * ## OPTIONS
     *
     * <backlink_id>
     * : Backlink ID
     *
     * ## EXAMPLES
     *
     *     wp aiseo backlink remove bl_abc123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function remove($args, $assoc_args) {
        $backlink_id = $args[0];
        
        WP_CLI::log(sprintf('Removing backlink %s...', $backlink_id));
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->remove_backlink($backlink_id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Backlink removed successfully!');
    }
    
    /**
     * Check backlink status
     *
     * ## OPTIONS
     *
     * <backlink_id>
     * : Backlink ID
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
     *     wp aiseo backlink check bl_abc123
     *     wp aiseo backlink check bl_abc123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function check($args, $assoc_args) {
        $backlink_id = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log(sprintf('Checking backlink %s...', $backlink_id));
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->check_backlink_status($backlink_id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Check complete!');
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Status Check Results:');
            WP_CLI::log(sprintf('  Status: %s', $result['status']));
            WP_CLI::log(sprintf('  HTTP Status: %d', $result['http_status']));
            WP_CLI::log(sprintf('  Link Exists: %s', $result['link_exists'] ? 'Yes' : 'No'));
            WP_CLI::log(sprintf('  Checked At: %s', $result['checked_at']));
        }
    }
    
    /**
     * Analyze backlink quality
     *
     * ## OPTIONS
     *
     * <backlink_id>
     * : Backlink ID
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
     *     wp aiseo backlink analyze bl_abc123
     *     wp aiseo backlink analyze bl_abc123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function analyze($args, $assoc_args) {
        $backlink_id = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log(sprintf('Analyzing backlink quality for %s...', $backlink_id));
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->analyze_backlink_quality($backlink_id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Analysis complete!');
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Quality Analysis:');
            WP_CLI::log(sprintf('  Quality Score: %d/100', $result['quality_score']));
            WP_CLI::log(sprintf('  Quality Level: %s', $result['quality_level']));
            
            if (!empty($result['factors'])) {
                WP_CLI::log('');
                WP_CLI::log('Key Factors:');
                foreach ($result['factors'] as $factor) {
                    WP_CLI::log('  • ' . $factor);
                }
            }
            
            if (!empty($result['recommendations'])) {
                WP_CLI::log('');
                WP_CLI::log('Recommendations:');
                foreach ($result['recommendations'] as $rec) {
                    WP_CLI::log('  • ' . $rec);
                }
            }
            
            if (!empty($result['risks'])) {
                WP_CLI::log('');
                WP_CLI::log('Risks:');
                foreach ($result['risks'] as $risk) {
                    WP_CLI::log('  ⚠ ' . $risk);
                }
            }
        }
    }
    
    /**
     * Get new backlinks
     *
     * ## OPTIONS
     *
     * [--days=<days>]
     * : Number of days to look back
     * ---
     * default: 7
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
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo backlink new
     *     wp aiseo backlink new --days=30
     *     wp aiseo backlink new --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function new($args, $assoc_args) {
        $days = isset($assoc_args['days']) ? absint($assoc_args['days']) : 7;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log(sprintf('Finding new backlinks from last %d days...', $days));
        
        $backlink = new AISEO_Backlink();
        $backlinks = $backlink->get_new_backlinks($days);
        
        if (empty($backlinks)) {
            WP_CLI::warning(sprintf('No new backlinks found in the last %d days', $days));
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($backlinks));
            return;
        }
        
        WP_CLI::success(sprintf('Found %d new backlinks!', count($backlinks)));
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($backlinks, JSON_PRETTY_PRINT));
        } else {
            $display_data = [];
            foreach ($backlinks as $bl) {
                $display_data[] = [
                    'id' => $bl['id'],
                    'source_url' => $bl['source_url'],
                    'discovered' => $bl['discovered_at'],
                    'status' => $bl['status']
                ];
            }
            
            WP_CLI\Utils\format_items($format, $display_data, 
                ['id', 'source_url', 'discovered', 'status']);
        }
    }
    
    /**
     * Get lost backlinks
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
     *     wp aiseo backlink lost
     *     wp aiseo backlink lost --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function lost($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::log('Finding lost backlinks...');
        
        $backlink = new AISEO_Backlink();
        $backlinks = $backlink->get_lost_backlinks();
        
        if (empty($backlinks)) {
            WP_CLI::success('No lost backlinks found!');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($backlinks));
            return;
        }
        
        WP_CLI::warning(sprintf('Found %d lost backlinks', count($backlinks)));
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($backlinks, JSON_PRETTY_PRINT));
        } else {
            $display_data = [];
            foreach ($backlinks as $bl) {
                $display_data[] = [
                    'id' => $bl['id'],
                    'source_url' => $bl['source_url'],
                    'target_url' => $bl['target_url'],
                    'last_checked' => $bl['last_checked']
                ];
            }
            
            WP_CLI\Utils\format_items($format, $display_data, 
                ['id', 'source_url', 'target_url', 'last_checked']);
        }
    }
    
    /**
     * Generate disavow file
     *
     * ## OPTIONS
     *
     * <backlink_ids>...
     * : Backlink IDs to disavow (space-separated)
     *
     * [--output=<file>]
     * : Output file path
     *
     * ## EXAMPLES
     *
     *     wp aiseo backlink disavow bl_abc123 bl_def456
     *     wp aiseo backlink disavow bl_abc123 bl_def456 --output=disavow.txt
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function disavow($args, $assoc_args) {
        $backlink_ids = $args;
        $output_file = isset($assoc_args['output']) ? $assoc_args['output'] : '';
        
        if (empty($backlink_ids)) {
            WP_CLI::error('Please provide at least one backlink ID');
        }
        
        WP_CLI::log(sprintf('Generating disavow file for %d backlinks...', count($backlink_ids)));
        
        $backlink = new AISEO_Backlink();
        $result = $backlink->generate_disavow_file($backlink_ids);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        if ($output_file) {
            file_put_contents($output_file, $result);
            WP_CLI::success(sprintf('Disavow file saved to: %s', $output_file));
        } else {
            WP_CLI::success('Disavow file generated!');
            WP_CLI::log('');
            WP_CLI::log($result);
        }
    }
    
    /**
     * Bulk check all backlinks
     *
     * ## EXAMPLES
     *
     *     wp aiseo backlink bulk-check
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk_check($args, $assoc_args) {
        WP_CLI::log('Starting bulk backlink check...');
        WP_CLI::log('This may take a while depending on the number of backlinks.');
        WP_CLI::log('');
        
        $backlink = new AISEO_Backlink();
        $results = $backlink->bulk_check_backlinks();
        
        WP_CLI::success('Bulk check complete!');
        WP_CLI::log('');
        WP_CLI::log('Results:');
        WP_CLI::log(sprintf('  Total Backlinks: %d', $results['total']));
        WP_CLI::log(sprintf('  Checked: %d', $results['checked']));
        WP_CLI::log(sprintf('  Active: %d', $results['active']));
        WP_CLI::log(sprintf('  Lost: %d', $results['lost']));
        WP_CLI::log(sprintf('  Broken: %d', $results['broken']));
        WP_CLI::log(sprintf('  Errors: %d', $results['errors']));
    }
    
    /**
     * Get backlink summary
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo backlink summary
     *     wp aiseo backlink summary --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function summary($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $backlink = new AISEO_Backlink();
        $summary = $backlink->get_summary();
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($summary, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Backlink Monitoring Summary:');
            WP_CLI::log('');
            WP_CLI::log(sprintf('Total Backlinks: %d', $summary['total_backlinks']));
            WP_CLI::log(sprintf('Active: %d', $summary['active']));
            WP_CLI::log(sprintf('Lost: %d', $summary['lost']));
            WP_CLI::log(sprintf('Broken: %d', $summary['broken']));
            WP_CLI::log(sprintf('New (Last 7 Days): %d', $summary['new_last_7_days']));
            WP_CLI::log(sprintf('Average Quality Score: %.1f', $summary['average_quality_score']));
            WP_CLI::log(sprintf('Follow Links: %d', $summary['follow_links']));
            WP_CLI::log(sprintf('Nofollow Links: %d', $summary['nofollow_links']));
        }
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo backlink list', ['AISEO_Backlink_CLI', 'list']);
WP_CLI::add_command('aiseo backlink add', ['AISEO_Backlink_CLI', 'add']);
WP_CLI::add_command('aiseo backlink remove', ['AISEO_Backlink_CLI', 'remove']);
WP_CLI::add_command('aiseo backlink check', ['AISEO_Backlink_CLI', 'check']);
WP_CLI::add_command('aiseo backlink analyze', ['AISEO_Backlink_CLI', 'analyze']);
WP_CLI::add_command('aiseo backlink new', ['AISEO_Backlink_CLI', 'new']);
WP_CLI::add_command('aiseo backlink lost', ['AISEO_Backlink_CLI', 'lost']);
WP_CLI::add_command('aiseo backlink disavow', ['AISEO_Backlink_CLI', 'disavow']);
WP_CLI::add_command('aiseo backlink bulk-check', ['AISEO_Backlink_CLI', 'bulk_check']);
WP_CLI::add_command('aiseo backlink summary', ['AISEO_Backlink_CLI', 'summary']);
