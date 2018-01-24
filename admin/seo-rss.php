<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>
<div class="container mt-3 float-left">
    <h1>RSS Settings</h1>
</div>

<?php
$update_rss = (isset($_POST['update_rss']) ? $_POST['update_rss'] : null);
?>

<?php if ( $update_rss == 'true' ) { 

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
		$mervin_rss['rss-header-content']=stripslashes(wp_kses_post($_POST['rss-header-content']));
	}
	if(isset($_POST['rss-footer-content'])){
		$mervin_rss['rss-footer-content']=stripslashes(wp_kses_post($_POST['rss-footer-content']));
	}
	
	
	update_option('mervin_rss', $mervin_rss);
	
	echo '<div class="container float-left updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?>

<?php
$options = get_mervin_options();
?>
<div class="container float-left" >
	<div class="metabox-holder">	                
		<div class="meta-box-sortables ui-sortable">
        	<div class="row"> 
        		<div class="col-md-8">
					<form method="POST" action="">  
							
					<input type="hidden" name="update_rss" value="true" />        
					<div class="border border-secondary p-3 mb-4" id="support">
					        
						<h3>Overall Settings</h3>
						
						<div class="form-group">
							<label for="rsshead">Content Before Each Post</label>

							<textarea class="form-control" cols="50" rows="5" name="rss-header-content" id="rsshead" class="regular-text" ><?php echo esc_html($options['rss-header-content'])?></textarea> 
						</div> 
						<div class="form-group">

							<label for="rssfoot">Content After each Post</label>
							<textarea class="form-control" cols="50" rows="5" name="rss-footer-content" id="rssfoot" class="regular-text" ><?php echo esc_html($options['rss-footer-content'])?></textarea>             
										
							Note: HTML Allowed
						</div>
				       
				    </div>
					    
					   
					     <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p> 
					     <?php wp_nonce_field( 'seo_rss', 'seo_rss_nonce_field' ); ?>
					</form>

				</div> <!-- End of Column One -->

			<?php include 'seo-sidebar.php';?>

			</div> <!-- End of Row -->
		</div> 


</div> 
</div> <!-- End of Container -->

</div>

<?php include 'seo-footer.php';?>
