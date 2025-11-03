<?php
/**
 * AISEO Competitor Analysis WP-CLI Commands
 *
 * @package AISEO
 * @since 1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Competitor Analysis WP-CLI Commands
 */
class AISEO_Competitor_CLI {
    
    /**
     * List all competitors
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
     *     wp aiseo competitor list
     *     wp aiseo competitor list --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function list($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $competitor = new AISEO_Competitor();
        $competitors = $competitor->get_competitors();
        
        if (empty($competitors)) {
            WP_CLI::warning('No competitors found');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($competitors));
            return;
        }
        
        WP_CLI::log(sprintf('Found %d competitor(s)', count($competitors)));
        WP_CLI::log('');
        
        WP_CLI\Utils\format_items($format, $competitors, ['id', 'name', 'domain', 'status', 'last_analyzed']);
    }
    
    /**
     * Add a competitor
     *
     * ## OPTIONS
     *
     * <url>
     * : Competitor URL
     *
     * [--name=<name>]
     * : Competitor name
     *
     * ## EXAMPLES
     *
     *     wp aiseo competitor add https://example.com
     *     wp aiseo competitor add https://example.com --name="Example Site"
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function add($args, $assoc_args) {
        $url = $args[0];
        $name = isset($assoc_args['name']) ? $assoc_args['name'] : '';
        
        if (empty($url)) {
            WP_CLI::error('URL is required');
        }
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->add_competitor($url, $name);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success(sprintf('Competitor added with ID: %s', $result));
    }
    
    /**
     * Remove a competitor
     *
     * ## OPTIONS
     *
     * <id>
     * : Competitor ID
     *
     * ## EXAMPLES
     *
     *     wp aiseo competitor remove comp_abc123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function remove($args, $assoc_args) {
        $id = $args[0];
        
        if (empty($id)) {
            WP_CLI::error('Competitor ID is required');
        }
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->remove_competitor($id);
        
        if ($result) {
            WP_CLI::success('Competitor removed successfully');
        } else {
            WP_CLI::error('Competitor not found');
        }
    }
    
    /**
     * Analyze a competitor
     *
     * ## OPTIONS
     *
     * <id>
     * : Competitor ID
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
     *     wp aiseo competitor analyze comp_abc123
     *     wp aiseo competitor analyze comp_abc123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function analyze($args, $assoc_args) {
        $id = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (empty($id)) {
            WP_CLI::error('Competitor ID is required');
        }
        
        WP_CLI::log('Analyzing competitor...');
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->analyze_competitor($id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        WP_CLI::success('Analysis complete!');
        WP_CLI::log('');
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            // Display key metrics
            WP_CLI::log('SEO Metrics:');
            WP_CLI::log(sprintf('  Title: %s (%d chars)', $result['title'], strlen($result['title'])));
            WP_CLI::log(sprintf('  Meta Description: %s (%d chars)', 
                substr($result['meta_description'], 0, 50) . '...', 
                strlen($result['meta_description'])));
            WP_CLI::log(sprintf('  H1 Tags: %d', count($result['h1_tags'])));
            WP_CLI::log(sprintf('  H2 Tags: %d', count($result['h2_tags'])));
            WP_CLI::log(sprintf('  Word Count: %d', $result['word_count']));
            WP_CLI::log(sprintf('  Images: %d', $result['image_count']));
            WP_CLI::log(sprintf('  Links: %d', $result['link_count']));
            WP_CLI::log(sprintf('  Schema Types: %s', implode(', ', $result['schema_types']) ?: 'None'));
        }
    }
    
    /**
     * Get competitor analysis
     *
     * ## OPTIONS
     *
     * <id>
     * : Competitor ID
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
     *     wp aiseo competitor get comp_abc123
     *     wp aiseo competitor get comp_abc123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function get($args, $assoc_args) {
        $id = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (empty($id)) {
            WP_CLI::error('Competitor ID is required');
        }
        
        $competitor = new AISEO_Competitor();
        $analysis = $competitor->get_analysis($id);
        
        if (!$analysis) {
            WP_CLI::error('No analysis data found. Run analyze first.');
        }
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($analysis, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Competitor Analysis:');
            WP_CLI::log('');
            WP_CLI::log(sprintf('Title: %s', $analysis['title']));
            WP_CLI::log(sprintf('Meta Description: %s', $analysis['meta_description']));
            WP_CLI::log(sprintf('Word Count: %d', $analysis['word_count']));
            WP_CLI::log(sprintf('Images: %d', $analysis['image_count']));
            WP_CLI::log(sprintf('Links: %d', $analysis['link_count']));
            WP_CLI::log(sprintf('Analyzed: %s', $analysis['analyzed_at']));
        }
    }
    
    /**
     * Compare competitor with own site
     *
     * ## OPTIONS
     *
     * <id>
     * : Competitor ID
     *
     * [--post-id=<post-id>]
     * : Post ID to compare (defaults to homepage)
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
     *     wp aiseo competitor compare comp_abc123
     *     wp aiseo competitor compare comp_abc123 --post-id=123
     *     wp aiseo competitor compare comp_abc123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function compare($args, $assoc_args) {
        $id = $args[0];
        $post_id = isset($assoc_args['post-id']) ? absint($assoc_args['post-id']) : null;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (empty($id)) {
            WP_CLI::error('Competitor ID is required');
        }
        
        $competitor = new AISEO_Competitor();
        $result = $competitor->compare_with_site($id, $post_id);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Comparison Results:');
            WP_CLI::log('');
            
            // Display differences
            WP_CLI::log('Metric Comparison:');
            foreach ($result['differences'] as $metric => $data) {
                $status = $data['diff'] >= 0 ? '✓' : '✗';
                WP_CLI::log(sprintf('  %s %s: Competitor=%d, You=%d (Diff: %+d)',
                    $status,
                    ucwords(str_replace('_', ' ', $metric)),
                    $data['competitor'],
                    $data['own'],
                    $data['diff']
                ));
            }
            
            // Display recommendations
            if (!empty($result['recommendations'])) {
                WP_CLI::log('');
                WP_CLI::log('Recommendations:');
                foreach ($result['recommendations'] as $recommendation) {
                    WP_CLI::log('  • ' . $recommendation);
                }
            }
        }
    }
    
    /**
     * Get competitor summary
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
     *     wp aiseo competitor summary
     *     wp aiseo competitor summary --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function summary($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $competitor = new AISEO_Competitor();
        $summary = $competitor->get_summary();
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($summary, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::log('Competitor Summary:');
            WP_CLI::log('');
            WP_CLI::log(sprintf('Total Competitors: %d', $summary['total_competitors']));
            WP_CLI::log(sprintf('Analyzed: %d', $summary['analyzed']));
            WP_CLI::log(sprintf('Pending Analysis: %d', $summary['pending']));
            WP_CLI::log(sprintf('Active: %d', $summary['active']));
            
            if (!empty($summary['competitors'])) {
                WP_CLI::log('');
                WP_CLI::log('Competitors:');
                WP_CLI\Utils\format_items('table', $summary['competitors'], 
                    ['id', 'name', 'domain', 'last_analyzed', 'has_analysis']);
            }
        }
    }
    
    /**
     * Bulk analyze all competitors
     *
     * ## OPTIONS
     *
     * [--force]
     * : Force re-analysis even if already analyzed
     *
     * ## EXAMPLES
     *
     *     wp aiseo competitor bulk-analyze
     *     wp aiseo competitor bulk-analyze --force
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk_analyze($args, $assoc_args) {
        $force = isset($assoc_args['force']);
        
        $competitor_obj = new AISEO_Competitor();
        $competitors = $competitor_obj->get_competitors();
        
        if (empty($competitors)) {
            WP_CLI::warning('No competitors found');
            return;
        }
        
        $total = count($competitors);
        $analyzed = 0;
        $skipped = 0;
        $failed = 0;
        
        WP_CLI::log(sprintf('Analyzing %d competitor(s)...', $total));
        
        $progress = \WP_CLI\Utils\make_progress_bar('Analyzing', $total);
        
        foreach ($competitors as $comp) {
            // Skip if already analyzed and not forcing
            if (!$force && $comp['last_analyzed']) {
                $skipped++;
                $progress->tick();
                continue;
            }
            
            $result = $competitor_obj->analyze_competitor($comp['id']);
            
            if (is_wp_error($result)) {
                $failed++;
            } else {
                $analyzed++;
            }
            
            $progress->tick();
            
            // Small delay to avoid overwhelming servers
            sleep(1);
        }
        
        $progress->finish();
        
        WP_CLI::log('');
        WP_CLI::success(sprintf(
            'Bulk analysis complete. Analyzed: %d, Skipped: %d, Failed: %d',
            $analyzed,
            $skipped,
            $failed
        ));
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo competitor list', ['AISEO_Competitor_CLI', 'list']);
WP_CLI::add_command('aiseo competitor add', ['AISEO_Competitor_CLI', 'add']);
WP_CLI::add_command('aiseo competitor remove', ['AISEO_Competitor_CLI', 'remove']);
WP_CLI::add_command('aiseo competitor analyze', ['AISEO_Competitor_CLI', 'analyze']);
WP_CLI::add_command('aiseo competitor get', ['AISEO_Competitor_CLI', 'get']);
WP_CLI::add_command('aiseo competitor compare', ['AISEO_Competitor_CLI', 'compare']);
WP_CLI::add_command('aiseo competitor summary', ['AISEO_Competitor_CLI', 'summary']);
WP_CLI::add_command('aiseo competitor bulk-analyze', ['AISEO_Competitor_CLI', 'bulk_analyze']);
