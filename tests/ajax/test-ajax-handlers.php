<?php
/**
 * WP-CLI command to test AISEO AJAX handlers
 * 
 * Usage: wp eval-file wp-content/plugins/aiseo/test-ajax-cli.php
 */

if (!defined('WP_CLI') && php_sapi_name() === 'cli') {
    // Load WordPress if running from CLI but not WP-CLI
    // Try to find wp-load.php by going up from the plugin directory
    $wp_load = dirname(__FILE__) . '/../../../../../../wp-load.php';
    
    // If not found, check if WP_ROOT environment variable is set
    if (!file_exists($wp_load) && getenv('WP_ROOT')) {
        $wp_load = getenv('WP_ROOT') . '/wp-load.php';
    }
    
    if (!file_exists($wp_load)) {
        echo "ERROR: Cannot find wp-load.php\n";
        echo "Please set WP_ROOT environment variable or run from WordPress installation:\n";
        echo "  export WP_ROOT=/path/to/wordpress\n";
        echo "  php tests/ajax/test-ajax-handlers.php\n";
        exit(1);
    }
    require_once($wp_load);
}

echo "=== AISEO AJAX HANDLER TEST ===\n\n";

// Load AISEO plugin classes
$plugin_dir = dirname(__FILE__) . '/../..';
require_once($plugin_dir . '/includes/class-aiseo-api.php');
require_once($plugin_dir . '/admin/class-aiseo-admin.php');
echo "✓ AISEO classes loaded\n\n";

// Set up user context (simulate logged-in admin)
$admin_user = get_users(['role' => 'administrator', 'number' => 1]);
if (empty($admin_user)) {
    echo "ERROR: No admin user found!\n";
    exit(1);
}

$user_id = $admin_user[0]->ID;
wp_set_current_user($user_id);
echo "✓ Set current user to: {$admin_user[0]->user_login} (ID: $user_id)\n";
echo "✓ User can edit_posts: " . (current_user_can('edit_posts') ? 'YES' : 'NO') . "\n\n";

// Generate a fresh nonce
$nonce = wp_create_nonce('aiseo_admin_nonce');
echo "✓ Generated nonce: $nonce\n";

// Verify it immediately
$check = wp_verify_nonce($nonce, 'aiseo_admin_nonce');
echo "✓ Nonce verification: " . ($check ? "VALID ($check)" : "INVALID") . "\n\n";

// Get a test post
$posts = get_posts(['numberposts' => 1, 'post_status' => 'any']);
if (empty($posts)) {
    echo "ERROR: No posts found!\n";
    exit(1);
}

$post_id = $posts[0]->ID;
echo "✓ Using post: {$posts[0]->post_title} (ID: $post_id)\n\n";

// Simulate the AJAX request
echo "--- Simulating AJAX Request ---\n";
$_POST = [
    'action' => 'aiseo_generate_title',
    'post_id' => $post_id,
    'nonce' => $nonce
];
$_REQUEST = $_POST;

echo "POST data:\n";
print_r($_POST);
echo "\n";

// Call the AJAX handler directly
echo "--- Calling ajax_generate_title() ---\n";
try {
    // Get the admin class instance
    $admin_class = new AISEO_Admin();
    
    // Capture output
    ob_start();
    $admin_class->ajax_generate_title();
    $output = ob_get_clean();
    
    echo "Output: $output\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
