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
	add_option('zeo_canonical_url', 'yes');
	add_option('zeo_nofollow', 'no');
	add_option('zeo_activate_title', 'yes');	
	
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
/* Rel No Follow */

if(zeo_ischeckeds('zeo_nofollow', 'yes' )){zeo_process_post();}
function zeo_process_post(){
add_filter( 'the_content', 'zeo_relnofollow' );
}

function zeo_relnofollow($content){
	return str_replace('<a href=', '<a rel="nofollow" href=',  $content);
}

/* General Function */

function zeo_ischeckeds($chkname,$value)
    {
                  
                if(get_option($chkname) == $value)
                {
                    return true;
                } 
        	return false;
	}



?>