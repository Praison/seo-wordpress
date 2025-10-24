<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div>
<div class="container mt-3 float-left">
    <h1>Breadcrumbs Settings</h1>
</div>

<?php
$update_sitemapoptions = isset($_POST['update_sitemapoptions']) ? sanitize_text_field( wp_unslash( $_POST['update_sitemapoptions'] ) ) : null;
?>

<?php if ( $update_sitemapoptions == 'true' ) {   
	
	/*NONCE Verification*/
	
	if ( ! isset( $_POST['seo_breadcrumbs_nonce_field'] ) 
	    || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['seo_breadcrumbs_nonce_field'] ) ), 'seo_breadcrumbs' ) 
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
		$mervin_breadcrumbs[$list]=sanitize_text_field( wp_unslash( $_POST[$list] ) );
	}
	}
	
	if(isset($_POST['mervin_breadcrumbs'])){
	$mervin_breadcrumbs = array_merge( $mervin_breadcrumbs, array_map( 'sanitize_text_field', wp_unslash( $_POST['mervin_breadcrumbs'] ) ) );
	}
	
	update_option('mervin_breadcrumbs', $mervin_breadcrumbs);
	
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
<div class="container float-left">
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
								$values = array(0 => __('None','seo-wordpress') );
								foreach (get_object_taxonomies($pt) as $tax) {
									$taxobj = get_taxonomy($tax);
									$values[$tax] = $taxobj->labels->singular_name;
								}
								$ptobj = get_post_type_object($pt);
								$content .= select('post_types-'.$pt.'-maintax', $ptobj->labels->name, $values);					
							}
							}
							echo wp_kses_post( $content );
							?> 
					    </div>
					    
					    
					    <div class="border border-secondary p-3 mb-4" id="support">					    
					        <h3>Post type archive to show in breadcrumbs for:</h3>
			                <?php
								$content ='';
							foreach (get_taxonomies(array('public'=>true)) as $taxonomy) {
								if ( !in_array( $taxonomy, array('nav_menu','link_category','post_format', 'category', 'post_tag') ) ) {
								$tax = get_taxonomy($taxonomy);
								$values = array( '' => __( 'None', 'seo-wordpress' ) );
								if ( get_option('show_on_front') == 'page' )
									$values['post'] = __( 'Blog', 'seo-wordpress' );
								
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
						      echo wp_kses_post( $content );    
							?>
					       <br />
					       <strong>Use this code to insert Breadcrumbs in your theme:</strong>
					              <br />
							<pre>&lt;?php if ( function_exists(&#x27;get_mervin_breadcrumbs&#x27;) ) {
								get_mervin_breadcrumbs(&#x27;&lt;p id=&quot;breadcrumbs&quot;&gt;&#x27;,&#x27;&lt;/p&gt;&#x27;);
							} ?&gt;</pre>		
					    
					    </div>

					     <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p> 
					     <?php wp_nonce_field( 'seo_breadcrumbs', 'seo_breadcrumbs_nonce_field' ); ?>
					</form>

					</div> <!-- End of Column One -->

				<?php include 'seo-sidebar.php';?>

				</div> <!-- End of Row -->
			
	</div> <!-- End of Container -->
</div>

<?php include 'seo-footer.php';?>
