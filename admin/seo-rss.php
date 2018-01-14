<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>
<div class="container mt-3">
    <h1>RSS Settings</h1>
</div>

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
		$mervin_rss['rss-header-content']=stripslashes(wp_kses_post($_POST['rss-header-content']));
	}
	if(isset($_POST['rss-footer-content'])){
		$mervin_rss['rss-footer-content']=stripslashes(wp_kses_post($_POST['rss-footer-content']));
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
<div class="container" >
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

			</div> <!-- End of Row -->
		</div> 


</div> 
</div> <!-- End of Container -->

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
