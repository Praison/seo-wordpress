<?php
/**
 * @package SEO Wordpress
 * @author Mervin Praison
 * @version 2.0
 */
/*
    Plugin Name: SEO Wordpress
    Plugin URI: http://mervin.info/seo-wordpress/
    Description: SEO Wordpress Plugin by Mervin Praison is a Powerfull Best Optimisation Plugin which has many SEO Features. Google Authorship and Google Analytics Integration. Very Easy to Setup. Check all benefits here http://mervin.info/seo-wordpress/
    Author: Mervin Praison
    Version: 2.0
    License: GPL
    Author URI: http://mervin.info/
    Last change: 30.04.2012
*/


define( 'SEO_URL', plugin_dir_url(__FILE__) );
define( 'SEO_PATH', plugin_dir_path(__FILE__) );
define( 'SEO_BASENAME', plugin_basename( __FILE__ ) );
define( 'SEO_ADMIN_DIRECTORY', 'seo-wordpress/admin');

global $post;
require_once ( 'seo-global-functions.php');
require_once ( 'seo-data-class.php');
require_once ( 'seo-metabox-class.php');
require_once ( 'seo-metafunctions.php');
require_once ( 'seo-rewritetitle-class.php');
require_once ( 'seo-authorship.php');
require_once ( 'seo-authorship-badge.php');
require_once ( 'seo-authorship-icon.php');
require_once ( 'seo-xml-sitemap.php');
// include (SEO_URL.'/seo-wordpress/authorship/seo-authorship.php');

register_activation_hook(__FILE__, 'zeo_activate');


$zeo = new seo_metabox_class();


add_action( 'wp_head', array( $zeo, 'zeo_head') );


?>