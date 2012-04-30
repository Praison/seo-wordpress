<div class="wrap">
<h1>Google Authorship And Analytics Settings</h1>
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

<div class="postbox-container" style="width:70%;">
				<div class="metabox-holder">	
					<div class="meta-box-sortables ui-sortable">
                    <div class="postbox" id="support">
<strong><h3>Want more FREE Plugins? Encourage me by,
LIKING ME and ADDING ME to your circles</h3></strong>
<table>
<tr>
<td>
<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fmervinpraisons&amp;width=250&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=252850214734670" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px; height:62px;" allowTransparency="true"></iframe>
</td>
<td style="padding-top:10px;" >
<div class="g-plus" data-href="https://plus.google.com/101518602031253199279?rel=publisher" data-width="170" data-height="70" data-theme="light"></div>
</td>
<td style="padding-top:10px;" >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a name="fb_share" type="icon_link" 
   share_url="http://mervin.info/seo-wordpress" style="font-weight:bold; font-size:15px;">Share me Please</a> 
<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" 
        type="text/javascript">
</script>
</td>
</tr>
</table>
</div>



<form method="POST" action="">  
        <input type="hidden" name="update_authorshipoptions" value="true" />
        <div class="postbox" id="support">
        <h3>Google Authorship Settings</h3>
<table cellpadding="6">
        
		

<?php
global $current_user;
	get_currentuserinfo();
    ?>
    


		<tr>
			<th align="left" style="font-weight:normal"><label for="mpgpauthor">Google Plus Profile URL (Required)</label></th>

			<td>
				<input size="54" type="text" name="zeoauthor" id="mpgpauthor" value="<?php echo esc_attr( get_the_author_meta( 'zeoauthor', $current_user->ID ) ); ?>" class="regular-text" />
                <!--<br />
				<span class="description">Please enter your Google Plus Profile URL. (with "https://plus.google.com/1234567890987654321")</span>
                -->
			</td>
		</tr>
		<tr>

			<th align="left" style="font-weight:normal"><label for="preferredname">Preferred Name</label></th>
			<td>
				<input size="54" type="text" name="zeopreferredname" id="preferredname" value="<?php echo esc_attr( get_the_author_meta( 'zeopreferredname', $current_user->ID ) ); ?>" class="regular-text" />
                <!--
                <br />
				<span class="description">Enter Your Preferred Name</span>
                -->
			</td>
		</tr>

	</table>
    </div>
     <p><input type="submit" name="search" value="Update Options" class="button" /></p>  
</form>
<br />

        <form method="POST" action="">  
        <input type="hidden" name="update_analyticsoptions" value="true" />
        <div class="postbox" id="support">
        <h3>Google Analytics Settings</h3>
        <table cellpadding="6">
        
        <tr>
        <td>Please Enter your Analytics Tracking ID</td>
        <td><input size="51" type="text" value="<?php echo get_option('zeo_analytics_id'); ?>" name="zeo_analytics_id"  /></td>
        </tr>
        
        
        </table>
        </div>
            <p><input type="submit" name="search" value="Update Options" class="button" /></p>  
        </form> 


</div></div>

</div>