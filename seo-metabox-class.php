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
		<td width="40%"><b>Title</b></td>
		<td><form method="POST" action=""> <input type="text" size="80" name="zeo_title" value="';
		echo $titlevalue;

		echo '" ></input>
		</td>
		
		</tr>
		<tr>
		<td><b>Description</b></td>
		<td>
		
		<textarea name="zeo_description" rows="2" cols="82" >';
		echo $descriptionvalue;
		echo '</textarea>
		
		</td>
		
		</tr>
		<tr>
		<td><b>Keywords</b></td>
		<td><input type="text" size="80" name="zeo_keywords" value="';
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
	
	public function zeo_head(){
	$i=1;
	echo "\n<!-- Wordpress SEO Plugin by Mervin Praison ( http://mervin.info/seo-wordpress/ ) --> \n";
	foreach ($this->zeo_uniqueid as $uid){
	$seo_data_class = new seo_data_class();
	$checkvalue = $seo_data_class->zeo_get_post_meta($uid);	
	
		
		if (is_front_page()&& $i==1){
			if(get_option('zeo_home_description')!=NULL)echo "<meta name='description' content='".get_option('zeo_home_description')."'/> ";
			if(get_option('zeo_home_keywords')!=NULL)echo "<meta name='keywords' content='".get_option('zeo_home_keywords')."'/>";
			$i=2;
		}
		elseif($checkvalue!=NULL){
			if($uid=='zeo_description')echo "<meta name='description' content='".$seo_data_class->zeo_get_post_meta($uid)."'/> ";
			if($uid=='zeo_keywords')echo "<meta name='keywords' content='".$seo_data_class->zeo_get_post_meta($uid)."'/>";
			if($uid=='zeo_index')echo "<meta name='robots' content='".$seo_data_class->zeo_get_post_meta($uid)."'/>";
			
		}
				
	}
	if(get_option('zeo_canonical_url')!=NULL && get_option('zeo_canonical_url')=='yes')echo "<link rel='canonical' href='".get_permalink()."' />";
	echo "\n<!-- End of Wordpress SEO Plugin by Mervin Praison --> \n";	
	}	

	
	
}


?>