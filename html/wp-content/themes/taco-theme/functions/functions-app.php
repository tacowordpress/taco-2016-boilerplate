<?php

/**
 * Register the CSS
 * @return array
 */
function app_get_css() {
  $app_css = (ENVIRONMENT === ENVIRONMENT_PROD)
    ? '_/dist/main.min.css'
    : '_/dist/main.css';

  return array(
    'all' => [
      'main' => $app_css,
    ],
  );
}


/**
 * Register the JS
 * @return array
 */
function app_get_js() {
  $app_js = (ENVIRONMENT === ENVIRONMENT_PROD)
    ? '_/dist/main.min.js'
    : '_/dist/main.js';

  $jquery_js = (ENVIRONMENT === ENVIRONMENT_PROD)
    ? '_/dist/jquery.min.js'
    : '_/dist/jquery.js';

  return [
    'jquery' => $jquery_js,
    'main' => $app_js,
  ];
}


/**
 * Register admin CSS
 * @return array
 */
function app_admin_get_css() {
  return [
    'all' => [
      'admin' => '_/css/admin.css',
    ],
  ];
}


/**
 * Register admin JS
 * @return array
 */
function app_admin_get_js() {
  return [
    // 'admin' => '_/js/admin-min.js',
  ];
}


/**
 * Register menus
 */
add_theme_support('menus');
define('MENU_PRIMARY', 'menu_primary');
function app_menus() {
  $locations = array(
    MENU_PRIMARY => __('Primary'),
  );
  register_nav_menus($locations);
}
add_action('init', 'app_menus');

/**
 * Add editor styles for Page Inserts
*/
//add_editor_style('style-wysiwyg.css');

/**
 * Add new thumbnail size
*/
// if ( function_exists( 'add_image_size' ) ) {
//   add_image_size( 'hero', 680, 450, true ); //(cropped)
// }

/**
 * Add support for excerpt
*/
add_post_type_support('page', 'excerpt');


/**
 * Get an image
 * @param string $path
 * @param size string $size (size keys that you've passed to add_image_size)
 * @return string Relative URL
 */
function app_image_path($path, $size) {
  // Which image size was requested?
  global $_wp_additional_image_sizes;
  $image_size = $_wp_additional_image_sizes[$size];

  // Get the path info
  $pathinfo = pathinfo($path);
  $fname = $pathinfo['basename'];
  $fext = $pathinfo['extension'];
  $dir = $pathinfo['dirname'];
  $fdir = realpath(str_replace('//', '/', ABSPATH.$dir)).'/';

  // Filename without any size suffix or extension (e.g. without -144x200.jpg)
  $fname_prefix = preg_replace('/(?:-\d+x\d+)?\.'.$fext.'$/i', '', $fname);
  $out_fname = sprintf(
    '%s-%sx%s.%s',
    $fname_prefix,
    $image_size['width'],
    $image_size['height'],
    $fext
  );

  // See if the file that we're predicting exists
  // If so, we can avoid a call to the database
  $fpath = $fdir.$out_fname;
  if(file_exists($fpath)) {
    return sprintf(
      '%s/%s',
      $pathinfo['dirname'],
      $out_fname
    );
  }

  // Can't find the file? Figure out the correct path from the database
  global $wpdb;
  $guid = site_url().$dir.'/'.$fname_prefix.'.'.$fext;
  $prepared = $wpdb->prepare(
    "SELECT
      pm.meta_value
    FROM $wpdb->posts p
    INNER JOIN $wpdb->postmeta pm
      ON p.ID = pm.post_id
    WHERE p.guid = %s
    AND pm.meta_key = '_wp_attachment_metadata'
    LIMIT 1",
    $guid
  );
  $row = $wpdb->get_row($prepared);
  if(is_object($row)) {
    $meta = unserialize($row->meta_value);
    if(isset($meta['sizes'][$size]['file'])) {
      $meta_fname = $meta['sizes'][$size]['file'];
      return sprintf(
        '%s/%s',
        $pathinfo['dirname'],
        $meta_fname
      );
    }
  }

  // Still nothing? Just return the path given
  return $path;
}


/**
 * Get the asset path
 * @param string $relative_path
 * @return string
 */
function get_asset_path($relative_path) {
  $clean_relative_path = $relative_path;
  $clean_relative_path = preg_replace('/^[\/_]+/', '', $clean_relative_path);
  return sprintf(
    '%s/_/%s%s',
    get_template_directory_uri(),
    $clean_relative_path,
    THEME_SUFFIX
  );
}


/**
 * Get an html string of all links to app icons
 * @return string
 */
function get_app_icons() {

  $app_dir = __DIR__.'/../';

  $files = scandir($app_dir.'/_/src/img/app-icons');
  $paths = [];
  foreach($files as $file)  {
    if(!preg_match('/\.png/', $file)) continue;
    preg_match('/(\d+x\d+)/', $file, $sizes);

    $file = '/app/wp-content/app-icons/'.$file;
    if(preg_match('/apple-icon|android/', $file)) {
      $paths[] = '<link rel="apple-touch-icon" sizes="'.$sizes[0].'" href="'.$file.'">';
      continue;
    }
    if(preg_match('/favicon/', $file) && !preg_match('/\d+/', $file)) {
      $paths[] = '<link rel="icon" type="image/ico" sizes="'.$sizes[0].'" href="'.$file.'">';
      continue;
    }
  }
  $paths[] = '<link rel="icon" href="/app/wp-content/app-icons/favicon.ico">';
  return join('', $paths);
}

// admin lockdown
add_action('init', function() {
  if(!is_admin()) return;
  if(wp_get_current_user()->data->user_login === USER_SUPER_ADMIN) return;
  $array_of_regex_restricted_admin_pages = array(
    '/plugins\.php/',
    '/edit-comments\.php/',
    '/tools.php/',
    '/options-general\.php/',
    '/themes\.php/',
    '/users\.php/'
  );
  foreach($array_of_regex_restricted_admin_pages as $p) {
    if(preg_match($p, $_SERVER['SCRIPT_NAME'])) {
      header('Location: /wp-admin/');
    }
  }
});

/* This function serves the purpose of including a php template and
 * be explicit about what vars it injects.
 * Typically, you would just set the variable above the include, but
 * doing it that way makes it hard to follow. In a sense, this also serves
 * another purpose of stopping the ugly html that some functions/methods generate.
 * @example include_with(__DIR__.'/incl-filename.php', array('foo' => $foo, 'bar' => $bar));
 */
function include_with($path, $array_vars, $once=false) {
  extract($array_vars);
  if($once) {
    include_once $path;
  } else {
    include $path;
  }
  foreach($array_vars as $k => $v) {
    unset($$k);
  }
  return;
}

// get rid of admin pages we don't need for non admin users
add_action('admin_menu', 'remove_non_vermilion_admin_menu_items', 999);
function remove_non_vermilion_admin_menu_items() {
  if(wp_get_current_user()->data->user_login != USER_SUPER_ADMIN) {
    remove_menu_page('plugins.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('tools.php');
    remove_menu_page('options-general.php');
    remove_menu_page('themes.php');
    remove_menu_page('users.php');
    remove_action('admin_notices', 'update_nag', 3);
  }
}
// Because we removed appearance and its sub menus, we need to re-enable menus here
add_action('admin_menu', 'add_non_vermilion_admin_menu_items', 999);
function add_non_vermilion_admin_menu_items() {
  if(wp_get_current_user()->data->user_login != USER_SUPER_ADMIN) {
    add_menu_page('Menus', 'Menus', 'manage_options', 'nav-menus.php', '', null, 6);
  }
}

// make the search engines discouraged text more visible
add_action('admin_print_styles', function() {
  echo '<style>p a[href*="options-reading.php"] { padding: 2px; background-color: red; color: white; };</style>';
});

// use the singles folder for all single-{post_type} or single.php template/s
add_filter('single_template', function() {
  global $post;
  if(!file_exists(__DIR__.sprintf('/../singles/single-%s.php', $post->post_type))) {
    return __DIR__.'/../singles/single.php';
  }
  return __DIR__.sprintf('/../singles/single-%s.php', $post->post_type);
});


/**
 * Get edit link when admin is logged in
 * @param int $id (post ID or term ID)
 * @param string $edit_type (post type or taxonomy slug)
 * @param string $label (optional admin-facing name for $edit_type)
 * @param bool $display_inline (omit wrapping paragraph)
 * @return string (HTML)
 */
function get_edit_link($id=null, $edit_type='post', $label=null, $display_inline=false) {
  if(!(is_user_logged_in() && current_user_can('manage_options'))) return null;

  $link_class = 'class="front-end-edit-link"';
  $link_tag = ($display_inline) ? 'span' : 'p';
  if(is_null($label)) {
    $label = Str::human(str_replace('-', ' ', $edit_type));
  }
  $subclasses = \Taco\Post\Loader::getSubclasses();
  $subclasses_machine = array_map(function($el){
    $el = substr($el, strrpos($el, '\\'));
    $el = Str::camelToHuman($el);
    $el = Str::machine($el, '-');
    return $el;
  }, $subclasses);
  if(in_array($edit_type, $subclasses_machine)) {
    // Edit post or display list of posts of this type
    $post_type_link = (!is_null($id))
      ? get_edit_post_link($id)
      : '/wp-admin/edit.php?post_type='.$edit_type;
    return sprintf(
      '<%s %s><a href="%s">Edit %s</a></%s>',
      $link_tag,
      $link_class,
      $post_type_link,
      $label,
      $link_tag
    );
  }

  // Find an applicable post type for editing a custom term
  $post_type = null;
  $post_types_by_taxonomy = [];
  foreach($subclasses as $subclass) {
    if(strpos($subclass, '\\') !== false) {
      $subclass = '\\'.$subclass;
    }
    $taxonomies = \Taco\Post\Factory::create($subclass)->getTaxonomies();
    if(Arr::iterable($taxonomies)) {
      foreach($taxonomies as $key => $taxonomy) {
        $taxonomy_slug = (is_array($taxonomy))
          ? $key
          : $taxonomy;
        $post_types_by_taxonomy[$taxonomy_slug][] = $subclass;
      }
    }
  }
  $post_types_by_taxonomy = array_unique($post_types_by_taxonomy);
  if(array_key_exists($edit_type, $post_types_by_taxonomy)) {
    $post_type = reset($post_types_by_taxonomy[$edit_type]);
    $post_type = substr($post_type, strrpos($post_type, '\\'));
    $post_type = Str::camelToHuman($post_type);
    $post_type = Str::machine($post_type, '-');
  } else {
    $post_type = 'post';
  }

  if(is_null($id)) {
    // View taxonomy term list
    return sprintf(
      '<%s %s><a href="/wp-admin/edit-tags.php?taxonomy=%s&post_type=%s">View %ss</a></%s>',
      $link_tag,
      $link_class,
      $edit_type,
      $post_type,
      $label,
      $link_tag
    );
  }

  // Edit term
  return sprintf(
    '<%s %s><a href="%s">Edit %s</a></%s>',
    $link_tag,
    $link_class,
    get_edit_term_link($id, $edit_type, $post_type),
    $label,
    $link_tag
  );
}


/**
 * Get App Options link when admin is logged in
 * @param string $description
 * @param bool $display_inline
 * @return type
 */
function get_app_options_link($description=null, $display_inline=false) {
  if(!(is_user_logged_in() && current_user_can('manage_options'))) return null;

  if(is_null($description)) {
    $description = 'this';
  }
  $options = AppOption::getInstance();
  return get_edit_link($options->ID, 'app-option', $description.' in '.$options->getPlural(), $display_inline);
}


function add_slug_to_body_class($classes=[]) {
  global $post;
  $file_name = basename($_SERVER['SCRIPT_FILENAME'], '.php');
  $queried_object = get_queried_object();
  $is_term = (is_object($queried_object) && property_exists($queried_object, 'term_taxonomy_id'));
  if(!$is_term && !is_null($post)) {
    $classes[] = $post->post_name;
  } else {
    $classes[] = Str::machine($file_name, '-');
  }
  return $classes;
}
add_filter('body_class', 'add_slug_to_body_class');


function add_slug_to_menu_item_class($menu_html) {
  // Get menu item IDs and link slugs
  preg_match_all('/menu-item-(\d+).*href="(?:(?:.*?)\/\/(?:.*?))?\/(.*?)\/?"/', $menu_html, $matches);

  // Combine match groups into array
  $menu_items = array_combine($matches[1], $matches[2]);

  // Strip slugs down to last segment
  $menu_items = array_map(function($el){
    $slash_index = strrpos($el, '/');
    return ($slash_index)
      ? substr($el, $slash_index + 1)
      : $el;
  }, $menu_items);

  // Search/replace
  foreach($menu_items as $menu_item_id => $link_slug) {
    $menu_html = preg_replace('/menu-item-'.$menu_item_id.'">/', 'menu-item-'.$menu_item_id.' menu-item-'.$link_slug.'">', $menu_html, 1);
  }
  return $menu_html;
}
add_filter('wp_nav_menu', 'add_slug_to_menu_item_class');
