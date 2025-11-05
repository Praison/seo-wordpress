<?php
/**
 * Quick nonce verification test
 * Run from command line: php tests/verify-nonce.php
 */

// Load WordPress
require_once('/Users/praison/Sites/localhost/wordpress/wp-load.php');

echo "=== NONCE VERIFICATION TEST ===\n\n";

// The nonce from the test
$test_nonce = 'b793e8733a';

echo "Testing nonce: $test_nonce\n";
echo "Action: aiseo_admin_nonce\n\n";

// Try to verify it
$result = wp_verify_nonce($test_nonce, 'aiseo_admin_nonce');

echo "wp_verify_nonce result: " . var_export($result, true) . "\n";
echo "Meaning: ";
if ($result === 1) {
    echo "VALID - Fresh (0-12 hours)\n";
} elseif ($result === 2) {
    echo "VALID - Old (12-24 hours)\n";
} elseif ($result === false) {
    echo "INVALID - Nonce verification failed\n";
}

echo "\n";

// Generate a fresh nonce for comparison
$fresh_nonce = wp_create_nonce('aiseo_admin_nonce');
echo "Fresh nonce for comparison: $fresh_nonce\n";
echo "Do they match? " . ($test_nonce === $fresh_nonce ? "YES" : "NO") . "\n";

echo "\n=== END TEST ===\n";
