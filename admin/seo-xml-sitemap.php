<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div class="wrap">
<h1>XML Sitemap Settings</h1>

<?php if ( $_POST['update_sitemapoptions'] == 'true' ) {  

	/*NONCE Verification*/ 
	
	if ( ! isset( $_POST['seo_xml_sitemap_nonce_field'] ) 
	    || ! wp_verify_nonce( $_POST['seo_xml_sitemap_nonce_field'], 'seo_xml_sitemap' ) 
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
	if ( !in_array( $post_type, array('revision','nav_menu_item','attachment') ) ) {
	foreach ($post_types as $post_type ) {					
		if(isset($_POST['post_types-'.$post_type.'-not_in_sitemap']))	
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
	
	echo '<div class="updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?>
  <?php if ( $_POST['update_analyticsoptions'] == 'true' ) { analyticsoptions_update(); }  

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
<div class="postbox-container" style="width:70%;">
				<div class="metabox-holder">	                
					<div class="meta-box-sortables ui-sortable">
                    
<form method="POST" action="">  
		
        <input type="hidden" name="update_sitemapoptions" value="true" />        
        <div class="postbox" id="support">
        <div class="handlediv" title="Click to toggle">
<br />
</div>
        <h3 class="hndle"><span>Overall Settings</span></h3>
        <div class="container">
<table cellpadding="6">
		<tr>
			<th align="left" style="font-weight:normal"><label for="enablesitemap">Enable XML Sitemap</label></th>

			<td>
				<input size="54" type="checkbox" name="enablexmlsitemap" id="enablesitemap" value="yes" class="regular-text" <?php if(isset($options['enablexmlsitemap'])){echo "checked";}?> />                
			</td>
		</tr>
        <tr>
			<th align="left" style="font-weight:normal"><label for="pingyahooid">Ping Yahoo!</label></th>

			<td>
				<input size="54" type="checkbox" name="xml_ping_yahoo" id="pingyahooid" value="yes" class="regular-text" <?php if(isset($options['xml_ping_yahoo'])){echo "checked";}?> />                
			</td>
		</tr>
		<tr>

			<th align="left" style="font-weight:normal"><label for="pingaskid">Ping Ask.com</label></th>
			<td>
				<input size="54" type="checkbox" name="xml_ping_ask" id="pingaskid" value="yes" class="regular-text" <?php if(isset($options['xml_ping_ask'])){echo "checked";}?> />
			</td>
		</tr>

	</table>
	<?php
	if ( $options['enablexmlsitemap'] )
	   echo '<p style="padding-left:10px;">'.sprintf(__('You can find your XML Sitemap here: %sXML Sitemap%s', 'seo-wordpress' ), '<a target="_blank" class="button-secondary" href="'.home_url($base.'sitemap_index.xml').'">', '</a>').'<br/><br/></p>';
			
	?>
    </div>    
    </div>
    
    <div class="postbox" id="support2">
    <div class="handlediv" title="Click to toggle"><br /></div>
        <h3 class="hndle">Post Types Disable</h3>
       		<div class="container">
				<table cellpadding="6">
 						<?php 							
							foreach (get_post_types() as $post_type) {
							if ( !in_array( $post_type, array('revision','nav_menu_item','attachment') ) ) {
							$pt = get_post_type_object($post_type);
					
						?>
					<tr>
						<th align="left" style="font-weight:normal"><label for="<?php echo 'post_types-'.$post_type.'-not_in_sitemap' ?>"><?php echo $pt->labels->name; ?></label></th>

					<td>
						<input size="54" type="checkbox" name="<?php echo 'post_types-'.$post_type.'-not_in_sitemap' ?>" id="<?php echo 'post_types-'.$post_type.'-not_in_sitemap' ?>" value="yes" class="regular-text" <?php if(isset($options['post_types-'.$post_type.'-not_in_sitemap'])){echo "checked";}?>/>                
					</td>
					</tr>
		
 						<?php					
							}
							}
						?>
				</table>
   			 </div>
    </div>
    
    
    <div class="postbox" id="support3">
    <div class="handlediv" title="Click to toggle"><br /></div>
        <h3 class="hndle">Taxonomy Disable</h3>
       		<div class="container">
				<table cellpadding="6">
 						<?php 
							
							foreach (get_taxonomies() as $taxonomy) {
								if ( !in_array( $taxonomy, array('nav_menu','link_category','post_format') ) ) {
									$tax = get_taxonomy($taxonomy);
										if ( isset( $tax->labels->name ) && trim($tax->labels->name) != '' ){
					
						?>
					<tr>
						<th align="left" style="font-weight:normal"><label for="<?php echo 'taxonomies-'.$taxonomy.'-not_in_sitemap' ?>"><?php echo $tax->labels->name; ?></label></th>

					<td>
						<input size="54" type="checkbox" name="<?php echo 'taxonomies-'.$taxonomy.'-not_in_sitemap' ?>" id="<?php echo 'taxonomies-'.$taxonomy.'-not_in_sitemap' ?>" value="yes" class="regular-text" <?php if(isset($options['taxonomies-'.$taxonomy.'-not_in_sitemap'])){echo "checked";}?>/>                
					</td>
					</tr>
		
 						<?php					
							}
							}
							}
						?>
				</table>
   			 </div>
    </div>
     <p><input type="submit" name="search" value="Update Options" class="button" /></p> 
     <?php wp_nonce_field( 'seo_xml_sitemap', 'seo_xml_sitemap_nonce_field' ); ?>
</form>






</div> 
</div>
</div>