<?php
move_me_around_scripts();

function move_me_around_scripts() {
     wp_enqueue_script('dashboard');
}

?>
<div class="wrap">
<h1>Breadcrumbs Settings</h1>

<?php if ( $_POST['update_sitemapoptions'] == 'true' ) { sitemapoptions_update(); }  

function sitemapoptions_update(){
	global $current_user;
	$mervin_breadcrumbs = array();
	if ( !current_user_can( 'edit_user', $current_user->ID ) )
		return false;
	
				
	
	$listtoupdate = array('breadcrumbs-enable', 'breadcrumbs-boldlast', 'trytheme', 'breadcrumbs-sep', 'breadcrumbs-home', 'breadcrumbs-selectedmenu', 'breadcrumbs-blog-remove', 'breadcrumbs-menus', 'breadcrumbs-archiveprefix', 'breadcrumbs-searchprefix', 'breadcrumbs-404crumb', 'breadcrumbs-prefix' );
	foreach ($listtoupdate as $list){
	if(isset($_POST[$list])){
		$mervin_breadcrumbs[$list]=stripslashes($_POST[$list]);
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
<div class="postbox-container" style="width:70%;">
				<div class="metabox-holder">	                
					<div class="meta-box-sortables ui-sortable">
                    
<form method="POST" action="">  
		
        <input type="hidden" name="update_sitemapoptions" value="true" />        
        <div class="postbox" id="support">
        <div class="handlediv" title="Click to toggle">
<br />
</div>
        <h3 class="hndle"><span>Overall Settings</span></h3>
        <div class="container">
<table cellpadding="6">
		<tr>
			<th align="right" style="font-weight:normal; width:230px;"><label for="enablebreadcrumbs">Enable Breadcrumbs</label></th>

			<td>
				<input align="left" type="checkbox" name="breadcrumbs-enable" id="enablebreadcrumbs" value="yes"  <?php if(isset($options['breadcrumbs-enable'])){echo "checked";}?> />                
			</td>
		</tr>
        <tr>
			<th align="right" style="font-weight:normal"><label for="breadcrumbsep">Breadcrumbs Separator</label></th>

			<td>
				<input size="54" type="text" name="breadcrumbs-sep" id="breadcrumbsep" class="regular-text" <?php if(isset($options['breadcrumbs-sep'])){ ?>
                value="<?php echo $options['breadcrumbs-sep']?>"
				<?php 	}?> />                
			</td>
		</tr>
        
        <tr>
			<th align="right" style="font-weight:normal"><label for="breadcrumbhome">Home Page Anchor Text</label></th>

			<td>
				<input size="54" type="text" name="breadcrumbs-home" id="breadcrumbhome" class="regular-text" <?php if(isset($options['breadcrumbs-home'])){ ?>
                value="<?php echo $options['breadcrumbs-home']?>"
				<?php 	}?> />                
			</td>
		</tr>
        
        <tr>
			<th align="right" style="font-weight:normal"><label for="breadcrumbprefix">Breadcrumb Prefix</label></th>

			<td>
				<input size="54" type="text" name="breadcrumbs-prefix" id="breadcrumbprefix" class="regular-text" <?php if(isset($options['breadcrumbs-prefix'])){ ?>
                value="<?php echo $options['breadcrumbs-prefix']?>"
				<?php 	}?> />                
			</td>
		</tr>
        
        <tr>
			<th align="right" style="font-weight:normal"><label for="breadcrumbarchiveprefix">Archive Page Prefix</label></th>

			<td>
				<input size="54" type="text" name="breadcrumbs-archiveprefix" id="breadcrumbarchiveprefix" class="regular-text" <?php if(isset($options['breadcrumbs-archiveprefix'])){ ?>
                value="<?php echo $options['breadcrumbs-archiveprefix']?>"
				<?php 	}?> />                
			</td>
		</tr>
        
        <tr>
			<th align="right" style="font-weight:normal"><label for="breadcrumbsearchprefix">Search Page Prefix</label></th>

			<td>
				<input size="54" type="text" name="breadcrumbs-searchprefix" id="breadcrumbsearchprefix" class="regular-text" <?php if(isset($options['breadcrumbs-searchprefix'])){ ?>
                value="<?php echo $options['breadcrumbs-searchprefix']?>"
				<?php 	}?> />                
			</td>
		</tr>
        
        <tr>
			<th align="right" style="font-weight:normal"><label for="breadcrumb404prefix">404 Page Prefix</label></th>

			<td>
				<input size="54" type="text" name="breadcrumbs-404crumb" id="breadcrumb404prefix" class="regular-text" <?php if(isset($options['breadcrumbs-404crumb'])){ ?>
                value="<?php echo $options['breadcrumbs-404crumb']?>"
				<?php 	}?> />                
			</td>
		</tr>
        
        <tr>
			<th align="right" style="font-weight:normal"><label for="removeblogbreadcrumbs">Remove Breadcrumbs on Blog Pages</label></th>

			<td>
				<input size="54" type="checkbox" name="breadcrumbs-blog-remove" id="removeblogbreadcrumbs" value="yes" <?php if(isset($options['breadcrumbs-blog-remove'])){echo "checked";}?> />                
			</td>
		</tr>
        
        <tr>
			<th align="right" style="font-weight:normal"><label for="boldlastbreadcrumbs">Bold Last page in the Breadcrumb</label></th>

			<td>
				<input size="54" type="checkbox" name="breadcrumbs-boldlast" id="boldlastbreadcrumbs" value="yes" <?php if(isset($options['breadcrumbs-boldlast'])){echo "checked";}?> />                
			</td>
		</tr>
		
        <tr>
			<th align="right" style="font-weight:normal"><label for="trybreadcrumbs">Hybric, Thematic or Thesis Theme</label></th>

			<td>
				<input size="54" type="checkbox" name="trytheme" id="trybreadcrumbs" value="yes" <?php if(isset($options['trytheme'])){echo "checked";}?> />                
			</td>
		</tr>

	</table>
    </div>    
    </div>
   
    
    <div class="postbox" id="support2">
    <div class="handlediv" title="Click to toggle"><br /></div>
        <h3 class="hndle">Taxonomy to Show in Breadcrumbs</h3>
       		<div class="container">
				<table cellpadding="6">
                
                    
 						
					
                    
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
			
						$output = '<tr><td style="width:230px;" ><label style="float:right;" for="'.$id.'">'.$label.':</label></td>';
						$output .= '<td><select name="'.$option.'['.$id.']" id="'.$id.'">';
			
							foreach($values as $value => $label) {
								$sel = '';
								if (isset($options[$id]) && $options[$id] == $value)
								$sel = 'selected="selected" ';

								if (!empty($label))
								$output .= '<option '.$sel.'value="'.$value.'">'.$label.'</option>';
								}
						$output .= '</select></td><tr>';
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
                    
				</table>
   			 </div>
    </div>
    
    
    <div class="postbox" id="support3">
    <div class="handlediv" title="Click to toggle"><br /></div>
        <h3 class="hndle">Post type archive to show in breadcrumbs for:</h3>
       		<div class="container">
				<table cellpadding="6">
 						
					<tr>				

					<td>
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
					</td>
					</tr>
		
 						
				</table>
						
				
   			 </div>
       <br />
       <strong>Use this code to insert Breadcrumbs in your theme:</strong>
              <br />
			 <pre>&lt;?php if ( function_exists(&#x27;get_mervin_breadcrumbs&#x27;) ) {
	get_mervin_breadcrumbs(&#x27;&lt;p id=&quot;breadcrumbs&quot;&gt;&#x27;,&#x27;&lt;/p&gt;&#x27;);
} ?&gt;</pre>		
    </div>
     <p><input type="submit" name="search" value="Update Options" class="button" /></p> 
     
</form>






</div> 
</div>
</div>