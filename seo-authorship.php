<?php

/*

  Copyright 2012  Mervin Praison  

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/* Initializing */

include( WP_PLUGIN_DIR . '/seo-wordpress/seo-authorship-badge.php');
include( WP_PLUGIN_DIR . '/seo-wordpress/seo-authorship-icon.php');

/*  End of Initializing  */

function get_google_authorship () {
	echo seo_authorship_short();
}



/*

Start of The Function

*/
function seo_authorship_short () { 
$mpgp_author_name = esc_attr( get_the_author_meta( 'zeopreferredname', $user->ID ) );
$mpgp_author_display = esc_attr( get_the_author_meta( 'display_name', $user->ID ) );
$mpgp_author_url = esc_attr( get_the_author_meta( 'zeoauthor', $user->ID ) );
if($mpgp_author_url!=NULL){

if($mpgp_author_name==NULL) 
					{
						$authorizing = $mpgp_author_display;
					}
					else{
						
					$authorizing = $mpgp_author_name;
					
					}

				$mpgpreturn = "<a href='";
				$mpgpreturn .= $mpgp_author_url;
				$mpgpreturn .= "?rel=author' rel='";
				if(is_author){ $mpgpreturn .="author";}
				else {$mpgpreturn .= "me";}
				$mpgpreturn .= "' title='Google Plus Profile for ";
				$mpgpreturn .= $authorizing; 
				$mpgpreturn .="'>";					
				$mpgpreturn .= $authorizing;
				$mpgpreturn .= "</a>";
}

		return $mpgpreturn;
} 

add_shortcode( 'seo_google_authorship', 'seo_authorship_short' );

/*

End of The Function

*/



add_action( 'show_user_profile', 'seo_author_profile_fields' );
add_action( 'edit_user_profile', 'seo_author_profile_fields' );

function seo_author_profile_fields( $user ) { 
	
	global $current_user;
	get_currentuserinfo();
	$mpgp_author_name = esc_attr( get_the_author_meta( 'zeopreferredname', $current_user->ID ) );
	$mpgp_author_url = esc_attr( get_the_author_meta( 'zeoauthor', $current_user->ID ) );

	?>
	<h3>Wordpress SEO Plugin Google Authorship</h3>

	<table class="form-table">

		<tr>
			<th><label for="mpgpauthor">Google Plus Profile URL</label></th>

			<td>
				<input type="text" name="zeoauthor" id="mpgpauthor" value="<?php echo esc_attr( get_the_author_meta( 'zeoauthor', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your Google Plus Profile URL. (with "https://plus.google.com/1234567890987654321")</span>
			</td>
		</tr>
		<tr>

			<th><label for="preferredname">Preferred Name</label></th>
			<td>
				<input type="text" name="zeopreferredname" id="preferredname" value="<?php echo esc_attr( get_the_author_meta( 'zeopreferredname', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Enter Your Preferred Name</span>
			</td>
		</tr>

	</table>
<?php }


add_action( 'personal_options_update', 'seo_author_profile_save' );

function seo_author_profile_save( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	update_usermeta( $user_id, 'zeoauthor', $_POST['zeoauthor'] );
	update_usermeta( $user_id, 'zeopreferredname', $_POST['zeopreferredname'] );
}



?>