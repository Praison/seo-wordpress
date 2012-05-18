<?php

function call_seo_metabox_class() {

	return new seo_metabox_class();
	
}
if ( is_admin() ){
add_action( 'load-post.php', 'call_seo_metabox_class' );
}


class seo_metabox_class {
	

public $zeo_uniqueid = array ('zeo_title','zeo_description','zeo_keywords', 'zeo_index'	);

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
		
		$i=0;
		
		foreach ($this->zeo_uniqueid as $uid){
			if($i==0)$mytitle=$uid;
			if($i==1)$mydescription=$uid;
			if($i==2)$mykeywords=$uid;
			if($i==3)$myindex=$uid;
			$i+=1;
		}
		
		$titlevalue = $seo_data_class->zeo_get_post_meta($mytitle);
		$descriptionvalue = $seo_data_class->zeo_get_post_meta($mydescription);
		$keywordsvalue = $seo_data_class->zeo_get_post_meta($mykeywords);
		$indexvalue = $seo_data_class->zeo_get_post_meta($myindex);
		
		
        echo '<table>
		<tr>
		<td width="30%"><b>Title</b></td>
		<td><form method="POST" action=""> <input type="text" size="82" name="zeo_title" value="';
		echo $titlevalue;

		echo '" ></input>
		</td>
		
		</tr>
		<tr>
		<td><b>Description</b></td>
		<td>
		
		<textarea name="zeo_description" rows="2" cols="84" >';
		echo $descriptionvalue;
		echo '</textarea>
		
		</td>
		
		</tr>
		<tr>
		<td><b>Keywords</b></td>
		<td><input type="text" size="82" name="zeo_keywords" value="';
		echo $keywordsvalue;
		
		echo '" ></input></form></td>';
		?>
		</tr>
		<tr>
		<td><b>Index, Follow Settings</b></td>
		<td><input type="radio" name="zeo_index" value="index, follow" <?php if($indexvalue=='index, follow')echo 'checked'; ?> />Index, Follow &nbsp;&nbsp;
		<input type="radio" name="zeo_index" value="index, nofollow" <?php if($indexvalue=='index, nofollow')echo 'checked'; ?> /> Index, NoFollow &nbsp;&nbsp;
        <input type="radio" name="zeo_index" value="noindex, follow" <?php if($indexvalue=='noindex, follow')echo 'checked'; ?> /> NoIndex, Follow &nbsp;&nbsp;
		<input type="radio" name="zeo_index" value="noindex, nofollow" <?php if($indexvalue=='noindex, nofollow')echo 'checked'; ?> /> NoIndex, NoFollow &nbsp;
		</form></td>
		
		</tr>
		
		<?php
		echo '</table>';
    }
	
	
	
	public function myplugin_save_postdata( $post_id ) {
		
 		 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
   	   return;
	
 		 if ( !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
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

 		foreach ($this->zeo_uniqueid as $uid){			
		
		$mytitle = $_POST[$uid];			
		$uniqueid = $uid;
		
		
		$seo_data_class = new seo_data_class();
		$checkvalue = $seo_data_class->zeo_get_post_meta($uniqueid);
		
		
		if($mytitle!=NULL){
		if(isset($checkvalue)){
			$seo_data_class->zeo_update_post_meta($uniqueid, $mytitle);
		}
		else{
			$seo_data_class->zeo_add_post_meta($uniqueid, $mytitle);	
			
		}
		}
		else{
		$seo_data_class->zeo_delete_post_meta($uniqueid, $mytitle);	
		
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
	echo "\n<!-- Wordpress SEO Plugin by Mervin Praison ( http://mervin.info/seo-wordpress/ ) --> \n";
	foreach ($this->zeo_uniqueid as $uid){
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
		elseif($checkvalue!=NULL){
			if($uid=='zeo_description' && get_option('zeo_home_description')==NULL )echo "<meta name='description' content='".$seo_data_class->zeo_get_post_meta($uid)."'/> ";
			if($uid=='zeo_keywords' && get_option('zeo_home_keywords')==NULL)echo " <meta name='keywords' content='".$seo_data_class->zeo_get_post_meta($uid)."'/>";
			if($uid=='zeo_index' && !is_front_page())echo " <meta name='robots' content='".$seo_data_class->zeo_get_post_meta($uid)."'/>";
			
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