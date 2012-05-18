<div class="wrap">
<h1>Wordpress SEO Plugin Settings</h1>
<?php 

function zeo_ischecked($chkname,$value)
    {
                  
                if(get_option($chkname) == $value)
                {
                    return true;
                } 
        	return false;
	}

if ( $_POST['update_zeooptions'] == 'true' ) { zeooptions_update(); }  

function zeooptions_update(){
	
	update_option('zeo_common_home_title', $_POST['zeo_common_home_title']);
	update_option('zeo_home_description', $_POST['zeo_home_description']);
	update_option('zeo_home_keywords', $_POST['zeo_home_keywords']); 
	update_option('zeo_blog_description', $_POST['zeo_blog_description']);
	update_option('zeo_blog_keywords', $_POST['zeo_blog_keywords']); 
	update_option('zeo_common_frontpage_title', $_POST['zeo_common_frontpage_title']); 
	update_option('zeo_common_page_title', $_POST['zeo_common_page_title']); 
	update_option('zeo_common_post_title', $_POST['zeo_common_post_title']); 
	update_option('zeo_common_category_title', $_POST['zeo_common_category_title']); 
	update_option('zeo_common_archive_title', $_POST['zeo_common_archive_title']); 
	update_option('zeo_common_tag_title', $_POST['zeo_common_tag_title']); 
	update_option('zeo_common_search_title', $_POST['zeo_common_search_title']); 
	update_option('zeo_common_error_title', $_POST['zeo_common_error_title']);
	update_option('zeo_canonical_url', $_POST['zeo_canonical_url']);
	update_option('zeo_nofollow', $_POST['zeo_nofollow']);
	update_option('zeo_activate_title', $_POST['zeo_activate_title']);	
	update_option('zeo_category_nofollow', $_POST['zeo_category_nofollow']);
	update_option('zeo_tag_nofollow', $_POST['zeo_tag_nofollow']);
	update_option('zeo_date_nofollow', $_POST['zeo_date_nofollow']);
	update_option('zeo_post_types', $_POST['zeo_post_types']);
	
	echo '<div class="updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}

?>
<div class="postbox-container" style="width:70%;">
				<div class="metabox-holder">	
					<div class="meta-box-sortables ui-sortable">
                    <div class="postbox" id="support">
<strong><h3>Want more FREE Plugins? Encourage me by,
LIKING ME and ADDING ME to your circles</h3></strong>
<table>
<tr>
<td>
<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmervinpraisons&amp;width=200&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=252850214734670" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:200px; height:62px;" allowTransparency="true"></iframe>
</td>
<td style="padding-top:10px;" >
<div class="g-plus" data-href="https://plus.google.com/101518602031253199279?rel=publisher" data-width="170" data-height="70" data-theme="light"></div>
</td>
<td style="padding-top:10px;" >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="fb_share" type="icon_link" 
   share_url="http://mervin.info/seo-wordpress" style="font-weight:bold; font-size:15px;">Share me Please</a> 
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" 
        type="text/javascript">
</script>
</td>
<td>
&nbsp;&nbsp;&nbsp;<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://mervin.info/seo-wordpress" data-size="large">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
   </td>
</tr>
</table>
</div>

                    
<form method="POST" action="">  
            <input type="hidden" name="update_zeooptions" value="true" />  
            <div class="postbox" id="support">
            <table cellpadding="6">
	            <h3>Home Page Settings</h3>
                
                <tr><td width="210">
				Home Page Title: 
				</td><td>
            	<input size="55" type="text" value="<?php echo get_option('zeo_common_home_title'); ?>" name="zeo_common_home_title"  />  
            	</td></tr>
                <tr><td>
				Home Page  Meta Description:
				</td><td>
            	<textarea size="50" rows="3" cols="52" name="zeo_home_description" ><?php echo get_option('zeo_home_description'); ?></textarea>  
            	</td></tr>
                <tr><td>
				Home Page  Meta Keywords:
				</td><td>
            	<input size="55" type="text" value="<?php echo get_option('zeo_home_keywords'); ?>" name="zeo_home_keywords"  />  
            	</td></tr>
                <tr><td>
				Blog Page  Meta Description (if exists):
				</td><td>
            	<textarea size="50" rows="3" cols="52" name="zeo_blog_description" ><?php echo get_option('zeo_blog_description'); ?></textarea>  
            	</td></tr>
                <tr><td>
				Blog Page  Meta Keywords (if exists):
				</td><td>
            	<input size="55" type="text" value="<?php echo get_option('zeo_blog_keywords'); ?>" name="zeo_blog_keywords"  />  
            	</td></tr></table>
                
                </div>
                
                <div class="postbox" id="support">
                
                
                <table cellpadding="6">
                <h3>Other Page Title Settings</h3>
                <tr><td>
				<h3>Titles</h3> </td><td><h3>Title Prefix</h3>
				</td><td>
            	<h3>Title Suffix</h3>
            	</td></tr>
                <tr><td>
				Blog Page Title: </td><td> Blog Page Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_frontpage_title'); ?>" name="zeo_common_frontpage_title"  />  
            	</td></tr>
				<tr><td>
				Page Title: </td><td> Individual Page Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_page_title'); ?>" name="zeo_common_page_title"  />  
            	</td></tr>
                <tr><td>
				Post Title: </td><td> Individual Post Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_post_title'); ?>" name="zeo_common_post_title"  />  
            	</td></tr>
                <tr><td>
				Category Title: </td><td> Individual Category Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_category_title'); ?>" name="zeo_common_category_title"  />  
            	</td></tr>                
                <tr><td>
				Archive Title: </td><td> Individual Archive Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_archive_title'); ?>" name="zeo_common_archive_title"  />  
            	</td></tr>
                
                <tr><td>
				Tag Title: </td><td> Individual Tag Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_tag_title'); ?>" name="zeo_common_tag_title"  />  
            	</td></tr>
                <tr><td>
				Search Title: </td><td> Individual Search Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_search_title'); ?>" name="zeo_common_search_title"  />  
            	</td></tr>
                <tr><td>
				404 Page Title: </td><td> Individual 404 Page Title
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_error_title'); ?>" name="zeo_common_error_title"  />  
            	</td></tr>
                </table>
                
                </div>
                 <div class="postbox" id="support">
                <table cellpadding="6">
                <h3>General Settings</h3>
                <tr>
        		<td width="212"><h3>Functions</h3></td>
        		<td width="312"><h3>Setup</h3></td>
        		</tr>
                <tr><td>
				Activate Other Page Title settings: 
				</td><td>
            	<input type="checkbox" name="zeo_activate_title" value="yes" <?php if(zeo_ischecked('zeo_activate_title', 'yes' )){echo "checked";}?>>  </input>
            	</td></tr>
                <tr><td>
				Canonical Link: 
				</td><td>
            	<input type="checkbox" name="zeo_canonical_url" value="yes" <?php if(zeo_ischecked('zeo_canonical_url', 'yes' )){echo "checked";}?>>  </input>
            	</td></tr>
                <tr><td>
				Category No Follow: 
				</td><td>
            	<input type="checkbox" name="zeo_category_nofollow" value="yes" <?php if(zeo_ischecked('zeo_category_nofollow', 'yes' )){echo "checked";}?>> </input>
            	</td></tr>
                <tr><td>
				Tag No Follow: 
				</td><td>
            	<input type="checkbox" name="zeo_tag_nofollow" value="yes" <?php if(zeo_ischecked('zeo_tag_nofollow', 'yes' )){echo "checked";}?>> </input>
            	</td></tr>
                <tr><td>
				Date Based Page No Follow: 
				</td><td>
            	<input type="checkbox" name="zeo_date_nofollow" value="yes" <?php if(zeo_ischecked('zeo_date_nofollow', 'yes' )){echo "checked";}?>> </input>
            	</td></tr>
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
                    <option value="" <?php if(in_array('', get_option('zeo_post_types'))){ echo 'selected';} ?> > Select None</option>
                <?php
					$post_types=get_post_types('','names');
					foreach ($post_types as $post_type ) {
					
				?>
                        
					<option value="<?php echo $post_type; ?>" <?php if(in_array($post_type, get_option('zeo_post_types'))){ echo 'selected';} ?> > <?php echo $post_type ?></option>
                    
                    <?php					
					}
				?>
                	</select>
                </td></tr>
                </table>                
                </div>
                
                -->
                
            <p><input type="submit" name="search" value="Update Options" class="button" /></p>  
        </form>        
     
       </div></div></div>

