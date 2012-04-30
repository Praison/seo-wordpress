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
	// add_submenu_page( SEO_ADMIN_DIRECTORY.'/seo-dashboard.php', 'Import', 'Import', 9, SEO_ADMIN_DIRECTORY.'/seo-import-export.php' );
	
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