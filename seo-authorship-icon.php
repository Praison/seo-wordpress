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

function get_google_authorship_icon () {
	echo seo_authorship_icon_short();
}


/* Start of The Function */


function seo_authorship_icon_short () { 




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
				$mpgpreturn .= "?prsrc=3' rel='";
				if(is_author){ $mpgpreturn .="author";}
				else {$mpgpreturn .= "me";}
				$mpgpreturn .= "' style='text-decoration:none;' title='Google Plus Profile for ";
				$mpgpreturn .= $authorizing; 
				$mpgpreturn .="'><img src='https://ssl.gstatic.com/images/icons/gplus-32.png' alt='' style='border:0;width:32px;height:32px;'/>";
				$mpgpreturn .= "</a>";
}

		return $mpgpreturn;
} 

add_shortcode( 'seo_google_authorship_icon', 'seo_authorship_icon_short' );

/* End of The Function */





?>