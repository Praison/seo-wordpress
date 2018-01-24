<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>
<div class="container mt-3 float-left">
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

<?php
$update_authorshipoptions = (isset($_POST['update_authorshipoptions']) ? $_POST['update_authorshipoptions'] : null);
?>

<?php if ( $update_authorshipoptions == 'true' ) {  
	
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

	update_user_meta( $current_user->ID, 'zeoauthor', sanitize_text_field($_POST['zeoauthor'] ));
	update_user_meta( $current_user->ID, 'zeopreferredname', sanitize_text_field($_POST['zeopreferredname'] ));
	
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

  <?php if ( $update_analyticsoptions == 'true' ) {   
	
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
						    <input type="hidden" name="update_authorshipoptions" value="true" />
						        <div class="border border-secondary p-3 mb-4" id="support">
						        	<h3>Google Authorship Settings</h3>  
									
									<?php global $current_user;	wp_get_current_user();  ?>
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


				<?php include 'seo-sidebar.php';?>


				</div> <!-- End of Row -->
			</div> 

		</div>
</div>	<!-- End of Container -->

</div> 

<?php include 'seo-footer.php';?>
