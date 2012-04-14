<div class="wrap">
<h1>Wordpress SEO Plugin Settings</h1>
<br />
<?php if ( $_POST['update_zeooptions'] == 'true' ) { zeooptions_update(); }  

function zeooptions_update(){
	
	update_option('zeo_common_home_title', $_POST['zeo_common_home_title']);
	update_option('zeo_home_description', $_POST['zeo_home_description']);
	update_option('zeo_home_keywords', $_POST['zeo_home_keywords']); 
	update_option('zeo_common_frontpage_title', $_POST['zeo_common_frontpage_title']); 
	update_option('zeo_common_page_title', $_POST['zeo_common_page_title']); 
	update_option('zeo_common_post_title', $_POST['zeo_common_post_title']); 
	update_option('zeo_common_category_title', $_POST['zeo_common_category_title']); 
	update_option('zeo_common_archive_title', $_POST['zeo_common_archive_title']); 
	update_option('zeo_common_tag_title', $_POST['zeo_common_tag_title']); 
	update_option('zeo_common_search_title', $_POST['zeo_common_search_title']); 
	update_option('zeo_common_error_title', $_POST['zeo_common_error_title']); 
	
	
	echo '<div class="updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?>
<?php if ( $_POST['update_authorshipoptions'] == 'true' ) { authorshipoptions_update(); }  

function authorshipoptions_update(){
	global $current_user;
	if ( !current_user_can( 'edit_user', $current_user->ID ) )
		return false;

	update_usermeta( $current_user->ID, 'zeoauthor', $_POST['zeoauthor'] );
	update_usermeta( $current_user->ID, 'zeopreferredname', $_POST['zeopreferredname'] );
	
	echo '<div class="updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?>
  <?php if ( $_POST['update_analyticsoptions'] == 'true' ) { analyticsoptions_update(); }  

function analyticsoptions_update(){
	
	
	update_option('zeo_analytics_id', $_POST['zeo_analytics_id']); 
	
	echo '<div class="updated">
		<p>
			<strong>Options saved</strong>
		</p>
	</div>'; 
	
}
?> 
<h3>If you thought of Encouraging me to develop more Plugins for FREE,<br />
Please LIKE ME and ADD ME to your circles</h3>
<table>
<tr>
<td>
<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmervinpraisons&amp;width=250&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=252850214734670" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px; height:62px;" allowTransparency="true"></iframe>
</td>
<td>
<div class="g-plus" data-href="https://plus.google.com/101518602031253199279?rel=publisher" data-width="170" data-height="69" data-theme="light"></div>
</td>
</tr>
</table>
<form method="POST" action="">  
            <input type="hidden" name="update_zeooptions" value="true" />  
            <table cellpadding="2">
	            <h3>Home Page Settings</h3>
                <tr style="background-color:#CCC;">
        		<td width="210"><b>Home Page</b></td>
        		<td><b>Settings</b></td>
        		</tr>
                <tr><td>
				Home Page Title: 
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_common_home_title'); ?>" name="zeo_common_home_title"  />  
            	</td></tr>
                <tr><td>
				Home Page  Meta Description:
				</td><td>
            	<textarea size="50" rows="3" cols="52" name="zeo_home_description" ><?php echo get_option('zeo_home_description'); ?></textarea>  
            	</td></tr>
                <tr><td>
				Home Page  Meta Keywords:
				</td><td>
            	<input size="50" type="text" value="<?php echo get_option('zeo_home_keywords'); ?>" name="zeo_home_keywords"  />  
            	</td></tr></table><table>
                <h3>Other Page Title Settings</h3>
                <tr style="background-color:#CCC;"><td>
				<b>Titles</b> </td><td><b>Title Prefix</b>
				</td><td>
            	<b>Title Suffix</b>
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
            <p><input type="submit" name="search" value="Update Options" class="button" /></p>  
        </form> 
        
     
        <br />
        <h1>Google Analytics Settings</h1>
        <form method="POST" action="">  
        <input type="hidden" name="update_analyticsoptions" value="true" />
        <table cellpadding="2">
        <tr style="background-color:#CCC;">
        <td width="230"><b>Analytics</b></td>
        <td><b>ID</b></td>
        </tr>
        <tr>
        <td>Please Enter your Tracking ID</td>
        <td><input size="48" type="text" value="<?php echo get_option('zeo_analytics_id'); ?>" name="zeo_analytics_id"  /></td>
        </tr>
        
        
        </table>
            <p><input type="submit" name="search" value="Update Options" class="button" /></p>  
        </form> 


<br />

<h1>Google Authorship Settings</h1>
<form method="POST" action="">  
        <input type="hidden" name="update_authorshipoptions" value="true" />
<table cellpadding="2">
        <tr style="background-color:#CCC;"><td width="230"><b>Function</b></td><td width="300"><b>Setup</b></td></tr>
		

<?php
global $current_user;
	get_currentuserinfo();
    ?>
    


		<tr>
			<th align="left" style="font-weight:normal"><label for="mpgpauthor">Google Plus Profile URL (Required)</label></th>

			<td>
				<input type="text" name="zeoauthor" id="mpgpauthor" value="<?php echo esc_attr( get_the_author_meta( 'zeoauthor', $current_user->ID ) ); ?>" class="regular-text" />
                <!--<br />
				<span class="description">Please enter your Google Plus Profile URL. (with "https://plus.google.com/1234567890987654321")</span>
                -->
			</td>
		</tr>
		<tr>

			<th align="left" style="font-weight:normal"><label for="preferredname">Preferred Name</label></th>
			<td>
				<input type="text" name="zeopreferredname" id="preferredname" value="<?php echo esc_attr( get_the_author_meta( 'zeopreferredname', $current_user->ID ) ); ?>" class="regular-text" />
                <!--
                <br />
				<span class="description">Enter Your Preferred Name</span>
                -->
			</td>
		</tr>

	</table>
     <p><input type="submit" name="search" value="Update Options" class="button" /></p>  
</form>

</div>