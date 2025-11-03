<?php
/**
 * AISEO Unified Report WP-CLI Commands
 * 
 * @package AISEO
 * @since 1.16.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WP-CLI commands for unified SEO reporting
 */
class AISEO_Unified_Report_CLI {
    
    /**
     * Generate unified SEO report for a post
     * 
     * ## OPTIONS
     * 
     * <post_id>
     * : The post ID to generate report for
     * 
     * [--force-refresh]
     * : Force refresh (bypass cache)
     * 
     * [--format=<format>]
     * : Output format (json, table, yaml, csv)
     * ---
     * default: table
     * options:
     *   - json
     *   - table
     *   - yaml
     *   - csv
     * ---
     * 
     * ## EXAMPLES
     * 
     *     # Generate report for post ID 1
     *     wp aiseo report unified 1
     * 
     *     # Force refresh
     *     wp aiseo report unified 1 --force-refresh
     * 
     *     # Get JSON output
     *     wp aiseo report unified 1 --format=json
     * 
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function unified($args, $assoc_args) {
        list($post_id) = $args;
        $post_id = absint($post_id);
        
        if (!class_exists('AISEO_Unified_Report')) {
            WP_CLI::error('Unified Report class not found');
            return;
        }
        
        $options = [];
        if (isset($assoc_args['force-refresh'])) {
            $options['force_refresh'] = true;
            WP_CLI::log('Force refresh enabled - bypassing cache...');
        }
        
        WP_CLI::log("Generating unified SEO report for post ID {$post_id}...");
        
        $report = AISEO_Unified_Report::generate_report($post_id, $options);
        
        if (isset($report['error'])) {
            WP_CLI::error($report['error']);
            return;
        }
        
        $format = $assoc_args['format'] ?? 'table';
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($report, JSON_PRETTY_PRINT));
            return;
        }
        
        // Display summary
        WP_CLI::success("Report generated successfully!");
        WP_CLI::log('');
        WP_CLI::log("Post: {$report['post_title']} (ID: {$report['post_id']})");
        WP_CLI::log("Overall Score: {$report['overall_score']}/100");
        WP_CLI::log("Status: " . strtoupper($report['status']));
        WP_CLI::log("Generated: {$report['generated_at']}");
        WP_CLI::log('');
        
        // Display section scores
        WP_CLI::log('Section Scores:');
        $section_data = [];
        foreach ($report['sections'] as $key => $section) {
            $section_data[] = [
                'Section' => $section['name'],
                'Score' => $section['score'] . '/100',
                'Weight' => $section['weight'] . '%',
                'Weighted' => round(($section['score'] * $section['weight']) / 100, 1)
            ];
        }
        
        WP_CLI\Utils\format_items('table', $section_data, ['Section', 'Score', 'Weight', 'Weighted']);
        
        // Display recommendations
        if (!empty($report['recommendations'])) {
            WP_CLI::log('');
            WP_CLI::log('Top Recommendations:');
            $rec_data = [];
            foreach (array_slice($report['recommendations'], 0, 5) as $rec) {
                $rec_data[] = [
                    'Priority' => strtoupper($rec['priority']),
                    'Section' => $rec['section'],
                    'Action' => $rec['action']
                ];
            }
            WP_CLI\Utils\format_items('table', $rec_data, ['Priority', 'Section', 'Action']);
        }
        
        // Display summary
        if (!empty($report['summary'])) {
            WP_CLI::log('');
            WP_CLI::log('Summary:');
            if (!empty($report['summary']['strengths'])) {
                WP_CLI::log('  Strengths: ' . implode(', ', $report['summary']['strengths']));
            }
            if (!empty($report['summary']['weaknesses'])) {
                WP_CLI::log('  Weaknesses: ' . implode(', ', $report['summary']['weaknesses']));
            }
            if (!empty($report['summary']['quick_wins'])) {
                WP_CLI::log('  Quick Wins: ' . implode(', ', $report['summary']['quick_wins']));
            }
        }
    }
    
    /**
     * Get historical reports for a post
     * 
     * ## OPTIONS
     * 
     * <post_id>
     * : The post ID to get history for
     * 
     * [--limit=<limit>]
     * : Number of historical reports to retrieve
     * ---
     * default: 10
     * ---
     * 
     * [--format=<format>]
     * : Output format (json, table, yaml, csv)
     * ---
     * default: table
     * options:
     *   - json
     *   - table
     *   - yaml
     *   - csv
     * ---
     * 
     * ## EXAMPLES
     * 
     *     # Get last 10 reports
     *     wp aiseo report history 1
     * 
     *     # Get last 5 reports
     *     wp aiseo report history 1 --limit=5
     * 
     *     # Get JSON output
     *     wp aiseo report history 1 --format=json
     * 
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function history($args, $assoc_args) {
        list($post_id) = $args;
        $post_id = absint($post_id);
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 10;
        
        if (!class_exists('AISEO_Unified_Report')) {
            WP_CLI::error('Unified Report class not found');
            return;
        }
        
        WP_CLI::log("Retrieving historical reports for post ID {$post_id}...");
        
        $history = AISEO_Unified_Report::get_history($post_id, $limit);
        
        if (empty($history)) {
            WP_CLI::warning('No historical reports found for this post');
            return;
        }
        
        $format = $assoc_args['format'] ?? 'table';
        
        if ($format === 'json') {
            WP_CLI::log(json_encode($history, JSON_PRETTY_PRINT));
            return;
        }
        
        WP_CLI::success("Found " . count($history) . " historical reports");
        WP_CLI::log('');
        
        $history_data = [];
        foreach ($history as $index => $report) {
            $history_data[] = [
                '#' => $index + 1,
                'Date' => $report['generated_at'] ?? 'N/A',
                'Score' => ($report['overall_score'] ?? 0) . '/100',
                'Status' => strtoupper($report['status'] ?? 'unknown'),
                'Sections' => count($report['sections'] ?? [])
            ];
        }
        
        WP_CLI\Utils\format_items('table', $history_data, ['#', 'Date', 'Score', 'Status', 'Sections']);
    }
    
    /**
     * Clear unified report cache for a post
     * 
     * ## OPTIONS
     * 
     * <post_id>
     * : The post ID to clear cache for
     * 
     * ## EXAMPLES
     * 
     *     # Clear cache for post ID 1
     *     wp aiseo report clear-cache 1
     * 
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function clear_cache($args, $assoc_args) {
        list($post_id) = $args;
        $post_id = absint($post_id);
        
        if (!class_exists('AISEO_Unified_Report')) {
            WP_CLI::error('Unified Report class not found');
            return;
        }
        
        AISEO_Unified_Report::clear_cache($post_id);
        
        WP_CLI::success("Cache cleared for post ID {$post_id}");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo report', 'AISEO_Unified_Report_CLI');
}
