<?php

function call_seo_deprecated_class() {	
	return new seo_deprecated();		
}

if ( is_admin() ){
	$transient = 'seo_deprecated_check';
	if ( !get_transient( $transient ) ) {
		// check if the function was not executed.
		add_action( 'admin_menu', 'call_seo_deprecated_class' );
		set_transient( $transient, 'TRUE' );
	}
}


class seo_deprecated{

	function __construct() {
		add_action('admin_init', array( &$this, 'front_home_page' ));
	}

	function front_home_page(){

		$frontpage_id = get_option( 'page_on_front' );
		$blog_id = get_option( 'page_for_posts' );

		if($frontpage_id){
			update_post_meta( $frontpage_id, 'zeo_description', get_option('zeo_home_description') );
			update_post_meta( $frontpage_id, 'zeo_keywords', get_option('zeo_home_keywords') );
		}
		if($blog_id){
			update_post_meta( $blog_id, 'zeo_description', get_option('zeo_blog_description') );
			update_post_meta( $blog_id, 'zeo_keywords', get_option('zeo_blog_keywords') );

		}
		
	}

}

?>