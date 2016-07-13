<?php

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
* function to check the status of the plugins
* @return array
*/
function plugin_activity_checker() {
    // Check if get_plugins() function exists. This is required on the front end of the
    // site, since it is in a file that is normally only loaded in the admin.
    if ( ! function_exists( 'get_plugins' ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    // possible seo plugins
    $seo_plugins = array(
        "wordpress-seo/wp-seo.php" => "Yoast"
    );

    $active_plugins = get_option("active_plugins");
    $all_plugins = array_keys(get_plugins());
    // enabled, uploaded
    $plugin_status = [];
     
    foreach($seo_plugins as $name => $title) {
      $plugin_enabled = false;
      $plugin_uploaded = false;
      
      if(in_array($name, $active_plugins)) {
        $plugin_enabled = true;
        $plugin_uploaded = true;
              
      } elseif(in_array($name, $all_plugins)) {
        $plugin_uploaded = true;
      }
      
      $plugin_status[$title] = array(
        'plugin_enabled' => $plugin_enabled,
        'plugin_uploaded' => $plugin_uploaded
      );
    }
    return $plugin_status;
}

/**
* Check if SEO plugin installed
* @return bool
*/
function check_if_seo_plugin_installed() {
  // see if seo is already installed
  $plugins_status = plugin_activity_checker();
  $yoast_isset = isset($plugins_status['Yoast']);
  $yoast_enabled = $plugins_status['Yoast']['plugin_enabled'];
    
  if($yoast_isset && $yoast_enabled === true) {
    return true;
  } else {
    return false;
  }
}


/**
 * Get the page title
 * @return string
 */
function app_get_page_title() {
  // check if yoast is set
  $yoast_isset = check_if_seo_plugin_installed();
    
  if($yoast_isset) {
    return get_the_title();
  } else {
    return join(' | ', array_filter(array(wp_title(null, false), get_bloginfo('name')), 'strlen'));
  }
}


/**
 * Enqueue the CSS
 * @param bool $is_admin
 */
function app_enqueue_style($is_admin=false) {
  $styles = ($is_admin)
    ? app_admin_get_css()
    : app_get_css();
  if(!Arr::iterable($styles)) return;
  
  $template_directory = get_template_directory_uri();
  foreach($styles as $media => $media_styles) {
    if(!Arr::iterable($media_styles)) continue;
    
    foreach($media_styles as $k => $media_style) {
      $path = (preg_match('/^(https?\:|\/\/)/', $media_style))
        ? $media_style
        : $template_directory.'/'.$media_style;
      wp_register_style($k, $path, false, THEME_VERSION, $media);
      wp_enqueue_style($k);
    }
  }
}


/**
 * Enqueue the JS
 * @param bool $is_admin
 * @param array $scripts
 */
function app_enqueue_script($is_admin=false) {
  $scripts = ($is_admin)
    ? app_admin_get_js()
    : app_get_js();
  if(!Arr::iterable($scripts)) return;
  
  $template_directory = get_template_directory_uri();
  foreach($scripts as $key => $script) {
    $path = (preg_match('/^(https?\:|\/\/)/', $script))
      ? $script
      : $template_directory.'/'.$script;
    wp_deregister_script($key);
    wp_register_script($key, $path, false, THEME_VERSION, true);
    wp_enqueue_script($key);
  }
}


/**
 * Enqueue admin CSS
 */
function app_admin_enqueue_style() {
  app_enqueue_style(true);
}


/**
 * Enqueue admin JS
 */
function app_admin_enqueue_script() {
  app_enqueue_script(true);
}


/**
 * Enqueue both CSS and JS
 */
if(!is_admin() && !is_auth_page()) {
  add_action('wp_enqueue_scripts', 'app_enqueue_style', 10);
  add_action('wp_enqueue_scripts', 'app_enqueue_script', 1);
}
if(is_admin() && !is_auth_page()) {
  add_action('admin_enqueue_scripts', 'app_admin_enqueue_style', 10);
  add_action('admin_enqueue_scripts', 'app_admin_enqueue_script', 1);
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


// Disable emojis
// https://wordpress.org/support/topic/cant-remove-emoji-detection-script
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');


/**
 * Disable automatic embedding
 * @link http://wordpress.stackexchange.com/questions/211701/what-does-wp-embed-min-js-do-in-wordpress-4-4
 */
function my_deregister_scripts() {
  wp_deregister_script('wp-embed');
}
add_action('wp_footer', 'my_deregister_scripts');
