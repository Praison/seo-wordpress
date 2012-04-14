<?php


function zeo_activate() {
	$default_title =  "| ".get_bloginfo('name');
	$default_home_title =  get_bloginfo('name')." | ".get_bloginfo('description');
	add_option('zeo_common_home_title', $default_home_title);
	add_option('zeo_common_frontpage_title', $default_title);
	add_option('zeo_common_page_title', $default_title);
	add_option('zeo_common_post_title', $default_title);
	add_option('zeo_common_category_title', $default_title);
	add_option('zeo_common_archive_title', $default_title);
	add_option('zeo_common_tag_title', $default_title);
	add_option('zeo_common_search_title', $default_title);
	add_option('zeo_common_error_title', $default_title);
	add_option('zeo_analytics_id', ''); 
	add_option('zeo_home_description', ''); 
	add_option('zeo_home_keywords', ''); 
}


function zeo_google_analytics(){
	
	echo "<script type='text/javascript'>

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '".get_option('zeo_analytics_id')."']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>";

	
}
if(get_option('zeo_analytics_id')!=NULL)
add_action('wp_footer', 'zeo_google_analytics');

add_action( 'admin_menu', 'zeo_options_menu' );
function zeo_options_menu(){
	
	add_options_page('Wordpress SEO Plugin' , 'Wordpress SEO', 9,  SEO_ADMIN_DIRECTORY.'/seo-optionspage.php');
	
}
add_action( 'admin_head', 'zeo_admin_header' );

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