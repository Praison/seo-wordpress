<?php
/**
 * @package Praison SEO WordPress
 * @author Mervin Praison
 * @version 4.0.12
 */
/*
    Plugin Name: Praison SEO WordPress
    Plugin URI: https://praison.com/wordpress/plugins/seo/
    Description: SEO Wordpress Plugin by Mervin Praison is a Powerfull Best Optimisation Plugin which has many SEO Features. Google Webmasters and Google Analytics Integration. Very Easy to Setup. Check all benefits here https://mer.vin/seo-wordpress/
    Author: Mervin Praison
    Version: 4.0.16
    License: GPL
    Author URI: https://praison.com/
    Last change: 11.09.2019
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
require_once ( 'inc/seo-deprecated.php');

// custom css and js
add_action('admin_enqueue_scripts', 'cstm_css_and_js');

function cstm_css_and_js($hook) {
	
     // your-slug => The slug name to refer to this menu used in "add_submenu_page"
     // tools_page => refers to Tools top menu, so it's a Tools' sub-menu page
    if ( 'post.php' == $hook ) {
        
        //wp_enqueue_style('yseostyle_css', plugins_url('css/yseo/yseo-style.css',__FILE__ ));
        //wp_enqueue_style('yseo_css', plugins_url('css/yseo/yseo-min.css',__FILE__ ));
        //wp_enqueue_script('yseo_js', plugins_url('js/yseo.js',__FILE__ ), array( 'jquery' ), false, true );
        wp_enqueue_style('praisonseo_css', plugins_url('css/praisonseo.css',__FILE__ ));
        wp_enqueue_script('praisonseo_js', plugins_url('js/praisonseo.js',__FILE__ ), array( 'jquery' ), false, true );
    }

     if ( 'seo-wordpress/admin/seo-dashboard.php' != $hook && 
     	'seo-wordpress/admin/seo-authorship.php' != $hook &&
     	'seo-wordpress/admin/seo-xml-sitemap.php' != $hook &&
     	'seo-wordpress/admin/seo-breadcrumbs.php' != $hook &&
     	'seo-wordpress/admin/seo-rss.php' != $hook 
 		) {
         return;
     }

    wp_enqueue_style('boot_css', plugins_url('css/bootstrap.min.css',__FILE__ ));
    wp_enqueue_style('fa_css', plugins_url('css/font-awesome.min.css',__FILE__ ));
    wp_enqueue_style('praison_seo_css', plugins_url('css/main.css',__FILE__ ));
    wp_enqueue_script('boot_js', plugins_url('js/bootstrap.min.js',__FILE__ ), array( 'jquery' ), false, true );
    
 }


// include (SEO_URL.'/seo-wordpress/authorship/seo-authorship.php');

register_activation_hook(__FILE__, 'zeo_activate');


$zeo = new zeo_head_class();

add_action( 'wp_head', array( $zeo, 'zeo_head') );


?>
