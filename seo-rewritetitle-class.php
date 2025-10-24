<?php


class zeo_rewrite_title {

  public function __construct(){
    
    
  }
  public function zeo_rewrite(&$content) {
    $title = false;
  $uid = 'zeo_title';
  $seo_data_class = new seo_data_class();
  $individual_title = $seo_data_class->zeo_get_post_meta($uid);

    $bloghome = get_option('home');
    if (substr($bloghome, 0, 1) != '/') {
      $pattern = preg_quote($bloghome, '/');
      $content = preg_replace("/$pattern\"/", "$bloghome/\"", $content);
    }

    if (is_single()) {
    if($individual_title==NULL)
      $title = trim(wp_title(false, false));
    else
    $title = $individual_title;
    $title .= " ";
    $title .= get_option('zeo_common_post_title');
    } else if (is_archive()) {
      global $post, $posts;

      if (is_category()) {
        $title = trim(single_cat_title('', false)); $title .= " ";
    $title .= get_option('zeo_common_category_title'); 
      } else if (is_month()) {
        $title = get_the_time('F, Y'); $title .= " ";
    $title .= get_option('zeo_common_archive_title');
      } else if (is_day()) {
        $title = get_the_time('F jS, Y'); $title .= " ";
    $title .= get_option('zeo_common_archive_title');
      } else if (is_year()) {
        $title = get_the_time('Y'); $title .= " ";
    $title .= get_option('zeo_common_archive_title');
      }
    else if (is_tag()) {
      $title = trim(wp_title(false, false)); $title .= " ";
    $title .= get_option('zeo_common_tag_title');
    }
    } else if (is_search()) {
      $title = isset($_REQUEST['s']) ? trim(sanitize_text_field( wp_unslash( $_REQUEST['s'] ) )) : ''; $title .= " ";
    $title .= get_option('zeo_common_search_title');
    }else if (is_home()) {
    if(is_front_page())   
    $title = get_option('zeo_common_home_title');
    else {
      $title = trim(get_post_meta(get_option( 'page_for_posts' ), 'zeo_title', true));
      if($title==NULL)
        $title = trim(wp_title(false, false));
      $title .= " ";
      $title .= get_option('zeo_common_frontpage_title');      
    }
    
    }else if (is_front_page()) {          
      $title = trim(get_post_meta(get_option( 'page_on_front' ), 'zeo_title', true));    
      if($title==NULL)
        $title = trim(wp_title(false, false));
    }else if (is_page()) {
      if($individual_title==NULL)
      $title = trim(wp_title(false, false));
    else
    $title = $individual_title;
    $title .= " ";
    $title .= get_option('zeo_common_page_title');
    }
    
    if ($title) {
      $blogname = get_option('blogname');

      $content = preg_replace("/<title>.*<\/title>/", "<title>$title</title>", $content);
      //$content = preg_replace("/>$blogname</", ">$title - $blogname<", $content);
    }
  }
  
  function wpzeo_footer() {
    // Fetch the page (which is only missing some end tags)
    $content = ob_get_contents();

    // and erase the output buffer.
    ob_end_clean();

    // Actually rewrite the page.
    $this->zeo_rewrite($content);

    // Finally, echo the page so the browser can fetch it.
    echo($content);
  }
  
  function starting() {
    ob_start();
  }

  


}


function zeo_final_title(){
    // Use object to avoid namespace collisions
  $zeo_rewrite_title = new zeo_rewrite_title();

  // We want to act when the page is 99% complete
  add_action('wp_footer', array(&$zeo_rewrite_title, 'wpzeo_footer'));

  // There is no action hook for "start of processing",
  // so we use this implicitly.
  $zeo_rewrite_title->starting();
}

/* General Function */

function zeo_ischk($chkname,$value)
    {
                  
                if(get_option($chkname) == $value)
                {
                    return true;
                } 
          return false;
  }
if(zeo_ischk('zeo_activate_title', 'yes' )){zeo_final_title();}

?>