<?php
/**
 * AISEO Image SEO WP-CLI Commands
 *
 * @package AISEO
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

/**
 * Image SEO WP-CLI Commands
 */
class AISEO_Image_CLI {
    
    /**
     * Generate alt text for an image
     *
     * ## OPTIONS
     *
     * <image-id>
     * : The attachment ID
     *
     * [--overwrite]
     * : Overwrite existing alt text
     *
     * ## EXAMPLES
     *
     *     wp aiseo image generate-alt 123
     *     wp aiseo image generate-alt 123 --overwrite
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function generate_alt($args, $assoc_args) {
        $image_id = absint($args[0]);
        $overwrite = isset($assoc_args['overwrite']);
        
        if (!wp_attachment_is_image($image_id)) {
            WP_CLI::error('Invalid image ID or not an image attachment.');
        }
        
        $image_seo = new AISEO_Image_SEO();
        
        WP_CLI::log("Generating alt text for image ID: {$image_id}...");
        
        $alt_text = $image_seo->generate_alt_text($image_id, ['overwrite' => $overwrite]);
        
        if (is_wp_error($alt_text)) {
            WP_CLI::error($alt_text->get_error_message());
        }
        
        WP_CLI::success("Generated alt text: {$alt_text}");
    }
    
    /**
     * Bulk generate alt text for images
     *
     * ## OPTIONS
     *
     * [--all]
     * : Process all images
     *
     * [--missing-only]
     * : Only process images without alt text
     *
     * [--limit=<number>]
     * : Limit number of images to process
     * ---
     * default: 100
     * ---
     *
     * [--overwrite]
     * : Overwrite existing alt text
     *
     * [--dry-run]
     * : Preview without making changes
     *
     * ## EXAMPLES
     *
     *     wp aiseo image bulk-generate --all
     *     wp aiseo image bulk-generate --missing-only --limit=50
     *     wp aiseo image bulk-generate --dry-run
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function bulk_generate($args, $assoc_args) {
        $image_seo = new AISEO_Image_SEO();
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 100;
        $overwrite = isset($assoc_args['overwrite']);
        $dry_run = isset($assoc_args['dry-run']);
        
        // Get images
        if (isset($assoc_args['missing-only']) || !isset($assoc_args['all'])) {
            WP_CLI::log('Finding images without alt text...');
            $images = $image_seo->detect_missing_alt_text(['posts_per_page' => $limit]);
            $image_ids = wp_list_pluck($images, 'ID');
        } else {
            WP_CLI::log('Getting all images...');
            $image_ids = get_posts([
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'posts_per_page' => $limit,
                'fields' => 'ids',
                'post_status' => 'inherit'
            ]);
        }
        
        if (empty($image_ids)) {
            WP_CLI::warning('No images found to process.');
            return;
        }
        
        $total = count($image_ids);
        WP_CLI::log(sprintf('Processing %d images...', $total));
        
        if ($dry_run) {
            WP_CLI::log('DRY RUN MODE - No changes will be made');
        }
        
        $progress = \WP_CLI\Utils\make_progress_bar('Generating alt text', $total);
        
        $success = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($image_ids as $image_id) {
            try {
                if (!$dry_run) {
                    $alt_text = $image_seo->generate_alt_text($image_id, ['overwrite' => $overwrite]);
                    
                    if (is_wp_error($alt_text)) {
                        if ($alt_text->get_error_code() === 'alt_exists') {
                            $skipped++;
                        } else {
                            throw new Exception($alt_text->get_error_message());
                        }
                    } else {
                        $success++;
                    }
                    
                    // Rate limiting
                    sleep(2);
                } else {
                    $success++;
                }
                
            } catch (Exception $e) {
                $errors++;
                WP_CLI::debug("Error processing image {$image_id}: " . $e->getMessage());
            }
            
            $progress->tick();
        }
        
        $progress->finish();
        
        WP_CLI::success(sprintf(
            'Completed: %d successful, %d skipped, %d errors',
            $success,
            $skipped,
            $errors
        ));
    }
    
    /**
     * Detect images missing alt text
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
     * [--limit=<number>]
     * : Limit number of results
     * ---
     * default: 100
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo image detect-missing
     *     wp aiseo image detect-missing --format=json
     *     wp aiseo image detect-missing --format=count
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function detect_missing($args, $assoc_args) {
        $image_seo = new AISEO_Image_SEO();
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 100;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $images = $image_seo->detect_missing_alt_text(['posts_per_page' => $limit]);
        
        if (empty($images)) {
            WP_CLI::success('All images have alt text!');
            return;
        }
        
        if ($format === 'count') {
            WP_CLI::log(count($images));
            return;
        }
        
        $output = [];
        foreach ($images as $image) {
            $output[] = [
                'ID' => $image->ID,
                'Filename' => basename($image->guid),
                'Parent' => $image->parent_title ?? 'None',
                'Size' => $image->filesize,
                'Date' => $image->post_date
            ];
        }
        
        WP_CLI\Utils\format_items($format, $output, ['ID', 'Filename', 'Parent', 'Size', 'Date']);
    }
    
    /**
     * Analyze image SEO score
     *
     * ## OPTIONS
     *
     * <post-id>
     * : The post ID to analyze images for
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
     *     wp aiseo image analyze 123
     *     wp aiseo image analyze 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function analyze($args, $assoc_args) {
        $post_id = absint($args[0]);
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        $image_seo = new AISEO_Image_SEO();
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error('Post not found.');
        }
        
        // Get all images in post
        $content = $post->post_content;
        preg_match_all('/<img[^>]+>/i', $content, $matches);
        
        if (empty($matches[0])) {
            WP_CLI::warning('No images found in post.');
            return;
        }
        
        WP_CLI::log(sprintf('Analyzing %d images in post...', count($matches[0])));
        
        $results = [];
        $total_score = 0;
        $image_count = 0;
        
        foreach ($matches[0] as $img_tag) {
            // Extract image ID
            if (preg_match('/wp-image-(\d+)/i', $img_tag, $id_match)) {
                $image_id = absint($id_match[1]);
                $score_data = $image_seo->analyze_image_seo($image_id);
                
                $results[] = [
                    'Image_ID' => $image_id,
                    'Score' => $score_data['percentage'],
                    'Status' => $score_data['status'],
                    'Alt_Text' => !empty($score_data['checks']['alt_text_present']['status']) ? 'Yes' : 'No'
                ];
                
                $total_score += $score_data['percentage'];
                $image_count++;
            }
        }
        
        if ($image_count > 0) {
            $average_score = round($total_score / $image_count);
            
            if ($format === 'json') {
                WP_CLI::log(json_encode([
                    'average_score' => $average_score,
                    'total_images' => $image_count,
                    'images' => $results
                ], JSON_PRETTY_PRINT));
            } else {
                WP_CLI\Utils\format_items('table', $results, ['Image_ID', 'Score', 'Status', 'Alt_Text']);
                WP_CLI::success(sprintf('Average image SEO score: %d/100', $average_score));
            }
        } else {
            WP_CLI::warning('No valid images found to analyze.');
        }
    }
}

// Register WP-CLI commands
WP_CLI::add_command('aiseo image', 'AISEO_Image_CLI');
