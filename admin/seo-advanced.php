<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div class="wrap">
<h1>Advanced Settings</h1>

<?php if ( $_POST['update_rss'] == 'true' ) { rss_update(); }  

function rss_update(){
	global $current_user;
	$mervin_rss = array();
	if ( !current_user_can( 'edit_user', $current_user->ID ) )
		return false;
	
	
	
	if(isset($_POST['advanced-categorybase'])){
		$mervin_advanced['advanced-categorybase']='yes';
	}
	if(isset($_POST['advanced-categorytrailing'])){
		$mervin_advanced['advanced-categorytrailing']='yes';
	}
	if(isset($_POST['advanced-attachmentredirect'])){
		$mervin_advanced['advanced-attachmentredirect']='yes';
	}
	
	
	update_option('mervin_advanced', $mervin_advanced);
	
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
<div class="postbox-container" style="width:70%;">
				<div class="metabox-holder">	                
					<div class="meta-box-sortables ui-sortable">
                    
<form method="POST" action="">  
		
        <input type="hidden" name="update_rss" value="true" />        
        <div class="postbox" id="support">
        <div class="handlediv" title="Click to toggle">
<br />
</div>
        <h3 class="hndle"><span>Permalink Settings</span></h3>
        <div class="container">
<table cellpadding="6">
		 <tr>
			<th align="right" style="font-weight:normal; width:300px;"><label for="advancedcategorybase">Strip Category Base from Category URL</label></th>

			<td>
				<input align="left" type="checkbox" name="advanced-categorybase" id="advancedcategorybase" value="yes"  <?php if(isset($options['advanced-categorybase'])){echo "checked";}?> />                
			</td>
		</tr> 
		 <tr>
			<th align="right" style="font-weight:normal; width:230px;"><label for="advancedcategorytrailing">Trailing Slash on all Category and tag URL's</label></th>

			<td>
				<input align="left" type="checkbox" name="advanced-categorytrailing" id="advancedcategorytrailing" value="yes"  <?php if(isset($options['advanced-categorytrailing'])){echo "checked";}?> />                
			</td>
		</tr>
        <tr>
			<th align="right" style="font-weight:normal; width:230px;"><label for="advancedattachmentredirect">Redirect attachment URL's to parent post URL</label></th>

			<td>
				<input align="left" type="checkbox" name="advanced-attachmentredirect" id="advancedattachmentredirect" value="yes"  <?php if(isset($options['advanced-attachmentredirect'])){echo "checked";}?> />                
			</td>
		</tr>

	</table>
    </div>    
    </div>
    
   
     <p><input type="submit" name="search" value="Update Options" class="button" /></p> 
     
</form>






</div> 
</div>
</div>