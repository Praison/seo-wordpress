<?php
/**
 * AISEO Webmaster Verification
 *
 * Handles webmaster tool verification codes (Google, Bing, etc.)
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Webmaster {
    
    /**
     * Option keys for verification codes
     */
    const OPTION_GOOGLE = 'aiseo_verification_google';
    const OPTION_BING = 'aiseo_verification_bing';
    const OPTION_YANDEX = 'aiseo_verification_yandex';
    const OPTION_PINTEREST = 'aiseo_verification_pinterest';
    const OPTION_BAIDU = 'aiseo_verification_baidu';
    
    /**
     * Initialize webmaster verification
     */
    public function init() {
        // Output verification meta tags in head
        add_action('wp_head', array($this, 'output_verification_tags'), 1);
    }
    
    /**
     * Get all verification codes
     *
     * @return array Verification codes
     */
    public function get_verification_codes() {
        return array(
            'google' => get_option(self::OPTION_GOOGLE, ''),
            'bing' => get_option(self::OPTION_BING, ''),
            'yandex' => get_option(self::OPTION_YANDEX, ''),
            'pinterest' => get_option(self::OPTION_PINTEREST, ''),
            'baidu' => get_option(self::OPTION_BAIDU, ''),
        );
    }
    
    /**
     * Update verification codes
     *
     * @param array $codes Verification codes to update
     * @return bool Success
     */
    public function update_verification_codes($codes) {
        $updated = false;
        
        if (isset($codes['google'])) {
            update_option(self::OPTION_GOOGLE, $this->sanitize_verification_code($codes['google']));
            $updated = true;
        }
        
        if (isset($codes['bing'])) {
            update_option(self::OPTION_BING, $this->sanitize_verification_code($codes['bing']));
            $updated = true;
        }
        
        if (isset($codes['yandex'])) {
            update_option(self::OPTION_YANDEX, $this->sanitize_verification_code($codes['yandex']));
            $updated = true;
        }
        
        if (isset($codes['pinterest'])) {
            update_option(self::OPTION_PINTEREST, $this->sanitize_verification_code($codes['pinterest']));
            $updated = true;
        }
        
        if (isset($codes['baidu'])) {
            update_option(self::OPTION_BAIDU, $this->sanitize_verification_code($codes['baidu']));
            $updated = true;
        }
        
        return $updated;
    }
    
    /**
     * Sanitize verification code
     * Extracts just the code if a full meta tag is provided
     *
     * @param string $code Verification code or meta tag
     * @return string Sanitized code
     */
    private function sanitize_verification_code($code) {
        $code = trim($code);
        
        // If it's a full meta tag, extract the content
        if (preg_match('/content=["\']([^"\']+)["\']/', $code, $matches)) {
            $code = $matches[1];
        }
        
        // Remove any HTML tags
        $code = wp_strip_all_tags($code);
        
        return sanitize_text_field($code);
    }
    
    /**
     * Output verification meta tags
     */
    public function output_verification_tags() {
        $codes = $this->get_verification_codes();
        
        // Google Search Console
        if (!empty($codes['google'])) {
            echo '<meta name="google-site-verification" content="' . esc_attr($codes['google']) . '">' . "\n";
        }
        
        // Bing Webmaster Tools
        if (!empty($codes['bing'])) {
            echo '<meta name="msvalidate.01" content="' . esc_attr($codes['bing']) . '">' . "\n";
        }
        
        // Yandex Webmaster
        if (!empty($codes['yandex'])) {
            echo '<meta name="yandex-verification" content="' . esc_attr($codes['yandex']) . '">' . "\n";
        }
        
        // Pinterest
        if (!empty($codes['pinterest'])) {
            echo '<meta name="p:domain_verify" content="' . esc_attr($codes['pinterest']) . '">' . "\n";
        }
        
        // Baidu
        if (!empty($codes['baidu'])) {
            echo '<meta name="baidu-site-verification" content="' . esc_attr($codes['baidu']) . '">' . "\n";
        }
    }
}
