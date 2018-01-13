<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>
<div class="container mt-3">
    <h1>XML Sitemap Settings</h1>
</div>	

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
<div class="container">
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
						if ( $options['enablexmlsitemap'] )
						   echo '<div >'.sprintf(__('You can find your XML Sitemap here: %sXML Sitemap%s', 'seo-wordpress' ), '<a target="_blank" class="btn btn-primary" href="'.home_url($base.'sitemap_index.xml').'">', '</a>').'</div>';
								
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

<!-- Support  Sidebar-->

	<div class="col-md-4" id="support">
    	<div class="border border-secondary p-3">
            <div class="row px-3">
                <h2>SEO Review</h2>
                <div class="clearfix"></div>
                <h3 class="pb-3">Praison SEO Support &nbsp;</h3>

                <div>
                    
                    <i class="fa fa-check fa-fw fa-lg" > </i> Meta Data Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Redirect Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> OnPage SEO Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Structured Data Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Speed Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Mobile Optimisation Check<br /> 
                    <i class="fa fa-check fa-fw fa-lg" > </i> Conversion Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Content Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Keyword Analysis <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Broken Pages Check <br />
                    <i class="fa fa-check fa-fw fa-lg" > </i> Monthly SEO Report<br />
                    <br />

                    Cost: £349 / Month <br /><br />

                    Note : Only Limited Space <br /><br />
                    Google Algorithm Changes regularly so we need a regular Analysis to be on top. 

                    <br /><br />

                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="FJGG89UCUYDQW">
                    <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online!">
                    <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
                    </form>


                </div>
            </div>
    	</div>
	</div>

<!-- End of Support -->


			</div>
		</div> 
	</div>
</div>

<div class="container pt-4">

    <!-- Button trigger modal -->
    <a class="float-right" data-toggle="modal" data-target="#exampleModalLong" href="#">
      Terms and Conditions
    </a>

</div>
<!-- Modal -->
<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">SEO Review Terms and Conditions</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h3>Praison SEO Support </h3>

        <p>
        <i class="fa fa-check fa-fw fa-lg" > </i> £349 / month is for a reasonable size website, probably around 100 page website; Website will be analysed and a report will be provided each month. <br /><br /> 

        <i class="fa fa-check fa-fw fa-lg" > </i> For Large websites and for the subscription of £349/month, 100 pages will be analysed based on your suggested pages. Remaining pages will be analysed based on additional budget. <br /><br />

        <i class="fa fa-check fa-fw fa-lg" > </i> From the report you will get a clear understanding of the status of your website, each month. From that you could take necessary action and be on top of the regular Google Algorithm updates. <br /><br />

        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
