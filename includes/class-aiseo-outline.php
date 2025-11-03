<?php
/**
 * AISEO Content Outline Generator
 *
 * @package AISEO
 * @since 1.13.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Content Outline Generator Class
 */
class AISEO_Outline {
    
    private $api;
    
    public function __construct() {
        $this->api = new AISEO_API();
    }
    
    /**
     * Generate content outline
     *
     * @param string $topic Topic or title
     * @param string $keyword Focus keyword
     * @param array $options Additional options
     * @return array|WP_Error Generated outline
     */
    public function generate($topic, $keyword = '', $options = []) {
        $word_count = isset($options['word_count']) ? $options['word_count'] : 1500;
        $tone = isset($options['tone']) ? $options['tone'] : 'professional';
        
        $prompt = "Create a detailed content outline for: '{$topic}'\n\n";
        
        if (!empty($keyword)) {
            $prompt .= "Focus keyword: {$keyword}\n";
        }
        
        $prompt .= "Target word count: {$word_count}\n";
        $prompt .= "Tone: {$tone}\n\n";
        $prompt .= "Include:\n";
        $prompt .= "1. Introduction (hook, problem, solution preview)\n";
        $prompt .= "2. Main sections with H2 headings\n";
        $prompt .= "3. Subsections with H3 headings\n";
        $prompt .= "4. Key points to cover in each section\n";
        $prompt .= "5. Conclusion\n";
        $prompt .= "6. Call-to-action suggestions\n\n";
        $prompt .= "Format as structured JSON.";
        
        $response = $this->api->make_request($prompt, [
            'max_tokens' => 1500,
            'temperature' => 0.8
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $outline = $this->parse_outline_response($response);
        
        return [
            'topic' => $topic,
            'keyword' => $keyword,
            'outline' => $outline,
            'estimated_word_count' => $word_count,
            'html' => $this->generate_outline_html($outline)
        ];
    }
    
    /**
     * Parse outline response
     */
    private function parse_outline_response($response) {
        // Try JSON first
        $json = json_decode($response, true);
        if ($json && is_array($json)) {
            return $json;
        }
        
        // Parse text format
        $outline = [
            'introduction' => [],
            'sections' => [],
            'conclusion' => [],
            'cta' => []
        ];
        
        $lines = explode("\n", $response);
        $current_section = null;
        $current_subsection = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // H2 headings
            if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
                $current_section = [
                    'heading' => $matches[1],
                    'subsections' => [],
                    'points' => []
                ];
                $outline['sections'][] = &$current_section;
            }
            // H3 headings
            elseif (preg_match('/^###\s+(.+)$/', $line, $matches)) {
                if ($current_section) {
                    $current_subsection = [
                        'heading' => $matches[1],
                        'points' => []
                    ];
                    $current_section['subsections'][] = &$current_subsection;
                }
            }
            // Bullet points
            elseif (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
                if ($current_subsection) {
                    $current_subsection['points'][] = $matches[1];
                } elseif ($current_section) {
                    $current_section['points'][] = $matches[1];
                }
            }
        }
        
        return $outline;
    }
    
    /**
     * Generate outline HTML
     */
    private function generate_outline_html($outline) {
        $html = '<div class="aiseo-outline">';
        
        if (!empty($outline['introduction'])) {
            $html .= '<h2>Introduction</h2><ul>';
            foreach ($outline['introduction'] as $point) {
                $html .= '<li>' . esc_html($point) . '</li>';
            }
            $html .= '</ul>';
        }
        
        if (!empty($outline['sections'])) {
            foreach ($outline['sections'] as $section) {
                $html .= '<h2>' . esc_html($section['heading']) . '</h2>';
                
                if (!empty($section['points'])) {
                    $html .= '<ul>';
                    foreach ($section['points'] as $point) {
                        $html .= '<li>' . esc_html($point) . '</li>';
                    }
                    $html .= '</ul>';
                }
                
                if (!empty($section['subsections'])) {
                    foreach ($section['subsections'] as $subsection) {
                        $html .= '<h3>' . esc_html($subsection['heading']) . '</h3>';
                        if (!empty($subsection['points'])) {
                            $html .= '<ul>';
                            foreach ($subsection['points'] as $point) {
                                $html .= '<li>' . esc_html($point) . '</li>';
                            }
                            $html .= '</ul>';
                        }
                    }
                }
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Save outline to post meta
     */
    public function save_to_post($post_id, $outline) {
        update_post_meta($post_id, '_aiseo_outline', $outline);
        return true;
    }
    
    /**
     * Get outline from post
     */
    public function get_from_post($post_id) {
        return get_post_meta($post_id, '_aiseo_outline', true);
    }
}
