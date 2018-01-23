<div class="pr-3">
<div class="container mt-4 float-left">
    <h1 >Wordpress SEO Plugin Settings</h1>
</div>
<?php 

function zeo_ischecked($chkname,$value)
    {
                  
                if(esc_html(get_option($chkname)) == $value)
                {
                    return true;
                } 
        	return false;
	}

if ( $_POST['update_zeooptions'] == 'true' ) {     
    
    /*NONCE Verification*/

    if ( ! isset( $_POST['seo_dashboard_nonce_field'] ) 
        || ! wp_verify_nonce( $_POST['seo_dashboard_nonce_field'], 'seo_dashboard' ) 
    ) {
       print 'Sorry, your nonce did not verify.';
       exit;
    } else {
        zeooptions_update(); 
    }
}  

function zeooptions_update(){

    // Only allowed user can edit
    
    global $current_user;

    if ( !current_user_can( 'edit_user', $current_user->ID ) )
        return false;
	
	//Validating POST Values

	
    if(!$_POST['zeo_common_home_title'] ) {$_POST['zeo_common_home_title'] = '';}
    if(!$_POST['zeo_home_description'] ) {$_POST['zeo_home_description'] = '';}
    if(!$_POST['zeo_home_keywords'] ) {$_POST['zeo_home_keywords'] = '';}
    if(!$_POST['zeo_blog_description'] ) {$_POST['zeo_blog_description'] = '';}
    if(!$_POST['zeo_blog_keywords'] ) {$_POST['zeo_blog_keywords'] = '';}
    if(!$_POST['zeo_common_frontpage_title'] ) {$_POST['zeo_common_frontpage_title'] = '';}
    if(!$_POST['zeo_common_page_title'] ) {$_POST['zeo_common_page_title'] = '';}
    if(!$_POST['zeo_common_post_title'] ) {$_POST['zeo_common_post_title'] = '';}
    if(!$_POST['zeo_common_category_title'] ) {$_POST['zeo_common_category_title'] = '';}
    if(!$_POST['zeo_common_archive_title'] ) {$_POST['zeo_common_archive_title'] = '';}
    if(!$_POST['zeo_common_tag_title'] ) {$_POST['zeo_common_tag_title'] = '';}
    if(!$_POST['zeo_common_search_title'] ) {$_POST['zeo_common_search_title'] = '';}
    if(!$_POST['zeo_common_error_title'] ) {$_POST['zeo_common_error_title'] = '';}
    if(!$_POST['zeo_canonical_url'] ) {$_POST['zeo_canonical_url'] = '';}
    if(!$_POST['zeo_nofollow'] ) {$_POST['zeo_nofollow'] = '';}
    if(!$_POST['zeo_activate_title'] ) {$_POST['zeo_activate_title'] = '';}
    if(!$_POST['zeo_category_nofollow'] ) {$_POST['zeo_category_nofollow'] = '';}
    if(!$_POST['zeo_tag_nofollow'] ) {$_POST['zeo_tag_nofollow'] = '';}
    if(!$_POST['zeo_date_nofollow'] ) {$_POST['zeo_date_nofollow'] = '';}
    if(!$_POST['zeo_post_types'] ) {$_POST['zeo_post_types'] = '';}

	//Sanitising the POST values	
	
	update_option('zeo_common_home_title', sanitize_text_field($_POST['zeo_common_home_title']));
	update_option('zeo_home_description', sanitize_textarea_field($_POST['zeo_home_description']));
	update_option('zeo_home_keywords', sanitize_text_field($_POST['zeo_home_keywords'])); 
	update_option('zeo_blog_description', sanitize_textarea_field($_POST['zeo_blog_description']));
	update_option('zeo_blog_keywords', sanitize_text_field($_POST['zeo_blog_keywords'])); 
	update_option('zeo_common_frontpage_title', sanitize_text_field($_POST['zeo_common_frontpage_title']));
	update_option('zeo_common_page_title', sanitize_text_field($_POST['zeo_common_page_title'])); 
	update_option('zeo_common_post_title', sanitize_text_field($_POST['zeo_common_post_title'])); 
	update_option('zeo_common_category_title', sanitize_text_field($_POST['zeo_common_category_title'])); 
	update_option('zeo_common_archive_title', sanitize_text_field($_POST['zeo_common_archive_title'])); 
	update_option('zeo_common_tag_title', sanitize_text_field($_POST['zeo_common_tag_title'])); 
	update_option('zeo_common_search_title', sanitize_text_field($_POST['zeo_common_search_title'])); 
	update_option('zeo_common_error_title', sanitize_text_field($_POST['zeo_common_error_title']));
	update_option('zeo_canonical_url', sanitize_text_field($_POST['zeo_canonical_url']));
	update_option('zeo_nofollow', sanitize_text_field($_POST['zeo_nofollow']));
	update_option('zeo_activate_title', sanitize_text_field($_POST['zeo_activate_title']));	
	update_option('zeo_category_nofollow', sanitize_text_field($_POST['zeo_category_nofollow']));
	update_option('zeo_tag_nofollow', sanitize_text_field($_POST['zeo_tag_nofollow']));
	update_option('zeo_date_nofollow', sanitize_text_field($_POST['zeo_date_nofollow']));
	update_option('zeo_post_types', sanitize_text_field($_POST['zeo_post_types']));
	
	echo '<div class="container float-left updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}

?>
<div class="container float-left" >

				<div class="metabox-holder">	
					<div class="meta-box-sortables ui-sortable">
<div class="row">

<!-- ESCAPING Values while displaying Data -->
	<div class="col-md-8">
                    
		<form method="POST" action="">  
            <input type="hidden" name="update_zeooptions" value="true" />  
                <div class="border border-secondary p-3 mb-4" id="support">
            
    	            <h2>Home Page Settings</h2>
                    
                    <div class="form-group">
        				<label for="homePageTitle">Home Page Title:</label>
        				
                    	<input id="homePageTitle" class="form-control" size="55" type="text" value="<?php echo esc_html(get_option('zeo_common_home_title')); ?>" name="zeo_common_home_title"  />  
                	</div>
                    <div class="form-group">
        				<label for="homePageMetaDescription">Home Page  Meta Description:</label>
        				
                    	<textarea id="homePageMetaDescription" class="form-control" size="50" rows="3" cols="52" name="zeo_home_description" ><?php echo esc_html(get_option('zeo_home_description')); ?></textarea>  
                	</div>
                    <div class="form-group">
        				<label for="homePageMetaKeywords">Home Page  Meta Keywords:</label>
        				
                    	<input id="homePageMetaKeywords" class="form-control" size="55" type="text" value="<?php echo esc_html(get_option('zeo_home_keywords')); ?>" name="zeo_home_keywords"  />  
                	</div>
                    <div class="form-group">
        				<label for="blogPageMetaDescription">Blog Page  Meta Description (if exists):</label>
        				
                    	<textarea id="blogPageMetaDescription" class="form-control" size="50" rows="3" cols="52" name="zeo_blog_description" ><?php echo esc_html(get_option('zeo_blog_description')); ?></textarea>  
                	</div>
                    <div class="form-group">
        				<label for="blogPageMetaKeywords">Blog Page  Meta Keywords (if exists):</label>
        				
                    	<input id="blogPageMetaKeywords" class="form-control" size="55" type="text" value="<?php echo esc_html(get_option('zeo_blog_keywords')); ?>" name="zeo_blog_keywords"  />  
            	    </div>
                
                </div>
                
                <div class="border border-secondary p-3 mb-4" id="support">  
                
                    <h2>Other Page Title Settings</h2>
                    
    				<h3>Title Suffix</h3> 
                    
                	<div class="form-group">
        				<label for="blogPageTitle">Blog Page Title:</label>
        				
                    	<input id="blogPageTitle" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_frontpage_title')); ?>" name="zeo_common_frontpage_title"  />  
                	</div>
                    <div class="form-group">
        				<label for="pageTitle">Page Title:</label>
        				
                    	<input id="pageTitle" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_page_title')); ?>" name="zeo_common_page_title"  />  
                	</div>
                    <div class="form-group">
        				<label for="postTitle">Post Title:</label>
        				
                    	<input id="postTitle" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_post_title')); ?>" name="zeo_common_post_title"  />  
                	</div>
                    <div class="form-group">
        				<label for="categoryTitle">Category Title:</label>
        				
                    	<input id="categoryTitle" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_category_title')); ?>" name="zeo_common_category_title"  />  
                	</div>
                    <div class="form-group">
        				<label for="archiveTitle">Archive Title:</label>
        				
                    	<input id="archiveTitle" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_archive_title')); ?>" name="zeo_common_archive_title"  />  
                	</div>
                    <div class="form-group">                   
                    
        				<label for="tagTitle">Tag Title:</label>
        				
                    	<input id="tagTitle" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_tag_title')); ?>" name="zeo_common_tag_title"  />  
                	</div>
                    <div class="form-group">
        				<label for="searchTitle">Search Title:</label>
        				
                    	<input id="searchTitle" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_search_title')); ?>" name="zeo_common_search_title"  />  
                	</div>
                    <div class="form-group">
        				<label for="404Title">404 Page Title:</label>
        				
                    	<input id="404Title" class="form-control" size="50" type="text" value="<?php echo esc_html(get_option('zeo_common_error_title')); ?>" name="zeo_common_error_title"  />  
            	    </div>

                    <h3>Title Prefix</h3>
                    Note : Title Prefix is your actual Page title
                
                </div>

                <div class="border border-secondary p-3 mb-4" id="support">
                    
                    <h2>General Settings</h2>
                    
            		<h3>Functions Setup</h3>
            		<div class="form-group">
        				<label class="form-check-label pr-3" for="activateOtherPageTitleSettings">Activate Other Page Title settings: </label>
        				
                    	<input id="activateOtherPageTitleSettings" class="form-check-input" type="checkbox" name="zeo_activate_title" value="yes" <?php if(zeo_ischecked('zeo_activate_title', 'yes' )){echo "checked";}?>>  </input>
                	</div>
                    <div class="form-group">
        				<label class="form-check-label pr-3" for="canonicalLink">Canonical Link: </label>
        				
                    	<input id="canonicalLink" class="form-check-input" type="checkbox" name="zeo_canonical_url" value="yes" <?php if(zeo_ischecked('zeo_canonical_url', 'yes' )){echo "checked";}?>>  </input>
                	</div>
                    <div class="form-group">
        				<label class="form-check-label pr-3" for="categoryNoFollow">Category No Follow: </label>
        				
                    	<input id="categoryNoFollow" class="form-check-input" type="checkbox" name="zeo_category_nofollow" value="yes" <?php if(zeo_ischecked('zeo_category_nofollow', 'yes' )){echo "checked";}?>> </input>
                	</div>
                    <div class="form-group">
        				<label class="form-check-label pr-3" for="tagNoFollow">Tag No Follow: </label>
        				
                    	<input id="tagNoFollow" class="form-check-input" type="checkbox" name="zeo_tag_nofollow" value="yes" <?php if(zeo_ischecked('zeo_tag_nofollow', 'yes' )){echo "checked";}?>> </input>
                	</div>
                    <div class="form-group">
                              
                    				<label class="form-check-label pr-3" for="dateBasedPageNoFollow">Date Based Page No Follow: </label>
                			
                            
                            	<input id="dateBasedPageNoFollow" class="form-check-input" type="checkbox" name="zeo_date_nofollow" value="yes" <?php if(zeo_ischecked('zeo_date_nofollow', 'yes' )){echo "checked";}?>> </input>
                            
                        
                	</div>
                    <!--
                    <tr><td>
    				rel = NoFollow for Outbound Links: 
    				</td><td>
                	<input type="checkbox" name="zeo_nofollow" value="yes" <?php if(zeo_ischecked('zeo_nofollow', 'yes' )){echo "checked";}?>>  </input>
                	</td></tr>              
                    -->
                    </table>
            	</div>
                
               <!-- 
                <div class="postbox" id="support">
                <table cellpadding="6">
                <h3>Custom Posts Meta Box (Advanced Users)</h3>
                <tr><td><br />
                <b>Disable SEO Setting Options on the Following Pages</b>
                </td></tr>
                <tr><td>

					<select name='zeo_post_types[]' size=5 width='300px' style="width: 300px" multiple>
                    <option value="" <?php if(in_array('', esc_html(get_option('zeo_post_types')))){ echo 'selected';} ?> > Select None</option>
                <?php
					$post_types=get_post_types('','names');
					foreach ($post_types as $post_type ) {
					
				?>
                        
					<option value="<?php echo $post_type; ?>" <?php if(in_array($post_type, esc_html(get_option('zeo_post_types')))){ echo 'selected';} ?> > <?php echo $post_type ?></option>
                    
                    <?php					
					}
				?>
                	</select>
                </td></tr>
                </table>                
                </div>
                
                -->
                
            <p><input type="submit" name="search" value="Update Options" class="btn btn-primary" /></p>  
            <?php wp_nonce_field( 'seo_dashboard', 'seo_dashboard_nonce_field' ); ?>
        </form>
    </div>        

    <?php include 'seo-sidebar.php';?>


     
	</div> <!-- End of Row -->

    </div>
    </div>
</div> <!-- End of Container -->


<?php include 'seo-footer.php';?>
