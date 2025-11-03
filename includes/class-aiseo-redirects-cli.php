<?php
/**
 * AISEO 404 Monitor & Redirection Manager WP-CLI Commands
 *
 * @package AISEO
 * @since 1.9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 404 Monitor & Redirection Manager CLI Commands
 */
class AISEO_Redirects_CLI {
    
    /**
     * Get 404 errors
     *
     * ## OPTIONS
     *
     * [--limit=<limit>]
     * : Number of errors to retrieve
     * ---
     * default: 100
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo 404 errors
     *     wp aiseo 404 errors --limit=50
     *     wp aiseo 404 errors --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function errors($args, $assoc_args) {
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 100;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->get_404_errors(['limit' => $limit]);
        
        if (empty($result['errors'])) {
            WP_CLI::success('No 404 errors found');
            return;
        }
        
        WP_CLI::success("Found {$result['total']} 404 errors");
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            WP_CLI::line('URL,Hits,Last Hit,Referrer');
            foreach ($result['errors'] as $error) {
                WP_CLI::line(sprintf(
                    '"%s",%d,"%s","%s"',
                    $error['url'],
                    $error['hits'],
                    $error['last_hit'],
                    $error['referrer'] ?? ''
                ));
            }
        } else {
            $table_data = [];
            foreach ($result['errors'] as $error) {
                $table_data[] = [
                    'URL' => $error['url'],
                    'Hits' => $error['hits'],
                    'Last Hit' => $error['last_hit'],
                    'Referrer' => wp_trim_words($error['referrer'] ?? 'N/A', 5)
                ];
            }
            WP_CLI\Utils\format_items('table', $table_data, ['URL', 'Hits', 'Last Hit', 'Referrer']);
        }
    }
    
    /**
     * Suggest redirect for a 404 URL
     *
     * ## OPTIONS
     *
     * <url>
     * : The 404 URL
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
     *     wp aiseo 404 suggest "/old-page"
     *     wp aiseo 404 suggest "/missing-post" --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function suggest($args, $assoc_args) {
        $url = $args[0];
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::line("Analyzing URL: {$url}");
        WP_CLI::line('Generating AI-powered redirect suggestions...');
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->suggest_redirect($url);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success('Redirect suggestion generated');
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line('');
            WP_CLI::line("Suggested URL: {$result['suggested_url']}");
            WP_CLI::line("Confidence: {$result['confidence']}");
            WP_CLI::line("Reason: {$result['reason']}");
            
            if (!empty($result['alternatives'])) {
                WP_CLI::line('');
                WP_CLI::line('Alternative URLs:');
                foreach ($result['alternatives'] as $alt) {
                    WP_CLI::line("  - {$alt}");
                }
            }
        }
    }
    
    /**
     * Create a redirect
     *
     * ## OPTIONS
     *
     * <source>
     * : Source URL
     *
     * <target>
     * : Target URL
     *
     * [--type=<type>]
     * : Redirect type
     * ---
     * default: 301
     * options:
     *   - 301
     *   - 302
     *   - 307
     * ---
     *
     * [--regex]
     * : Use regex matching
     *
     * ## EXAMPLES
     *
     *     wp aiseo redirects create "/old-page" "/new-page"
     *     wp aiseo redirects create "/old-page" "/new-page" --type=302
     *     wp aiseo redirects create "^/blog/(.*)$" "/news/$1" --regex
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function create($args, $assoc_args) {
        $source = $args[0];
        $target = $args[1];
        $type = isset($assoc_args['type']) ? $assoc_args['type'] : '301';
        $is_regex = isset($assoc_args['regex']);
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->create_redirect($source, $target, $type, [
            'is_regex' => $is_regex
        ]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success("Redirect created successfully (ID: {$result})");
        WP_CLI::line("Source: {$source}");
        WP_CLI::line("Target: {$target}");
        WP_CLI::line("Type: {$type}");
        WP_CLI::line("Regex: " . ($is_regex ? 'Yes' : 'No'));
    }
    
    /**
     * List all redirects
     *
     * ## OPTIONS
     *
     * [--limit=<limit>]
     * : Number of redirects to retrieve
     * ---
     * default: 100
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo redirects list
     *     wp aiseo redirects list --limit=50
     *     wp aiseo redirects list --format=csv
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function list($args, $assoc_args) {
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 100;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->get_redirects(['limit' => $limit]);
        
        if (empty($result['redirects'])) {
            WP_CLI::success('No redirects found');
            return;
        }
        
        WP_CLI::success("Found {$result['total']} redirects");
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            WP_CLI::line('ID,Source,Target,Type,Hits,Regex');
            foreach ($result['redirects'] as $redirect) {
                WP_CLI::line(sprintf(
                    '%d,"%s","%s",%s,%d,%s',
                    $redirect['id'],
                    $redirect['source_url'],
                    $redirect['target_url'],
                    $redirect['redirect_type'],
                    $redirect['hits'],
                    $redirect['is_regex'] ? 'Yes' : 'No'
                ));
            }
        } else {
            $table_data = [];
            foreach ($result['redirects'] as $redirect) {
                $table_data[] = [
                    'ID' => $redirect['id'],
                    'Source' => $redirect['source_url'],
                    'Target' => wp_trim_words($redirect['target_url'], 5),
                    'Type' => $redirect['redirect_type'],
                    'Hits' => $redirect['hits'],
                    'Regex' => $redirect['is_regex'] ? 'Yes' : 'No'
                ];
            }
            WP_CLI\Utils\format_items('table', $table_data, ['ID', 'Source', 'Target', 'Type', 'Hits', 'Regex']);
        }
    }
    
    /**
     * Delete a redirect
     *
     * ## OPTIONS
     *
     * <id>
     * : Redirect ID
     *
     * ## EXAMPLES
     *
     *     wp aiseo redirects delete 123
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function delete($args, $assoc_args) {
        $redirect_id = absint($args[0]);
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->delete_redirect($redirect_id);
        
        if (!$result) {
            WP_CLI::error('Failed to delete redirect');
            return;
        }
        
        WP_CLI::success("Redirect {$redirect_id} deleted successfully");
    }
    
    /**
     * Import redirects from CSV file
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to CSV file
     *
     * ## EXAMPLES
     *
     *     wp aiseo redirects import redirects.csv
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function import($args, $assoc_args) {
        $file = $args[0];
        
        if (!file_exists($file)) {
            WP_CLI::error("File not found: {$file}");
            return;
        }
        
        $csv_data = file_get_contents($file);
        
        WP_CLI::line('Importing redirects...');
        
        $redirects = new AISEO_Redirects();
        $result = $redirects->bulk_import_redirects($csv_data);
        
        WP_CLI::success("Import complete!");
        WP_CLI::line("Imported: {$result['imported']}");
        WP_CLI::line("Total lines: {$result['total_lines']}");
        
        if (!empty($result['errors'])) {
            WP_CLI::warning("Errors encountered:");
            foreach ($result['errors'] as $error) {
                WP_CLI::line("  - {$error}");
            }
        }
    }
    
    /**
     * Export redirects to CSV file
     *
     * ## OPTIONS
     *
     * [--file=<file>]
     * : Output file path
     * ---
     * default: redirects-export.csv
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo redirects export
     *     wp aiseo redirects export --file=my-redirects.csv
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function export($args, $assoc_args) {
        $file = isset($assoc_args['file']) ? $assoc_args['file'] : 'redirects-export.csv';
        
        $redirects = new AISEO_Redirects();
        $csv_data = $redirects->export_redirects();
        
        file_put_contents($file, $csv_data);
        
        WP_CLI::success("Redirects exported to: {$file}");
    }
    
    /**
     * Get redirect statistics
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
     *     wp aiseo redirects stats
     *     wp aiseo redirects stats --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function stats($args, $assoc_args) {
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $redirects = new AISEO_Redirects();
        $stats = $redirects->get_statistics();
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($stats, JSON_PRETTY_PRINT));
        } else {
            WP_CLI::line('=== Redirect Statistics ===');
            WP_CLI::line('');
            WP_CLI::line("Total Redirects: {$stats['total_redirects']}");
            WP_CLI::line("Total 404 Errors: {$stats['total_404_errors']}");
            WP_CLI::line("Total Redirect Hits: {$stats['total_redirect_hits']}");
            WP_CLI::line('');
            
            if (!empty($stats['top_404s'])) {
                WP_CLI::line('Top 10 404 Errors:');
                $table_data = [];
                foreach ($stats['top_404s'] as $error) {
                    $table_data[] = [
                        'URL' => $error['url'],
                        'Hits' => $error['hits']
                    ];
                }
                WP_CLI\Utils\format_items('table', $table_data, ['URL', 'Hits']);
            }
            
            WP_CLI::line('');
            
            if (!empty($stats['top_redirects'])) {
                WP_CLI::line('Top 10 Redirects:');
                $table_data = [];
                foreach ($stats['top_redirects'] as $redirect) {
                    $table_data[] = [
                        'Source' => $redirect['source_url'],
                        'Target' => wp_trim_words($redirect['target_url'], 5),
                        'Hits' => $redirect['hits']
                    ];
                }
                WP_CLI\Utils\format_items('table', $table_data, ['Source', 'Target', 'Hits']);
            }
        }
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo 404 errors', ['AISEO_Redirects_CLI', 'errors']);
    WP_CLI::add_command('aiseo 404 suggest', ['AISEO_Redirects_CLI', 'suggest']);
    WP_CLI::add_command('aiseo redirects create', ['AISEO_Redirects_CLI', 'create']);
    WP_CLI::add_command('aiseo redirects list', ['AISEO_Redirects_CLI', 'list']);
    WP_CLI::add_command('aiseo redirects delete', ['AISEO_Redirects_CLI', 'delete']);
    WP_CLI::add_command('aiseo redirects import', ['AISEO_Redirects_CLI', 'import']);
    WP_CLI::add_command('aiseo redirects export', ['AISEO_Redirects_CLI', 'export']);
    WP_CLI::add_command('aiseo redirects stats', ['AISEO_Redirects_CLI', 'stats']);
}
