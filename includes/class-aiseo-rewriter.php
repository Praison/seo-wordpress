<?php
/**
 * AISEO Smart Content Rewriter
 *
 * @package AISEO
 * @since 1.14.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Smart Content Rewriter Class
 */
class AISEO_Rewriter {
    
    private $api;
    
    public function __construct() {
        $this->api = new AISEO_API();
    }
    
    /**
     * Rewrite content
     *
     * @param string $content Original content
     * @param array $options Rewrite options
     * @return array|WP_Error Rewritten content
     */
    public function rewrite($content, $options = []) {
        $mode = isset($options['mode']) ? $options['mode'] : 'improve';
        $tone = isset($options['tone']) ? $options['tone'] : 'professional';
        $keyword = isset($options['keyword']) ? $options['keyword'] : '';
        
        $prompts = [
            'improve' => "Improve the following content while maintaining its core message. Make it more engaging, clear, and SEO-friendly.",
            'simplify' => "Rewrite the following content in simpler language that's easier to understand.",
            'expand' => "Expand the following content with more details, examples, and explanations.",
            'shorten' => "Condense the following content while keeping the key points.",
            'professional' => "Rewrite the following content in a more professional tone.",
            'casual' => "Rewrite the following content in a more casual, conversational tone."
        ];
        
        $prompt = isset($prompts[$mode]) ? $prompts[$mode] : $prompts['improve'];
        
        if (!empty($keyword)) {
            $prompt .= " Focus on the keyword: {$keyword}.";
        }
        
        $prompt .= "\n\nOriginal content:\n" . wp_trim_words($content, 1000);
        
        $response = $this->api->make_request($prompt, [
            'max_tokens' => 2000,
            'temperature' => 0.7
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return [
            'original' => $content,
            'rewritten' => $response,
            'mode' => $mode,
            'tone' => $tone,
            'improvements' => $this->analyze_improvements($content, $response)
        ];
    }
    
    /**
     * Analyze improvements
     */
    private function analyze_improvements($original, $rewritten) {
        $original_words = str_word_count(wp_strip_all_tags($original));
        $rewritten_words = str_word_count(wp_strip_all_tags($rewritten));
        
        return [
            'word_count_change' => $rewritten_words - $original_words,
            'word_count_percentage' => $original_words > 0 ? round((($rewritten_words - $original_words) / $original_words) * 100, 1) : 0,
            'original_words' => $original_words,
            'rewritten_words' => $rewritten_words
        ];
    }
    
    /**
     * Rewrite paragraph
     */
    public function rewrite_paragraph($paragraph, $options = []) {
        $mode = isset($options['mode']) ? $options['mode'] : 'improve';
        
        $prompt = "Rewrite this paragraph to {$mode} it:\n\n{$paragraph}";
        
        $response = $this->api->make_request($prompt, [
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $response;
    }
    
    /**
     * Rewrite sentence
     */
    public function rewrite_sentence($sentence, $options = []) {
        $mode = isset($options['mode']) ? $options['mode'] : 'improve';
        
        $prompt = "Rewrite this sentence to {$mode} it:\n\n{$sentence}";
        
        $response = $this->api->make_request($prompt, [
            'max_tokens' => 200,
            'temperature' => 0.7
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $response;
    }
}
