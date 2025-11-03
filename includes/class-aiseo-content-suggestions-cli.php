<?php
/**
 * AISEO Content Suggestions WP-CLI Commands
 *
 * @package AISEO
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Suggestions CLI Commands
 */
class AISEO_Content_Suggestions_CLI {
    
    /**
     * Get AI-powered topic suggestions
     *
     * ## OPTIONS
     *
     * [--niche=<niche>]
     * : The niche or industry
     *
     * [--keywords=<keywords>]
     * : Comma-separated keywords
     *
     * [--count=<count>]
     * : Number of topics to generate
     * ---
     * default: 10
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
     *     wp aiseo content topics --niche="WordPress SEO"
     *     wp aiseo content topics --keywords="seo,wordpress,optimization" --count=15
     *     wp aiseo content topics --niche="Digital Marketing" --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function topics($args, $assoc_args) {
        $niche = isset($assoc_args['niche']) ? $assoc_args['niche'] : '';
        $keywords = isset($assoc_args['keywords']) ? explode(',', $assoc_args['keywords']) : [];
        $count = isset($assoc_args['count']) ? absint($assoc_args['count']) : 10;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        if (empty($niche) && empty($keywords)) {
            WP_CLI::error('Please provide either --niche or --keywords parameter');
            return;
        }
        
        WP_CLI::line('Generating topic suggestions...');
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->get_topic_suggestions([
            'niche' => $niche,
            'keywords' => $keywords,
            'count' => $count
        ]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success("Generated {$result['total']} topic suggestions");
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            foreach ($result['topics'] as $topic) {
                WP_CLI::line(implode(',', [
                    $topic['title'],
                    $topic['keyword'] ?? '',
                    $topic['intent'] ?? '',
                    $topic['difficulty'] ?? ''
                ]));
            }
        } else {
            $table_data = [];
            foreach ($result['topics'] as $topic) {
                $table_data[] = [
                    'Title' => $topic['title'],
                    'Keyword' => $topic['keyword'] ?? 'N/A',
                    'Intent' => $topic['intent'] ?? 'N/A',
                    'Difficulty' => $topic['difficulty'] ?? 'N/A'
                ];
            }
            WP_CLI\Utils\format_items('table', $table_data, ['Title', 'Keyword', 'Intent', 'Difficulty']);
        }
    }
    
    /**
     * Get optimization tips for a post
     *
     * ## OPTIONS
     *
     * <post-id>
     * : The post ID
     *
     * [--focus-keyword=<keyword>]
     * : Focus keyword for analysis
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
     *     wp aiseo content optimize 123
     *     wp aiseo content optimize 123 --focus-keyword="wordpress seo"
     *     wp aiseo content optimize 123 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function optimize($args, $assoc_args) {
        $post_id = absint($args[0]);
        $focus_keyword = isset($assoc_args['focus-keyword']) ? $assoc_args['focus-keyword'] : '';
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        $post = get_post($post_id);
        if (!$post) {
            WP_CLI::error("Post {$post_id} not found");
            return;
        }
        
        WP_CLI::line("Analyzing post: {$post->post_title}");
        WP_CLI::line('Generating optimization tips...');
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->get_optimization_tips($post_id, [
            'focus_keyword' => $focus_keyword
        ]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success("Generated {$result['total']} optimization tips");
        WP_CLI::line("Current SEO Score: {$result['current_score']}/100");
        WP_CLI::line('');
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $table_data = [];
            foreach ($result['tips'] as $tip) {
                $table_data[] = [
                    'Category' => $tip['category'],
                    'Priority' => $tip['priority'],
                    'Tip' => wp_trim_words($tip['tip'], 15),
                    'Impact' => $tip['impact']
                ];
            }
            WP_CLI\Utils\format_items('table', $table_data, ['Category', 'Priority', 'Tip', 'Impact']);
        }
    }
    
    /**
     * Get trending topics in a niche
     *
     * ## OPTIONS
     *
     * <niche>
     * : The niche or industry
     *
     * [--timeframe=<timeframe>]
     * : Timeframe for trending topics
     * ---
     * default: week
     * options:
     *   - week
     *   - month
     *   - year
     * ---
     *
     * [--count=<count>]
     * : Number of topics
     * ---
     * default: 10
     * ---
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
     *     wp aiseo content trending "WordPress SEO"
     *     wp aiseo content trending "Digital Marketing" --timeframe=month
     *     wp aiseo content trending "AI Tools" --count=15 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function trending($args, $assoc_args) {
        $niche = $args[0];
        $timeframe = isset($assoc_args['timeframe']) ? $assoc_args['timeframe'] : 'week';
        $count = isset($assoc_args['count']) ? absint($assoc_args['count']) : 10;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::line("Finding trending topics in {$niche}...");
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->get_trending_topics($niche, [
            'timeframe' => $timeframe,
            'count' => $count
        ]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success("Found {$result['total']} trending topics");
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $table_data = [];
            foreach ($result['topics'] as $topic) {
                $table_data[] = [
                    'Topic' => $topic['topic'],
                    'Volume' => $topic['volume'] ?? 'N/A',
                    'Reason' => wp_trim_words($topic['reason'] ?? '', 10)
                ];
            }
            WP_CLI\Utils\format_items('table', $table_data, ['Topic', 'Volume', 'Reason']);
        }
    }
    
    /**
     * Generate content brief for a topic
     *
     * ## OPTIONS
     *
     * <topic>
     * : The topic or title
     *
     * [--focus-keyword=<keyword>]
     * : Focus keyword
     *
     * [--target-audience=<audience>]
     * : Target audience
     *
     * [--word-count=<count>]
     * : Target word count
     * ---
     * default: 1500
     * ---
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: yaml
     * options:
     *   - yaml
     *   - json
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo content brief "How to Optimize WordPress for SEO"
     *     wp aiseo content brief "Best SEO Plugins" --focus-keyword="seo plugins"
     *     wp aiseo content brief "SEO Guide" --word-count=2000 --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function brief($args, $assoc_args) {
        $topic = $args[0];
        $focus_keyword = isset($assoc_args['focus-keyword']) ? $assoc_args['focus-keyword'] : '';
        $target_audience = isset($assoc_args['target-audience']) ? $assoc_args['target-audience'] : '';
        $word_count = isset($assoc_args['word-count']) ? absint($assoc_args['word-count']) : 1500;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'yaml';
        
        WP_CLI::line("Generating content brief for: {$topic}");
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->generate_content_brief($topic, [
            'focus_keyword' => $focus_keyword,
            'target_audience' => $target_audience,
            'word_count' => $word_count
        ]);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success('Content brief generated successfully');
        WP_CLI::line('');
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            // YAML-like output
            $brief = $result['brief'];
            WP_CLI::line("Topic: {$topic}");
            WP_CLI::line("SEO Title: " . ($brief['title'] ?? 'N/A'));
            WP_CLI::line("Meta Description: " . ($brief['meta_description'] ?? 'N/A'));
            WP_CLI::line('');
            
            if (!empty($brief['primary_keywords'])) {
                WP_CLI::line('Primary Keywords:');
                foreach ($brief['primary_keywords'] as $keyword) {
                    WP_CLI::line("  - {$keyword}");
                }
                WP_CLI::line('');
            }
            
            if (!empty($brief['structure'])) {
                WP_CLI::line('Content Structure:');
                foreach ($brief['structure'] as $heading) {
                    WP_CLI::line("  - {$heading}");
                }
                WP_CLI::line('');
            }
            
            if (!empty($brief['key_points'])) {
                WP_CLI::line('Key Points to Cover:');
                foreach ($brief['key_points'] as $point) {
                    WP_CLI::line("  - {$point}");
                }
            }
        }
    }
    
    /**
     * Analyze content gaps
     *
     * ## OPTIONS
     *
     * <niche>
     * : The niche or industry
     *
     * [--post-type=<type>]
     * : Post type to analyze
     * ---
     * default: post
     * ---
     *
     * [--limit=<limit>]
     * : Number of existing posts to analyze
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
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo content gaps "WordPress SEO"
     *     wp aiseo content gaps "Digital Marketing" --limit=100
     *     wp aiseo content gaps "AI Tools" --format=json
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function gaps($args, $assoc_args) {
        $niche = $args[0];
        $post_type = isset($assoc_args['post-type']) ? $assoc_args['post-type'] : 'post';
        $limit = isset($assoc_args['limit']) ? absint($assoc_args['limit']) : 50;
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
        
        WP_CLI::line("Analyzing existing content in {$niche}...");
        
        // Get existing posts
        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        $existing_topics = array_map(function($post) {
            return $post->post_title;
        }, $posts);
        
        WP_CLI::line("Found {count($existing_topics)} existing posts");
        WP_CLI::line('Identifying content gaps...');
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $result = $content_suggestions->analyze_content_gaps($existing_topics, $niche);
        
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
            return;
        }
        
        WP_CLI::success("Identified {$result['total_gaps']} content gaps");
        
        if ($format === 'json') {
            WP_CLI::line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $table_data = [];
            foreach ($result['gaps'] as $gap) {
                $table_data[] = [
                    'Topic' => $gap['topic'],
                    'Priority' => $gap['priority'],
                    'Traffic Potential' => $gap['traffic_potential'] ?? 'N/A',
                    'Reason' => wp_trim_words($gap['reason'] ?? '', 10)
                ];
            }
            WP_CLI\Utils\format_items('table', $table_data, ['Topic', 'Priority', 'Traffic Potential', 'Reason']);
        }
    }
    
    /**
     * Clear content suggestions cache
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Cache type to clear
     * ---
     * default: all
     * options:
     *   - all
     *   - topics
     *   - tips
     *   - trending
     *   - brief
     * ---
     *
     * ## EXAMPLES
     *
     *     wp aiseo content clear-cache
     *     wp aiseo content clear-cache --type=topics
     *
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     */
    public function clear_cache($args, $assoc_args) {
        $type = isset($assoc_args['type']) ? $assoc_args['type'] : 'all';
        
        $content_suggestions = new AISEO_Content_Suggestions();
        $content_suggestions->clear_cache($type);
        
        WP_CLI::success("Content suggestions cache cleared: {$type}");
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('aiseo content topics', ['AISEO_Content_Suggestions_CLI', 'topics']);
    WP_CLI::add_command('aiseo content optimize', ['AISEO_Content_Suggestions_CLI', 'optimize']);
    WP_CLI::add_command('aiseo content trending', ['AISEO_Content_Suggestions_CLI', 'trending']);
    WP_CLI::add_command('aiseo content brief', ['AISEO_Content_Suggestions_CLI', 'brief']);
    WP_CLI::add_command('aiseo content gaps', ['AISEO_Content_Suggestions_CLI', 'gaps']);
    WP_CLI::add_command('aiseo content clear-cache', ['AISEO_Content_Suggestions_CLI', 'clear_cache']);
}
