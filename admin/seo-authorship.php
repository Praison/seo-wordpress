<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>
<div class="container mt-3">
    <h1>Google Authorship And Analytics Settings</h1>
</div>
<?php 

function zeo_ischecked($chkname,$value)
    {
                  
                if(get_option($chkname) == $value)
                {
                    return true;
                } 
        	return false;
	}



?>
<?php if ( $_POST['update_authorshipoptions'] == 'true' ) {  
	
	/*NONCE Verification*/
	
	if ( ! isset( $_POST['seo_authorship_nonce_field'] ) 
	    || ! wp_verify_nonce( $_POST['seo_authorship_nonce_field'], 'seo_authorship' ) 
	) {
	   print 'Sorry, your nonce did not verify.';
	   exit;
	} else {
		authorshipoptions_update(); 
	}

}  

function authorshipoptions_update(){
	
	// Only allowed user can edit

	global $current_user;

	if ( !current_user_can( 'edit_user', $current_user->ID ) )
		return false;

	// Validate POST Values

	if(!$_POST['zeoauthor'] ) {$_POST['zeoauthor'] = '';}
	if(!$_POST['zeopreferredname'] ) {$_POST['zeopreferredname'] = '';}

	// Sanitise POST values

	update_usermeta( $current_user->ID, 'zeoauthor', sanitize_text_field($_POST['zeoauthor'] ));
	update_usermeta( $current_user->ID, 'zeopreferredname', sanitize_text_field($_POST['zeopreferredname'] ));
	
	echo '<div class="updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?>
  <?php if ( $_POST['update_analyticsoptions'] == 'true' ) {   
	
	/*NONCE Verification*/
	
	if ( ! isset( $_POST['seo_analytics_nonce_field'] ) 
	    || ! wp_verify_nonce( $_POST['seo_analytics_nonce_field'], 'seo_analytics' ) 
	) {
	   print 'Sorry, your nonce did not verify.';
	   exit;
	} else {
		analyticsoptions_update(); 
	}
}  

function analyticsoptions_update(){
	
	// Only allowed user can edit

	global $current_user;

	if ( !current_user_can( 'edit_user', $current_user->ID ) )
		return false;

	$mervin_breadcrumbs = array();
	
	// Validating and Santising POST Values

	if(isset($_POST['verification-google'])){
		$mervin_verification['verification-google']=stripslashes(sanitize_text_field($_POST['verification-google']));
	}
	if(isset($_POST['verification-bing'])){
		$mervin_verification['verification-bing']=stripslashes(sanitize_text_field($_POST['verification-bing']));
	}
	if(isset($_POST['verification-alexa'])){
		$mervin_verification['verification-alexa']=stripslashes(sanitize_text_field($_POST['verification-alexa']));
	}
	
	if(!$_POST['zeo_analytics_id'] ) {$_POST['zeo_analytics_id'] = '';}
	
	update_option('mervin_verification', $mervin_verification);
	
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
<div class="container" >
		<div class="metabox-holder">	
			<div class="meta-box-sortables ui-sortable"> 
				<div class="row">
					<div class="col-md-8">
						<form method="POST" action="">  
						    <input type="hidden" name="update_authorshipoptions" value="true" />
						        <div class="border border-secondary p-3 mb-4" id="support">
						        	<h3>Google Authorship Settings</h3>  
									
									<?php global $current_user;	get_currentuserinfo();  ?>
									<div class="form-group">
										<label for="mpgpauthor">Google Plus Profile URL (Required)</label>
									
										<input class="form-control" size="54" type="text" name="zeoauthor" id="mpgpauthor" value="<?php echo esc_attr( get_the_author_meta( 'zeoauthor', $current_user->ID ) ); ?>" class="regular-text" />
							                <!--<br />
											<span class="description">Please enter your Google Plus Profile URL. (with "https://plus.google.com/1234567890987654321")</span>
							                -->
									</div>
									<div class="form-group">
										<label for="preferredname">Preferred Name</label></th>
										
										<input class="form-control" size="54" type="text" name="zeopreferredname" id="preferredname" value="<?php echo esc_attr( get_the_author_meta( 'zeopreferredname', $current_user->ID ) ); ?>" class="regular-text" />
							                <!--
							                <br />
											<span class="description">Enter Your Preferred Name</span>
							                -->
									</div>
						    	</div>
						     <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p>  
							<?php wp_nonce_field( 'seo_authorship', 'seo_authorship_nonce_field' ); ?>
						</form>
						<br />

						<form method="POST" action="">  
							<input type="hidden" name="update_analyticsoptions" value="true" />
							<div class="border border-secondary p-3 mb-4" id="support">
						        <h3 class="pb-3">Google Analytics Settings</h3>
						        <div class="form-group">
							        <label for="analyticsTrackingID">Please Enter your Analytics Tracking ID</label>
							        <input class="form-control" id="analyticsTrackingID" size="51" type="text" value="<?php echo esc_attr(get_option('zeo_analytics_id')); ?>" name="zeo_analytics_id"  />
						        </div>						        
						        
						        <h3 class="pb-3"><span>Webmaster Tools Verifications</span></h3>
						        <div class="form-group">
						        	<label for="mervingoogle">Google Webmaster Tools</label>

									<input class="form-control" size="54" type="text" name="verification-google" id="mervingoogle" class="regular-text" <?php if(isset($options['verification-google'])){ ?>
						                value="<?php echo esc_html($options['verification-google'])?>"
										<?php 	}?> />                
								</div>	
								<div class="form-group">	
									<label for="mervinbing">Bing Webmaster Tools</label>
									
									<input class="form-control" size="54" type="text" name="verification-bing" id="mervinbing" class="regular-text" <?php if(isset($options['verification-bing'])){ ?>
						                value="<?php echo esc_html($options['verification-bing'])?>"
										<?php 	}?> />                
								</div>	
								<div class="form-group">
									<label for="mervinalexa">Alexa Verification ID</label>

									<input class="form-control" size="54" type="text" name="verification-alexa" id="mervinalexa" class="regular-text" <?php if(isset($options['verification-alexa'])){ ?>
						                value="<?php echo esc_html($options['verification-alexa'])?>"
										<?php 	}?> />               
								</div>		
								
						    </div>  
						    
						    <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p>  
						    <?php wp_nonce_field( 'seo_analytics', 'seo_analytics_nonce_field' ); ?>
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
</div>	<!-- End of Container -->

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
