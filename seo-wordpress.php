<?php
/**
 * @package SEO Wordpress
 * @author Mervin Praison
 * @version 4.0.1
 */
/*
    Plugin Name: SEO Wordpress
    Plugin URI: http://mer.vin/seo-wordpress/
    Description: SEO Wordpress Plugin by Mervin Praison is a Powerfull Best Optimisation Plugin which has many SEO Features. Google Authorship and Google Analytics Integration. Very Easy to Setup. Check all benefits here http://mervin.info/seo-wordpress/
    Author: Mervin Praison
    Version: 4.0.3
    License: GPL
    Author URI: http://mer.vin/
    Last change: 08.01.2018
*/


define( 'SEO_URL', plugin_dir_url(__FILE__) );
define( 'SEO_PATH', plugin_dir_path(__FILE__) );
define( 'SEO_BASENAME', plugin_basename( __FILE__ ) );
define( 'SEO_ADMIN_DIRECTORY', 'seo-wordpress/admin');
$pluginurl = plugin_dir_url( __FILE__ );
if ( preg_match( '/^https/', $pluginurl ) && !preg_match( '/^https/', get_bloginfo('url') ) )
	$pluginurl = preg_replace( '/^https/', 'http', $pluginurl );
define( 'ZEO_FRONT_URL', $pluginurl );

global $post;
require_once ( 'seo-global-functions.php');
require_once ( 'seo-data-class.php');
require_once ( 'seo-metabox-class.php');
require_once ( 'seo-metafunctions.php');
require_once ( 'seo-rewritetitle-class.php');
require_once ( 'seo-authorship.php');
require_once ( 'seo-authorship-badge.php');
require_once ( 'seo-authorship-icon.php');
require_once ( 'seo-taxonomy.php');
require_once ( 'seo-breadcrumbs.php');
require_once ( 'seo-sitemaps.php');

// custom css and js
add_action('admin_enqueue_scripts', 'cstm_css_and_js');

function cstm_css_and_js($hook) {
	
     // your-slug => The slug name to refer to this menu used in "add_submenu_page"
     // tools_page => refers to Tools top menu, so it's a Tools' sub-menu page
     if ( 'seo-wordpress/admin/seo-dashboard.php' != $hook && 
     	'seo-wordpress/admin/seo-authorship.php' != $hook &&
     	'seo-wordpress/admin/seo-xml-sitemap.php' != $hook &&
     	'seo-wordpress/admin/seo-breadcrumbs.php' != $hook &&
     	'seo-wordpress/admin/seo-rss.php' != $hook 
 		) {
         return;
     }

    wp_enqueue_style('boot_css', plugins_url('css/bootstrap.min.css',__FILE__ ));
     wp_enqueue_script('boot_js', plugins_url('js/bootstrap.min.js',__FILE__ ), array( 'jquery' ), false, true );
 }


// include (SEO_URL.'/seo-wordpress/authorship/seo-authorship.php');

register_activation_hook(__FILE__, 'zeo_activate');


$zeo = new zeo_head_class();

add_action( 'wp_head', array( $zeo, 'zeo_head') );


?>