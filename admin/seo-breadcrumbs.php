<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>

<div class="container mt-3">
    <h1>Breadcrumbs Settings</h1>
</div>

<?php if ( $_POST['update_sitemapoptions'] == 'true' ) {   
	
	/*NONCE Verification*/
	
	if ( ! isset( $_POST['seo_breadcrumbs_nonce_field'] ) 
	    || ! wp_verify_nonce( $_POST['seo_breadcrumbs_nonce_field'], 'seo_breadcrumbs' ) 
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

	$mervin_breadcrumbs = array();
				
	
	$listtoupdate = array('breadcrumbs-enable', 'breadcrumbs-boldlast', 'trytheme', 'breadcrumbs-sep', 'breadcrumbs-home', 'breadcrumbs-selectedmenu', 'breadcrumbs-blog-remove', 'breadcrumbs-menus', 'breadcrumbs-archiveprefix', 'breadcrumbs-searchprefix', 'breadcrumbs-404crumb', 'breadcrumbs-prefix' );
	
	//  Validate and Sanitise POST Values

	foreach ($listtoupdate as $list){
	if(isset($_POST[$list])){
		$mervin_breadcrumbs[$list]=stripslashes(sanitize_text_field($_POST[$list]));
	}
	}
	
	if(isset($_POST['mervin_breadcrumbs'])){
	$mervin_breadcrumbs = array_merge( $mervin_breadcrumbs, stripslashes_deep($_POST['mervin_breadcrumbs']) );
	}
	
	update_option('mervin_breadcrumbs', $mervin_breadcrumbs);
	
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
					        
						<h3><span>Overall Settings</span></h3>
						
						<div class="form-group">
						<label for="enablebreadcrumbs">Enable Breadcrumbs</label>
						<input class="form-control" align="left" type="checkbox" name="breadcrumbs-enable" id="enablebreadcrumbs" value="yes"  <?php if(isset($options['breadcrumbs-enable'])){echo "checked";}?> />
						</div>
						<div class="form-group">  

						<label for="breadcrumbsep">Breadcrumbs Separator</label>
						<input class="form-control" size="54" type="text" name="breadcrumbs-sep" id="breadcrumbsep" class="regular-text" <?php if(isset($options['breadcrumbs-sep'])){ ?>
			                value="<?php echo esc_html($options['breadcrumbs-sep'])?>"
							<?php 	}?> /> 
						</div>
						<div class="form-group">                 
						
						<label for="breadcrumbhome">Home Page Anchor Text</label>
						<input class="form-control" size="54" type="text" name="breadcrumbs-home" id="breadcrumbhome" class="regular-text" <?php if(isset($options['breadcrumbs-home'])){ ?>
			                value="<?php echo esc_html($options['breadcrumbs-home'])?>"
							<?php 	}?> /> 

						</div>
						<div class="form-group">                 
						
						<label for="breadcrumbprefix">Breadcrumb Prefix</label>
						<input class="form-control" size="54" type="text" name="breadcrumbs-prefix" id="breadcrumbprefix" class="regular-text" <?php if(isset($options['breadcrumbs-prefix'])){ ?>
			                value="<?php echo esc_html($options['breadcrumbs-prefix'])?>"
							<?php 	}?> /> 
						</div>
						<div class="form-group">                 
						
						<label for="breadcrumbarchiveprefix">Archive Page Prefix</label>
						<input class="form-control" size="54" type="text" name="breadcrumbs-archiveprefix" id="breadcrumbarchiveprefix" class="regular-text" <?php if(isset($options['breadcrumbs-archiveprefix'])){ ?>
			                value="<?php echo esc_html($options['breadcrumbs-archiveprefix'])?>"
							<?php 	}?> /> 
						</div>
						<div class="form-group">                 
						
						<label for="breadcrumbsearchprefix">Search Page Prefix</label>
						<input class="form-control" size="54" type="text" name="breadcrumbs-searchprefix" id="breadcrumbsearchprefix" class="regular-text" <?php if(isset($options['breadcrumbs-searchprefix'])){ ?>
			                value="<?php echo esc_html($options['breadcrumbs-searchprefix'])?>"
							<?php 	}?> /> 
						</div>
						<div class="form-group">                 
						
						<label for="breadcrumb404prefix">404 Page Prefix</label>
						<input class="form-control" size="54" type="text" name="breadcrumbs-404crumb" id="breadcrumb404prefix" class="regular-text" <?php if(isset($options['breadcrumbs-404crumb'])){ ?>
			                value="<?php echo esc_html($options['breadcrumbs-404crumb'])?>"
							<?php 	}?> />  
						</div>
						<div class="form-group">                
						
						<label for="removeblogbreadcrumbs">Remove Breadcrumbs on Blog Pages</label>
						<input class="form-control" size="54" type="checkbox" name="breadcrumbs-blog-remove" id="removeblogbreadcrumbs" value="yes" <?php if(isset($options['breadcrumbs-blog-remove'])){echo "checked";}?> />    

						</div>
						<div class="form-group">              
						
						<label for="boldlastbreadcrumbs">Bold Last page in the Breadcrumb</label>
						<input class="form-control" size="54" type="checkbox" name="breadcrumbs-boldlast" id="boldlastbreadcrumbs" value="yes" <?php if(isset($options['breadcrumbs-boldlast'])){echo "checked";}?> />      

						</div>
						<div class="form-group">            
						
						<label for="trybreadcrumbs">Hybric, Thematic or Thesis Theme</label>
						<input class="form-control" size="54" type="checkbox" name="trytheme" id="trybreadcrumbs" value="yes" <?php if(isset($options['trytheme'])){echo "checked";}?> />

						</div>
								
					       
					</div>
					   
					    
					    <div class="border border-secondary p-3 mb-4" id="support">					    
					        <h3>Taxonomy to Show in Breadcrumbs</h3>
					       		
		                    <?php
							$content = '';
							/* Function for the Select Label*/
							
							function select($id, $label, $values, $option = '') {
								if ( $option == '') {
									$options = get_mervin_options();
									$option = !empty($option) ? $option : 'mervin_breadcrumbs';
								} else {
								if ( function_exists('is_network_admin') && is_network_admin() ) {
									$options = get_site_option($option);
								} else {
									$options = get_option($option);
								}
							}
					
								$output = '<div class="form-group"><label class="pr-3" for="'.$id.'">'.$label.':</label>';
								$output .= '<select name="'.$option.'['.$id.']" id="'.$id.'">';
					
									foreach($values as $value => $label) {
										$sel = '';
										if (isset($options[$id]) && $options[$id] == $value)
										$sel = 'selected="selected" ';

										if (!empty($label))
										$output .= '<option '.$sel.'value="'.$value.'">'.$label.'</option>';
										}
								$output .= '</select></div>';
								return $output . '';
							}
			
								/* Printing the Taxonomy to Show in Breadcrumbs option*/
			
							
							
			                foreach (get_post_types() as $pt) {
							if (in_array($pt, array('revision', 'attachment', 'nav_menu_item')))
								continue;

							$taxonomies = get_object_taxonomies($pt);
							if (count($taxonomies) > 0) {
								$values = array(0 => __('None','wordpress-seo') );
								foreach (get_object_taxonomies($pt) as $tax) {
									$taxobj = get_taxonomy($tax);
									$values[$tax] = $taxobj->labels->singular_name;
								}
								$ptobj = get_post_type_object($pt);
								$content .= select('post_types-'.$pt.'-maintax', $ptobj->labels->name, $values);					
							}
							}
							echo $content;
							?> 
					    </div>
					    
					    
					    <div class="border border-secondary p-3 mb-4" id="support">					    
					        <h3>Post type archive to show in breadcrumbs for:</h3>
			                <?php
								$content ='';
							foreach (get_taxonomies(array('public'=>true)) as $taxonomy) {
								if ( !in_array( $taxonomy, array('nav_menu','link_category','post_format', 'category', 'post_tag') ) ) {
								$tax = get_taxonomy($taxonomy);
								$values = array( '' => __( 'None', 'wordpress-seo' ) );
								if ( get_option('show_on_front') == 'page' )
									$values['post'] = __( 'Blog', 'wordpress-seo' );
								
								foreach (get_post_types( array('public' =>true) ) as $pt) {
									if (in_array($pt, array('revision', 'attachment', 'nav_menu_item')))
										continue;
									$ptobj = get_post_type_object($pt);
									if ($ptobj->has_archive)
										$values[$pt] = $ptobj->labels->name;
									}
									$content .= select('taxonomy-'.$taxonomy.'-ptparent', $tax->labels->singular_name, $values);					
								}
							} 
						      echo $content;    
							?>
					       <br />
					       <strong>Use this code to insert Breadcrumbs in your theme:</strong>
					              <br />
							<pre>&lt;?php if ( function_exists(&#x27;get_mervin_breadcrumbs&#x27;) ) {
								get_mervin_breadcrumbs(&#x27;&lt;p id=&quot;breadcrumbs&quot;&gt;&#x27;,&#x27;&lt;/p&gt;&#x27;);
							} ?&gt;</pre>		
					    
					    </div>

<<<<<<< HEAD

					     <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p> 
					     <?php wp_nonce_field( 'seo_breadcrumbs', 'seo_breadcrumbs_nonce_field' ); ?>
					</form>

					</div> <!-- End of Column One -->

					<div class="col-md-4" id="support">

					</div> <!-- End of Column Two -->
=======

					     <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p> 
					     <?php wp_nonce_field( 'seo_breadcrumbs', 'seo_breadcrumbs_nonce_field' ); ?>
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
>>>>>>> master

				</div> <!-- End of Row -->
			</div> 

<<<<<<< HEAD
		</div> 
	</div> <!-- End of Container -->
</div>
=======
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
>>>>>>> master
