<?php

function zeo_ischecked_global($chkname,$value)
    {
                  
                if(get_option($chkname) == $value)
                {
                    return true;
                } 
        	return false;
	}
	

/* Admin Menu */

add_action( 'admin_menu', 'zeo_options_menu' );
function zeo_options_menu(){
	
	 // add_options_page('Wordpress SEO Plugin' , 'Wordpress SEO', 9,  SEO_ADMIN_DIRECTORY.'/seo-dashboard.php');
	add_menu_page( 'Wordpress SEO','Wordpress SEO',	0, SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', '', plugins_url('/images/icon.png', __FILE__));
	add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'Dashboard ', 'Dashboard', 0,SEO_ADMIN_DIRECTORY.'/seo-dashboard.php' );
	add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'Authorship, Analytics', 'Authorship, Analytics', 9, SEO_ADMIN_DIRECTORY.'/seo-authorship.php' );
	add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'XML Sitemap', 'XML Sitemap', 9, SEO_ADMIN_DIRECTORY.'/seo-xml-sitemap.php' );
	add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'Breadcrumbs', 'Breadcrumbs', 9, SEO_ADMIN_DIRECTORY.'/seo-breadcrumbs.php' );
	add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'RSS', 'RSS', 9, SEO_ADMIN_DIRECTORY.'/seo-rss.php' );
	//add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'Advanced', 'Advanced', 9, SEO_ADMIN_DIRECTORY.'/seo-advanced.php' );
	// add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'Import', 'Import', 9, SEO_ADMIN_DIRECTORY.'/seo-import-export.php' );
	
}
 add_action( 'admin_head', 'zeo_admin_header' );

/* Get option value function */

function zeo_get_value( $val, $postid = '' ) {
	if ( empty($postid) ) {
		global $post;
		if (isset($post))
			$postid = $post->ID;
		else 
			return false;
	}
	$custom = get_post_custom($postid);
	if (!empty($custom['_mervin_seo_'.$val][0]))
		return maybe_unserialize( $custom['_mervin_seo_'.$val][0] );
	else
		return false;
}

function get_zeo_options_arr() {
	$optarr = array('zeo','zeo_indexation', 'zeo_permalinks', 'mervin_verification', 'mervin_rss', 'mervin_breadcrumbs', 'mervin_sitemap', 'mervin_advanced');
	return apply_filters( 'mervin_options', $optarr );
}


function get_mervin_options() {
	$options = array();
	foreach( get_zeo_options_arr() as $opt ) {
		$options = array_merge( $options, (array) get_option($opt) );
	}
	return $options;
}

function zeo_get_terms($id, $taxonomy) {
	// If we're on a specific tag, category or taxonomy page, return that and bail.
	if ( is_category() || is_tag() || is_tax() ) {
		global $wp_query;
		$term = $wp_query->get_queried_object();
		return $term->name;
	}
	
	$output = '';
	$terms = get_the_terms($id, $taxonomy);
	if ( $terms ) {
		foreach ($terms as $term) {
			$output .= $term->name.', ';
		}
		return rtrim( trim($output), ',' );
	}
	return '';
}

function zeo_get_term_meta( $term, $taxonomy, $meta ) {
	if ( is_string( $term ) ) 
		$term = get_term_by('slug', $term, $taxonomy);

	if ( is_object( $term ) )
		$term = $term->term_id;
	
	$tax_meta = get_option( 'zeo_taxonomy_meta' );
	if ( isset($tax_meta[$taxonomy][$term]) )
		$tax_meta = $tax_meta[$taxonomy][$term];
	else
		return false;
	
	return (isset($tax_meta['zeo_'.$meta])) ? $tax_meta['zeo_'.$meta] : false;
}

/* RSS Feed footer  */

add_filter('the_content_feed', 'zeo_embed_rssfooter' );
		add_filter('the_excerpt_rss', 'zeo_embed_rssfooter_excerpt' );
		
function zeo_rss_replace_vars($temp) {
		global $post;
		
		$authorlink   = '<a rel="author" href="'.get_author_posts_url( $post->post_author ).'">'.get_the_author().'</a>';
		$postlink 	  = '<a href="'.get_permalink().'">'.get_the_title()."</a>";
		$bloglink 	  = '<a href="'.get_bloginfo('url').'">'.get_bloginfo('name').'</a>';
		$blogdesclink = '<a href="'.get_bloginfo('url').'">'.get_bloginfo('name').' - '.get_bloginfo('description').'</a>';

		$temp = stripslashes($temp);
		$temp = str_replace("%%AUTHORLINK%%", $authorlink, $temp);
		$temp = str_replace("%%POSTLINK%%", $postlink, $temp);
		$temp = str_replace("%%BLOGLINK%%", $bloglink, $temp);		
		$temp = str_replace("%%BLOGDESCLINK%%", $blogdesclink, $temp);					
		return $temp;
	}

	function zeo_embed_rssfooter($content) {
		if(is_feed()) {
			$options  = get_mervin_options();

			if ( isset($options['rss-header-content']) && !empty($options['rss-header-content']) ) {
				$content = "<p>" . zeo_rss_replace_vars($options['rss-header-content']) . "</p>" . $content;
			} 
			if ( isset($options['rss-footer-content']) && !empty($options['rss-footer-content']) ) {
				$content .= "<p>" . zeo_rss_replace_vars($options['rss-footer-content']). "</p>";
			} 
		}
		return $content;
	}

	function zeo_embed_rssfooter_excerpt($content) {
		if(is_feed()) {
			$options  = get_mervin_options();

			if ( isset($options['rss-header-content']) && !empty($options['rss-header-content']) ) {
				$content = "<p>".zeo_rss_replace_vars($options['rss-header-content']) . "</p><p>" . $content ."</p>";
			} 
			if ( isset($options['rss-footer-content']) && !empty($options['rss-footer-content']) ) {
				$content = "<p>".$content."</p><p>".zeo_rss_replace_vars($options['rss-footer-content'])."</p>";
			} 
		}
		return $content;
	}

/* RSS Feed footer  */




/* Google Plus Icon Script */
function zeo_admin_header(){
  echo '<script type="text/javascript">
window.___gcfg = {lang: "en"};
(function() 
{var po = document.createElement("script");
po.type = "text/javascript"; po.async = true;po.src = "https://apis.google.com/js/plusone.js";
var s = document.getElementsByTagName("script")[0];
s.parentNode.insertBefore(po, s);
})();</script>';
}
?>