<?php
/**
 * AISEO AI-Powered FAQ Generator
 *
 * @package AISEO
 * @since 1.12.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FAQ Generator Class
 */
class AISEO_FAQ {
    
    private $api;
    
    public function __construct() {
        $this->api = new AISEO_API();
    }
    
    /**
     * Generate FAQs from content
     *
     * @param string $content Post content
     * @param int $count Number of FAQs to generate
     * @return array Generated FAQs
     */
    public function generate($content, $count = 5) {
        $prompt = "Based on the following content, generate {$count} frequently asked questions and their answers. Format as JSON array with 'question' and 'answer' keys.\n\nContent:\n" . wp_trim_words($content, 500);
        
        $response = $this->api->make_request($prompt, [
            'max_tokens' => 1000,
            'temperature' => 0.7
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $faqs = $this->parse_faq_response($response);
        
        return [
            'faqs' => $faqs,
            'schema' => $this->generate_faq_schema($faqs),
            'html' => $this->generate_faq_html($faqs)
        ];
    }
    
    /**
     * Parse FAQ response
     */
    private function parse_faq_response($response) {
        // Try to parse JSON
        $json = json_decode($response, true);
        
        if ($json && is_array($json)) {
            return $json;
        }
        
        // Fallback: parse text format
        $faqs = [];
        $lines = explode("\n", $response);
        $current_q = '';
        $current_a = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (preg_match('/^Q\d*[:.]?\s*(.+)$/i', $line, $matches)) {
                if ($current_q && $current_a) {
                    $faqs[] = ['question' => $current_q, 'answer' => $current_a];
                }
                $current_q = $matches[1];
                $current_a = '';
            } elseif (preg_match('/^A\d*[:.]?\s*(.+)$/i', $line, $matches)) {
                $current_a = $matches[1];
            } elseif (!empty($line) && $current_q && !$current_a) {
                $current_a = $line;
            }
        }
        
        if ($current_q && $current_a) {
            $faqs[] = ['question' => $current_q, 'answer' => $current_a];
        }
        
        return $faqs;
    }
    
    /**
     * Generate FAQ Schema
     */
    private function generate_faq_schema($faqs) {
        $main_entity = [];
        
        foreach ($faqs as $faq) {
            $main_entity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $main_entity
        ];
    }
    
    /**
     * Generate FAQ HTML
     */
    private function generate_faq_html($faqs) {
        $html = '<div class="aiseo-faq">';
        
        foreach ($faqs as $index => $faq) {
            $html .= sprintf(
                '<div class="faq-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <h3 itemprop="name">%s</h3>
                    <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <p itemprop="text">%s</p>
                    </div>
                </div>',
                esc_html($faq['question']),
                esc_html($faq['answer'])
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Save FAQs to post meta
     */
    public function save_to_post($post_id, $faqs) {
        update_post_meta($post_id, '_aiseo_faqs', $faqs);
        update_post_meta($post_id, '_aiseo_faq_schema', $this->generate_faq_schema($faqs));
        
        return true;
    }
    
    /**
     * Get FAQs from post
     */
    public function get_from_post($post_id) {
        return get_post_meta($post_id, '_aiseo_faqs', true);
    }
}
