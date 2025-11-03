<?php
if (!defined('ABSPATH')) exit;
$title = str_replace('-', ' ', basename(__FILE__, '.php'));
?>
<div class="aiseo-card">
    <h3><?php echo esc_html(ucwords($title)); ?></h3>
    <p><em><?php esc_html_e('Available via REST API and WP-CLI. UI coming soon.', 'aiseo'); ?></em></p>
</div>
