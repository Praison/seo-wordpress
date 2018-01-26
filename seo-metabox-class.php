<?php

function call_seo_metabox_class() {

	return new seo_metabox_class();
	
}
if ( is_admin() ){
	add_action( 'load-post.php', 'call_seo_metabox_class' );
}


class seo_metabox_class {


	

public $zeo_uniqueid = array ('zeo_title','zeo_description','zeo_keywords', 'zeo_index'	);

public static $post_meta_fields = array (
			'default' => array(
				'zeo_title'=> array(
					'field' => 'zeo_title',
					'type'=> 'text',
					'label' => 'Title'
				),
				'zeo_description'=> array(
					'field' => 'zeo_description',
					'type'=> 'textarea',
					'label' => 'Description'
				),
				'zeo_keywords'=> array(
					'field' => 'zeo_keywords',
					'type'=> 'text',
					'label' => 'Keywords'
				), 
				'zeo_index'=> array(
					'field' => 'zeo_index',
					'type'=> 'radio',
					'label' => 'Index',
					'options' => array(
						'index, follow',
						'index, nofollow',
						'noindex, follow',
						'noindex, nofollow'
					)
				)
			)
		);

	public function __construct(){	
		
		add_action( 'add_meta_boxes', array( &$this, 'add_some_meta_box' ) );
		add_action( 'save_post', array( &$this, 'myplugin_save_postdata' ));


		
		
	}
		
	public function add_some_meta_box()
    {
		/*
		
        add_meta_box( 
             'some_meta_box_name'
            ,__( 'Wordpress SEO Plugin Settings')
            ,array( &$this, 'render_meta_box_content' )
            ,'post' 
            ,'advanced'
            ,'high'
        );
		add_meta_box( 
             'some_meta_box_name'
            ,__( 'Wordpress SEO Plugin Settings')
            ,array( &$this, 'render_meta_box_content' )
            ,'page' 
            ,'advanced'
            ,'high'
        );
		
		*/
		
		$post_types=get_post_types('','names');
		foreach (get_post_types() as $post_type ) {
			 
			//if(!in_array($post_type, get_option('zeo_post_types'))){ 
			
			add_meta_box( 
             'some_meta_box_name'
            ,__( 'Wordpress SEO Plugin Settings')
            ,array( &$this, 'render_meta_box_content' )
            ,$post_type 
            ,'advanced'
            ,'high'
        	);
			
			//}
		}
		
    }

	public function render_meta_box_content($post) 
    {
		
		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );
		$seo_data_class = new seo_data_class();

		$output_m_fields = '';

		foreach(self::$post_meta_fields as $post_meta_field){
			foreach($post_meta_field as $meta_field){
				$output_m_fields .= '<tr><td width="27%"><b>'.$meta_field['label'].'</b></td>';
				if($meta_field['type']=='text'){
					$output_m_fields .= '<td><input id="'.$meta_field['field'].'" type="text" size="82" name="'.$meta_field['field'].'" value="'.$seo_data_class->zeo_get_post_meta($meta_field['field']).'" ></input><span id="'.$meta_field['field'].'_count"></span></td>';
				}elseif($meta_field['type']=='textarea'){
					$output_m_fields .= '<td><textarea id="'.$meta_field['field'].'" name="'.$meta_field['field'].'" rows="4" cols="84" >'.$seo_data_class->zeo_get_post_meta($meta_field['field']).'</textarea><span id="'.$meta_field['field'].'_count"></span></td>';					
				}elseif($meta_field['type']=='radio'){
					$output_m_fields .= '<td>';
					foreach($meta_field['options'] as $radio_option){
						$output_m_fields .= '<input type="radio" name="'.$meta_field['field'].'" value="'.$radio_option.'"';

						if($radio_option==$seo_data_class->zeo_get_post_meta($meta_field['field'])) {
								$output_m_fields .=  ' checked';
							}

						$output_m_fields .= ' />'.$radio_option.' &nbsp;&nbsp;';
						
					}
					$output_m_fields .= '</td>';
				}

				$output_m_fields .= '<td><span id="'.$meta_field['field'].'_count"></span></td></tr>';
			}
		}
		
		echo '<form method="POST" action=""><table>'.$output_m_fields.'</table></form>';		
		
		
    }
	
	
	
	public function myplugin_save_postdata( $post_id ) {
		
 		 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
   	   return;
		
   		$myplugin_noncename = (isset($_POST['myplugin_noncename']) ? $_POST['myplugin_noncename'] : null);

 		 if ( !wp_verify_nonce( $myplugin_noncename, plugin_basename( __FILE__ ) ) )
    	  return;
	  
 		 if ( 'page' == $_POST['post_type'] ) 
		  {
 		   if ( !current_user_can( 'edit_page', $post_id ) )
     	   return;
 		 }
	 	 else
 		 {
 		   if ( !current_user_can( 'edit_post', $post_id ) )
     	   return;
 		 }

 	 // OK, we're authenticated: we need to find and save the data

 		foreach(self::$post_meta_fields as $post_meta_field){
			foreach($post_meta_field as $meta_field){
				
				$postuid = (isset($_POST[$meta_field['field']]) ? $_POST[$meta_field['field']] : null );

				$meta_data = sanitize_text_field($postuid);			
				$meta_key = $meta_field['field'];
				
				$seo_data_class = new seo_data_class();
				$checkvalue = $seo_data_class->zeo_get_post_meta($meta_key);
				
				if($meta_data!=NULL){
					if(isset($checkvalue)){
						$seo_data_class->zeo_update_post_meta($meta_key, $meta_data);
					}
					else{
						$seo_data_class->zeo_add_post_meta($meta_key, $meta_data);	
					}
				}
				else{
					$seo_data_class->zeo_delete_post_meta($meta_key, $meta_data);	
				
				}
			}
		}
 		
		
	}
	
	
	

	
	
}

class zeo_head_class {
	
	

public $zeo_uniqueid = array ('zeo_title','zeo_description','zeo_keywords', 'zeo_index'	);

	public function __construct(){	

	}
	
	public function zeo_get_url($query) {
		
			if ($query->is_404 || $query->is_search) {
				return false;
			}
			$haspost = count($query->posts) > 0;
			$has_ut = function_exists('user_trailingslashit');

			if (get_query_var('m')) {
				$m = preg_replace('/[^0-9]/', '', get_query_var('m'));
				switch (strlen($m)) {
					case 4: 
					$link = get_year_link($m);
					break;
            		case 6: 
                	$link = get_month_link(substr($m, 0, 4), substr($m, 4, 2));
                	break;
            		case 8: 
                	$link = get_day_link(substr($m, 0, 4), substr($m, 4, 2), substr($m, 6, 2));
	                break;
           			default:
           			return false;
				}
			} elseif (($query->is_single || $query->is_page) && $haspost) {
				$post = $query->posts[0];
				$link = get_permalink($post->ID);
     			$link = $this->zeo_get_paged($link); 
			} elseif (($query->is_single || $query->is_page) && $haspost) {
				$post = $query->posts[0];
				$link = get_permalink($post->ID);
     			$link = $this->zeo_get_paged($link);
		} elseif ($query->is_author && $haspost) {
   			global $wp_version;
      		if ($wp_version >= '2') {
        		$author = get_userdata(get_query_var('author'));
     			if ($author === false)
        			return false;
       			$link = get_author_link(false, $author->ID, $author->user_nicename);
   			} else {
        		global $cache_userdata;
	            $userid = get_query_var('author');
	            $link = get_author_link(false, $userid, $cache_userdata[$userid]->user_nicename);
      		}
  		} elseif ($query->is_category && $haspost) {
    		$link = get_category_link(get_query_var('cat'));
			$link = $this->zeo_get_paged($link);
		} else if ($query->is_tag  && $haspost) {
			$tag = get_term_by('slug',get_query_var('tag'),'post_tag');
       		if (!empty($tag->term_id)) {
				$link = get_tag_link($tag->term_id);
			} 
			$link = $this->zeo_get_paged($link);			
  		} elseif ($query->is_day && $haspost) {
  			$link = get_day_link(get_query_var('year'),
	                             get_query_var('monthnum'),
	                             get_query_var('day'));
	    } elseif ($query->is_month && $haspost) {
	        $link = get_month_link(get_query_var('year'),
	                               get_query_var('monthnum'));
	    } elseif ($query->is_year && $haspost) {
	        $link = get_year_link(get_query_var('year'));
		} elseif ($query->is_home) {
	        if ((get_option('show_on_front') == 'page') &&
	            ($pageid = get_option('page_for_posts'))) {
	            $link = get_permalink($pageid);
				$link = $this->zeo_get_paged($link);
				$link = trailingslashit($link);
			} else {
				if ( function_exists( 'icl_get_home_url' ) ) {
					$link = icl_get_home_url();
				} else {
					$link = get_option( 'home' );
				}
				$link = $this->zeo_get_paged($link);
				$link = trailingslashit($link);
			}
		} elseif ($query->is_tax && $haspost ) {
				$taxonomy = get_query_var( 'taxonomy' );
				$term = get_query_var( 'term' );
				$link = get_term_link( $term, $taxonomy );
				$link = $this->zeo_get_paged( $link );
	    } else {
	        return false;
	    }

		return $link;

	}  
	
	
	public function zeo_get_paged($link) {
			$page = get_query_var('paged');
	        if ($page && $page > 1) {
	            $link = trailingslashit($link) ."page/". "$page";
	            if ($has_ut) {
	                $link = user_trailingslashit($link, 'paged');
	            } else {
	                $link .= '/';
	            }
			}
			return $link;
	}
	public function zeo_ischeck_head($chkname,$value)
    {
                  
                if(get_option($chkname) == $value)
                {
                    return true;
                } 
        	return false;
	}
	

public function zeo_head(){
	if (is_feed()) {
			return;
		}
	$i=1;
	$options = get_mervin_options();
	$post_meta_fields = seo_metabox_class::$post_meta_fields;	
	echo "\n<!-- Wordpress SEO Plugin by Mervin Praison ( https://mer.vin/seo-wordpress/ ) --> \n";
	foreach($post_meta_fields as $post_meta_field){
	foreach($post_meta_field as $meta_field){
		$uid =$meta_field['field'];
		$seo_data_class = new seo_data_class();
		$checkvalue = $seo_data_class->zeo_get_post_meta($uid);			
			
		if (is_front_page()&& $i==1){
			if(get_option('zeo_home_description')!=NULL)echo "<meta name='description' content='".get_option('zeo_home_description')."'/> ";
			if(get_option('zeo_home_keywords')!=NULL)echo " <meta name='keywords' content='".get_option('zeo_home_keywords')."'/>";
			
			/*  Adding Google Bing and Alexa Verifications  */	
			
			if ( is_front_page() ) {
			if (!empty($options['verification-google'])) {
				$google_meta = $options['verification-google'];
				if ( strpos($google_meta, 'content') ) {
					preg_match('/content="([^"]+)"/', $google_meta, $match);
					$google_meta = $match[1];
				}
				echo "<meta name=\"google-site-verification\" content=\"$google_meta\" />\n";
			}
				
			if (!empty($options['verification-bing'])) {
				$bing_meta = $options['verification-bing'];
				if ( strpos($bing_meta, 'content') ) {
					preg_match('/content="([^"]+)"/', $bing_meta, $match);
					$bing_meta = $match[1];
				}								
				echo "<meta name=\"msvalidate.01\" content=\"$bing_meta\" />\n";
			}
			
			if (!empty($options['verification-alexa'])) {
				echo "<meta name=\"alexaVerifyID\" content=\"".esc_attr($options['verification-alexa'])."\" />\n";
			}	
		}
			
			/*  Adding Google Bing and Alexa Verifications  */
			$i=2;
		}
		elseif(is_home() && $i==1){
			if(get_option('zeo_blog_description')!=NULL)echo "<meta name='description' content='".get_option('zeo_blog_description')."'/> ";
			if(get_option('zeo_blog_keywords')!=NULL)echo " <meta name='keywords' content='".get_option('zeo_blog_keywords')."'/>";
			$i=2;
		}
		elseif($checkvalue!=NULL && $i==1){
			if($uid=='zeo_description')echo "<meta name='description' content='".$seo_data_class->zeo_get_post_meta($uid)."'/>";
			if($uid=='zeo_keywords')echo " <meta name='keywords' content='".$seo_data_class->zeo_get_post_meta($uid)."'/>";
			if($uid=='zeo_index' && !is_front_page())echo " <meta name='robots' content='".$seo_data_class->zeo_get_post_meta($uid)."'/>";
			
		}
				
	}
	}


	global $wp_query;
	$url = $this->zeo_get_url($wp_query);
	if(get_option('zeo_canonical_url')!=NULL && get_option('zeo_canonical_url')=='yes'&& $url!=NULL )echo "<link rel='canonical' href='".$url."' />";
	if(is_category()&& $this->zeo_ischeck_head('zeo_category_nofollow', 'yes' ))echo ' <meta name="robots" content="noindex,follow" />';
	if(is_tag()&& $this->zeo_ischeck_head('zeo_tag_nofollow', 'yes' ))echo ' <meta name="robots" content="noindex,follow" />';
	if(is_date()&& $this->zeo_ischeck_head('zeo_date_nofollow', 'yes' ))echo ' <meta name="robots" content="noindex,follow" />';
	echo "\n<!-- End of Wordpress SEO Plugin by Mervin Praison --> \n";	
	}	
	
		

}


?>