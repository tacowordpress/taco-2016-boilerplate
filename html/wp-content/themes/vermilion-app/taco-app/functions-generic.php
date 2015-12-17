<?php


/**
* define excerpt
*/
// function get_excerpt($str, $start_pos=0, $max_length=100) {
//   if(strlen($str) > $max_length) {
//     $excerpt   = substr($str, $start_pos, $max_length-3);
//     $last_space = strrpos($excerpt, ' ');
//     $excerpt   = substr($excerpt, 0, $last_space);
//     $excerpt  .= '...';
//   } else {
//     $excerpt = $str;
//   }
//   return strip_tags($excerpt);
// }

/**
 * Is this the login page?
 * @return bool
 */
function is_auth_page() {
  return (
    array_key_exists('pagenow', $GLOBALS)
    && in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))
  );
}


/**
 * Get full current URL
 * @link http://stackoverflow.com/questions/6768793/php-get-the-full-url
 * @return string
 */
function get_full_url() {
  $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
  $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
  $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
  $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
  return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
}



/**
 * Change the login logo with CSS
 */
function app_login_css() {
  wp_enqueue_style('login_css', get_template_directory_uri() . '/css/login.css');
}
add_action('login_head', 'app_login_css');


/**
 * Remove the link and 'Powered by WordPress' from the login page
 */
function app_login_header_unlink() {
  return null;
}
add_filter('login_headerurl', 'app_login_header_unlink');
add_filter('login_headertitle', 'app_login_header_unlink');


/**
 * Get the page title
 * @return string
 */
function app_get_page_title() {
  return join(' | ', array_filter(array(wp_title(null, false), get_bloginfo('name')), 'strlen'));
}


/**
 * Removed Comments from the admin nav
 */
function app_admin_disable_comments() {
  global $menu;
  
  // Use regex to match b/c some items will have numbers suffixed (e.g. Comments 1)
  $remove_titles = array(
    '/Comments/i',
  );
  $items = array_combine(
    array_keys($menu),
    Collection::pluck($menu, 0)
  );
  foreach($items as $id=>$title) {
    foreach($remove_titles as $regex) {
      if(!preg_match($regex, $title)) continue;
      
      unset($menu[$id]);
      break;
    }
    if(!array_key_exists($id, $menu)) continue;
  }
}
add_action('admin_menu', 'app_admin_disable_comments');


/**
 * Enqueue the CSS
 */
function app_enqueue_style() {
  $styles = app_get_css();
  if(!Arr::iterable($styles)) return;
  
  foreach($styles as $media=>$media_styles) {
    if(!Arr::iterable($media_styles)) continue;
    
    foreach($media_styles as $k=>$media_style) {
      wp_register_style($k, get_template_directory_uri() . '/' . $media_style, false, THEME_VERSION, $media);
      wp_enqueue_style($k);
    }
  }
}


/**
 * Enqueue the JS
 */
function app_enqueue_script() {
  $scripts = app_get_js();
  if(!Arr::iterable($scripts)) return;
  
  foreach($scripts as $k=>$script) {
    wp_deregister_script($k);
    wp_register_script($k, get_template_directory_uri() . '/' . $script, false, THEME_VERSION, true);
    wp_enqueue_script($k);
  }
}


/**
 * Enqueue both CSS and JS
 */
if(!is_admin() && !is_auth_page()) {
  add_action('wp_enqueue_scripts', 'app_enqueue_style', 10);
  add_action('wp_enqueue_scripts', 'app_enqueue_script', 1);
}


/**
 * Removed unused items from the admin nav
 */
function app_clean_admin_nav() {
  global $menu;
  
  // Use regex to match b/c some items will have numbers suffixed (e.g. Comments 1)
  $remove_titles = array(
    //'/Dashboard/i',
    //'/Media/i',
    //'/Pages/i',
    //'/^Posts/i',
    '/Comments/i',
    //'/Appearance/i',
    //'/Plugins/i',
    //'/Users/i',
    //'/Tools/i',
    //'/Settings/i',
  );
  $items = array_combine(
    array_keys($menu),
    Collection::pluck($menu, 0)
  );
  foreach($items as $id=>$title) {
    foreach($remove_titles as $regex) {
      if(!preg_match($regex, $title)) continue;
      
      unset($menu[$id]);
      break;
    }
    if(!array_key_exists($id, $menu)) continue;
  }
}
add_action('admin_menu', 'app_clean_admin_nav');