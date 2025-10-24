<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>
<div class="container mt-3 float-left">
    <h1>XML Sitemap Settings</h1>
</div>	

<?php
$update_sitemapoptions = isset($_POST['update_sitemapoptions']) ? sanitize_text_field( wp_unslash( $_POST['update_sitemapoptions'] ) ) : null;
?>

<?php if ( $update_sitemapoptions == 'true' ) {  

	/*NONCE Verification*/ 
	
	if ( ! isset( $_POST['seo_xml_sitemap_nonce_field'] ) 
	    || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['seo_xml_sitemap_nonce_field'] ) ), 'seo_xml_sitemap' ) 
	) {
	   print 'Sorry, your nonce did not verify.';
	   exit;
	} else {
		sitemapoptions_update(); 
	}
}  

function sitemapoptions_update(){

	// Only allowed user can edit

	global $current_user;
	
	if ( !current_user_can( 'edit_user', $current_user->ID ) )
		return false;
	
	$mervin_sitemap = array();

	$post_types=get_post_types('','names');
	if ( !in_array( $post_types, array('revision','nav_menu_item','attachment') ) ) {
	foreach ($post_types as $post_type ) {					
		if(isset($_POST['post_types-'.$post_type.'-not_in_sitemap']) && sanitize_text_field( wp_unslash( $_POST['post_types-'.$post_type.'-not_in_sitemap'] ) ))	
		$mervin_sitemap['post_types-'.$post_type.'-not_in_sitemap'] = 'yes';

		}
	}
	
	foreach (get_taxonomies() as $taxonomy) {
								if ( !in_array( $taxonomy, array('nav_menu','link_category','post_format') ) ) {
									$tax = get_taxonomy($taxonomy);
										if ( isset( $tax->labels->name ) && trim($tax->labels->name) != '' ){
											
											if(isset($_POST['taxonomies-'.$taxonomy.'-not_in_sitemap'])) {
											
												$mervin_sitemap['taxonomies-'.$taxonomy.'-not_in_sitemap'] = 'yes';
												
											}
											
										}
									}
								}
	if(isset($_POST['xml_ping_yahoo'])){
		$mervin_sitemap['xml_ping_yahoo']='yes';
	}
	if(isset($_POST['xml_ping_ask'])){
		$mervin_sitemap['xml_ping_ask']='yes';
	}
	if(isset($_POST['enablexmlsitemap'])){
		$mervin_sitemap['enablexmlsitemap']='yes';
	}
	
	update_option('mervin_sitemap', $mervin_sitemap);
	
	echo '<div class="container float-left updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?>

<?php
$update_analyticsoptions = (isset($_POST['update_analyticsoptions']) ? $_POST['update_analyticsoptions'] : null);
?>

  <?php if ( $update_analyticsoptions == 'true' ) { analyticsoptions_update(); }  

function analyticsoptions_update(){
	
	
	update_option('zeo_analytics_id', sanitize_text_field($_POST['zeo_analytics_id'])); 
	
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
<div class="container float-left">
	<div class="metabox-holder">	                
		<div class="meta-box-sortables ui-sortable">
        	<div class="row">
        		<div class="col-md-8">
					<form method="POST" action="">  
							
					<input type="hidden" name="update_sitemapoptions" value="true" />        
					<div class="border border-secondary p-3 mb-4" id="support">
						<div class="handlediv" title="Click to toggle"></div>
					
						<h3 >Overall Settings</h3>
						<div class="form-group">
							<label for="enablesitemap">Enable XML Sitemap</label>

							<input size="54" type="checkbox" name="enablexmlsitemap" id="enablesitemap" value="yes" class="regular-text" <?php if(isset($options['enablexmlsitemap'])){echo "checked";}?> />  
						</div>
						<div class="form-group">
							<label for="pingyahooid">Ping Yahoo!</label>

							<input size="54" type="checkbox" name="xml_ping_yahoo" id="pingyahooid" value="yes" class="regular-text" <?php if(isset($options['xml_ping_yahoo'])){echo "checked";}?> />   
						</div>
						<div class="form-group">             
							<label for="pingaskid">Ping Ask.com</label>

							<input size="54" type="checkbox" name="xml_ping_ask" id="pingaskid" value="yes" class="regular-text" <?php if(isset($options['xml_ping_ask'])){echo "checked";}?> />
						</div>
						


						<?php
						if ( isset($options['enablexmlsitemap']) )
						   echo '<div >'.sprintf(__('You can find your XML Sitemap here: %sXML Sitemap%s', 'seo-wordpress' ), '<a target="_blank" class="btn btn-primary" href="'.home_url('/sitemap_index.xml').'">', '</a>').'</div>';
								
						?>
				    </div> 
					    
				    <div class="border border-secondary p-3 mb-4" id="support">
				    	<div class="handlediv" title="Click to toggle"></div>
				        <h3>Post Types Disable</h3>
 						<?php 							
							foreach (get_post_types() as $post_type) {
							if ( !in_array( $post_type, array('revision','nav_menu_item','attachment') ) ) {
							$pt = get_post_type_object($post_type);
					
						?>
						<div class="form-group">

							<label for="<?php echo 'post_types-'.$post_type.'-not_in_sitemap' ?>"><?php echo $pt->labels->name; ?></label>

							<input size="54" type="checkbox" name="<?php echo 'post_types-'.$post_type.'-not_in_sitemap' ?>" id="<?php echo 'post_types-'.$post_type.'-not_in_sitemap' ?>" value="yes" class="regular-text" <?php if(isset($options['post_types-'.$post_type.'-not_in_sitemap'])){echo "checked";}?>/>                
						</div>
		
 						<?php					
							}
							}
						?>

				    </div>
					    
					<div class="border border-secondary p-3 mb-4" id="support">
					    <div class="handlediv" title="Click to toggle"></div>
				        <h3>Taxonomy Disable</h3>
						
 						<?php 
							
							foreach (get_taxonomies() as $taxonomy) {
								if ( !in_array( $taxonomy, array('nav_menu','link_category','post_format') ) ) {
									$tax = get_taxonomy($taxonomy);
										if ( isset( $tax->labels->name ) && trim($tax->labels->name) != '' ){
					
						?>
						<div class="form-group">
							<label for="<?php echo 'taxonomies-'.$taxonomy.'-not_in_sitemap' ?>"><?php echo $tax->labels->name; ?></label>

							<input size="54" type="checkbox" name="<?php echo 'taxonomies-'.$taxonomy.'-not_in_sitemap' ?>" id="<?php echo 'taxonomies-'.$taxonomy.'-not_in_sitemap' ?>" value="yes" class="regular-text" <?php if(isset($options['taxonomies-'.$taxonomy.'-not_in_sitemap'])){echo "checked";}?>/>                

						</div>
 						<?php					
							}
							}
							}
						?>
						
					   			 
					</div>
					     <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p> 
					     <?php wp_nonce_field( 'seo_xml_sitemap', 'seo_xml_sitemap_nonce_field' ); ?>
					</form>


				</div> <!-- End of Column One -->

			<?php include 'seo-sidebar.php';?>


			</div>
		</div> 
	</div>
</div>

<?php include 'seo-footer.php';?>
