<?php
/**
 * AISEO Helper Functions
 *
 * Utility functions used across the plugin
 *
 * @package AISEO
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AISEO_Helpers {
    
    /**
     * Generate encryption keys and save to wp-config.php
     */
    public static function generate_encryption_keys() {
        $config_file = ABSPATH . 'wp-config.php';
        
        if (!is_writable($config_file)) {
            return false;
        }
        
        // Generate random keys
        $encryption_key = bin2hex(random_bytes(32));
        $encryption_salt = bin2hex(random_bytes(32));
        
        // Read wp-config.php
        $config_content = file_get_contents($config_file);
        
        // Check if keys already exist
        if (strpos($config_content, 'AISEO_ENCRYPTION_KEY') !== false) {
            return true;
        }
        
        // Add keys before the "That's all, stop editing!" line
        $keys_code = "\n// AISEO Encryption Keys\n";
        $keys_code .= "define('AISEO_ENCRYPTION_KEY', '$encryption_key');\n";
        $keys_code .= "define('AISEO_ENCRYPTION_SALT', '$encryption_salt');\n";
        
        $config_content = str_replace(
            "/* That's all, stop editing!",
            $keys_code . "/* That's all, stop editing!",
            $config_content
        );
        
        file_put_contents($config_file, $config_content);
        
        return true;
    }
    
    /**
     * Encrypt API key
     *
     * @param string $key API key to encrypt
     * @return string Encrypted key
     */
    public static function encrypt_api_key($key) {
        if (empty($key)) {
            return '';
        }
        
        if (!defined('AISEO_ENCRYPTION_KEY')) {
            return $key; // Return unencrypted if no key available
        }
        
        $cipher = 'AES-256-CBC';
        $iv_length = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt(
            $key,
            $cipher,
            AISEO_ENCRYPTION_KEY,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Decrypt API key
     *
     * @param string $encrypted_key Encrypted API key
     * @return string Decrypted key
     */
    public static function decrypt_api_key($encrypted_key) {
        if (empty($encrypted_key)) {
            return '';
        }
        
        if (!defined('AISEO_ENCRYPTION_KEY')) {
            return $encrypted_key; // Return as-is if no key available
        }
        
        $cipher = 'AES-256-CBC';
        $decoded = base64_decode($encrypted_key);
        
        if (strpos($decoded, '::') === false) {
            return $encrypted_key; // Not encrypted, return as-is
        }
        
        list($encrypted_data, $iv) = explode('::', $decoded, 2);
        
        return openssl_decrypt(
            $encrypted_data,
            $cipher,
            AISEO_ENCRYPTION_KEY,
            OPENSSL_RAW_DATA,
            $iv
        );
    }
    
    /**
     * Sanitize API key
     *
     * @param string $key API key
     * @return string Sanitized key
     */
    public static function sanitize_api_key($key) {
        return sanitize_text_field(trim($key));
    }
    
    /**
     * Calculate reading time
     *
     * @param string $content Post content
     * @return int Reading time in minutes
     */
    public static function calculate_reading_time($content) {
        $word_count = str_word_count(strip_tags($content));
        $reading_time = ceil($word_count / 200); // Average reading speed: 200 words/minute
        
        return max(1, $reading_time);
    }
    
    /**
     * Truncate text smartly
     *
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @param string $suffix Suffix to add
     * @return string Truncated text
     */
    public static function truncate_text($text, $length = 160, $suffix = '...') {
        $text = strip_tags($text);
        
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = substr($text, 0, $length);
        
        // Try to break at word boundary
        $last_space = strrpos($truncated, ' ');
        if ($last_space !== false) {
            $truncated = substr($truncated, 0, $last_space);
        }
        
        return $truncated . $suffix;
    }
    
    /**
     * Strip shortcodes and tags from content
     *
     * @param string $content Content to clean
     * @return string Cleaned content
     */
    public static function strip_shortcodes_and_tags($content) {
        // Remove shortcodes
        $content = strip_shortcodes($content);
        
        // Remove HTML tags
        $content = wp_strip_all_tags($content);
        
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        
        return trim($content);
    }
    
    /**
     * Get focus keyword for a post
     *
     * @param int $post_id Post ID
     * @return string Focus keyword
     */
    public static function get_focus_keyword($post_id) {
        return get_post_meta($post_id, '_aiseo_focus_keyword', true);
    }
    
    /**
     * Calculate Flesch-Kincaid readability score
     *
     * @param string $content Content to analyze
     * @return float Readability score
     */
    public static function calculate_flesch_kincaid($content) {
        $content = self::strip_shortcodes_and_tags($content);
        
        // Count sentences
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentence_count = count($sentences);
        
        if ($sentence_count === 0) {
            return 0;
        }
        
        // Count words
        $words = str_word_count($content);
        
        if ($words === 0) {
            return 0;
        }
        
        // Count syllables
        $syllables = self::count_syllables_in_text($content);
        
        // Flesch Reading Ease formula
        // 206.835 - 1.015 * (words/sentences) - 84.6 * (syllables/words)
        $score = 206.835 - 1.015 * ($words / $sentence_count) - 84.6 * ($syllables / $words);
        
        return max(0, min(100, $score));
    }
    
    /**
     * Count syllables in text
     *
     * @param string $text Text to analyze
     * @return int Syllable count
     */
    public static function count_syllables_in_text($text) {
        $words = str_word_count($text, 1);
        $syllables = 0;
        
        foreach ($words as $word) {
            $syllables += self::count_syllables($word);
        }
        
        return $syllables;
    }
    
    /**
     * Count syllables in a word
     *
     * @param string $word Word to analyze
     * @return int Syllable count
     */
    public static function count_syllables($word) {
        $word = strtolower($word);
        $word = preg_replace('/[^a-z]/', '', $word);
        
        if (strlen($word) <= 3) {
            return 1;
        }
        
        // Remove silent 'e' at the end
        $word = preg_replace('/e$/', '', $word);
        
        // Count vowel groups
        $vowels = array('a', 'e', 'i', 'o', 'u', 'y');
        $syllables = 0;
        $previous_was_vowel = false;
        
        for ($i = 0; $i < strlen($word); $i++) {
            $is_vowel = in_array($word[$i], $vowels);
            
            if ($is_vowel && !$previous_was_vowel) {
                $syllables++;
            }
            
            $previous_was_vowel = $is_vowel;
        }
        
        return max(1, $syllables);
    }
    
    /**
     * Log message to database
     *
     * @param string $level Log level (DEBUG, INFO, WARNING, ERROR, CRITICAL)
     * @param string $category Category (api_request, cache, security, performance)
     * @param string $message Log message
     * @param array $context Additional context data
     * @param int $user_id User ID
     * @param int $post_id Post ID
     */
    public static function log($level, $category, $message, $context = array(), $user_id = null, $post_id = null) {
        global $wpdb;
        
        // Check if debug mode is enabled for DEBUG level
        if ($level === 'DEBUG' && !defined('AISEO_DEBUG')) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'aiseo_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => current_time('mysql'),
                'level' => $level,
                'category' => $category,
                'message' => $message,
                'context' => json_encode($context),
                'user_id' => $user_id ?: get_current_user_id(),
                'post_id' => $post_id,
                'trace_id' => self::generate_trace_id(),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
        );
        
        // Also log to error_log for critical errors
        if (in_array($level, array('ERROR', 'CRITICAL'))) {
            error_log(sprintf('[AISEO %s] %s: %s', $level, $category, $message));
        }
    }
    
    /**
     * Generate unique trace ID
     *
     * @return string Trace ID
     */
    public static function generate_trace_id() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Get API key from options or environment
     *
     * @return string API key
     */
    public static function get_api_key() {
        // First check environment variable
        if (defined('OPENAI_API_KEY')) {
            return OPENAI_API_KEY;
        }
        
        // Then check .env file
        $env_key = getenv('OPENAI_API_KEY');
        if ($env_key) {
            return $env_key;
        }
        
        // Finally check database
        $encrypted_key = get_option('aiseo_openai_api_key');
        if ($encrypted_key) {
            return self::decrypt_api_key($encrypted_key);
        }
        
        return '';
    }
    
    /**
     * Save API key to database (encrypted)
     *
     * @param string $key API key
     * @return bool Success
     */
    public static function save_api_key($key) {
        $key = self::sanitize_api_key($key);
        
        if (empty($key)) {
            return delete_option('aiseo_openai_api_key');
        }
        
        $encrypted = self::encrypt_api_key($key);
        return update_option('aiseo_openai_api_key', $encrypted);
    }
    
    /**
     * Get content hash for caching
     *
     * @param string $content Content to hash
     * @param string $keyword Focus keyword
     * @return string MD5 hash
     */
    public static function get_content_hash($content, $keyword = '') {
        $content = substr($content, 0, 1000); // First 1000 chars
        return md5($content . $keyword . get_option('aiseo_version'));
    }
}
