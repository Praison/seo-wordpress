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

function get_google_authorship_badge () {
	echo seo_authorship_badge_short();
}

/* Google Plus Badge Header Code */

function seo_custom_js() {
    echo "<script type='text/javascript'>
window.___gcfg = {lang: 'en'};
(function() 
{var po = document.createElement('script');
po.type = 'text/javascript'; po.async = true;po.src = 'https://apis.google.com/js/plusone.js';
var s = document.getElementsByTagName('script')[0];
s.parentNode.insertBefore(po, s);
})();</script>";
}

add_action('wp_head', 'seo_custom_js');
/* End of Google Plus Badge Header Code */

/* Start of The Function */


function seo_authorship_badge_short () { 

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

				$mpgpreturn = "<g:plus href='";
				$mpgpreturn .= $mpgp_author_url;
				$mpgpreturn .= "' rel='";
				if(is_author){ $mpgpreturn .="author";}
				else {$mpgpreturn .= "me";}
				$mpgpreturn .= "' width='170' height='69' title='Google Plus Profile for ";
				$mpgpreturn .= $authorizing; 
				$mpgpreturn .="'>";
				$mpgpreturn .= "</g:plus>";
}

		return $mpgpreturn;
} 

add_shortcode( 'seo_google_authorship_badge', 'seo_authorship_badge_short' );

/* End of The Function */





?>