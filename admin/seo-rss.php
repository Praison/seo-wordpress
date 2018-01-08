<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div class="wrap">
<h1>RSS Settings</h1>

<?php if ( $_POST['update_rss'] == 'true' ) { 

	/*NONCE Verification*/ 
	
	if ( ! isset( $_POST['seo_rss_nonce_field'] ) 
	    || ! wp_verify_nonce( $_POST['seo_rss_nonce_field'], 'seo_rss' ) 
	) {
	   print 'Sorry, your nonce did not verify.';
	   exit;
	} else {
		rss_update(); 
	}
}  

function rss_update(){

	// Only allowed user can edit

	global $current_user;
	
	if ( !current_user_can( 'edit_user', $current_user->ID ) )
		return false;
	
	$mervin_rss = array();

	// Validate and Sanitise POST Values
	
	if(isset($_POST['rss-header-content'])){
		$mervin_rss['rss-header-content']=stripslashes(sanitize_textarea_field($_POST['rss-header-content']));
	}
	if(isset($_POST['rss-footer-content'])){
		$mervin_rss['rss-footer-content']=stripslashes(sanitize_textarea_field($_POST['rss-footer-content']));
	}
	
	
	update_option('mervin_rss', $mervin_rss);
	
	echo '<div class="updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?>

<?php
$options = get_mervin_options();
?>
<div class="postbox-container" style="width:70%;">
				<div class="metabox-holder">	                
					<div class="meta-box-sortables ui-sortable">
                    
<form method="POST" action="">  
		
        <input type="hidden" name="update_rss" value="true" />        
        <div class="postbox" id="support">
        <div class="handlediv" title="Click to toggle">
<br />
</div>
        <h3 class="hndle"><span>Overall Settings</span></h3>
        <div class="container">
<table cellpadding="6">
		<tr>
			<th align="right" style="font-weight:normal"><label for="rsshead">Content Before Each Post</label></th>

			<td>
				<textarea cols="50" rows="5" name="rss-header-content" id="rsshead" class="regular-text" ><?php echo esc_html($options['rss-header-content'])?></textarea>  
			</td>
		</tr>
       <tr>
			<th align="right" style="font-weight:normal"><label for="rssfoot">Content After each Post</label></th>

			<td>
				<textarea cols="50" rows="5" name="rss-footer-content" id="rssfoot" class="regular-text" ><?php echo esc_html($options['rss-footer-content'])?></textarea>             
			</td>
		</tr>
<tr>
<td>
Note: HTML Allowed
</td>
</tr>
	</table>
    </div>    
    </div>
    
   
     <p><input type="submit" name="search" value="Update Options" class="button" /></p> 
     <?php wp_nonce_field( 'seo_rss', 'seo_rss_nonce_field' ); ?>
</form>






</div> 
</div>
</div>