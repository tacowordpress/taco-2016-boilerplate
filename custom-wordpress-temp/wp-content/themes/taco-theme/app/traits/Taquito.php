<?php

use \Taco\Util\Arr;

trait Taquito  {

  public $loaded_post = null;
  
  public static function get404() {
    header("HTTP/1.0 404 Not Found - Archive Empty");
    require TEMPLATEPATH.'/404.php';
    exit;
  }

  public function auth404Check() {
    if(!is_user_logged_in()) {
        self::get404();
    }
  }

  /**
   * Overrides parent method
   * Gets only fields safe for use on the client-side (e.g. JS templates)
   * @param bool $get_ajax_fields
   * @param array $white_listed_fields
   */
  public static function getAll($get_ajax_fields=false, $white_listed_fields=null) {
    $records = parent::getAll();
    if(!$get_ajax_fields) return $records;

    $child_class = get_called_class();
    $child_class_fields = (new $child_class)->getFields();
    $field_keys = array_keys($child_class_fields);
    $new_records = [];
    foreach($records as $r) {
      $new_fields = [];
      foreach($r->_info as $k => $v) {
        if(!in_array($k, $white_listed_fields)) continue;
        $new_fields[$k] = $v;
      }
      // Add other necessary but safe fields
      $new_fields['ID'] = $r->ID;
      $new_fields['post_title'] = $r->post_title;
      $new_fields['post_content'] = $r->post_content;
      $new_fields['post_date'] = $r->post_date;
      $r->_info = $new_fields;
    }
    return $records;
  }


  public function getTheExcerptOf($field_key, $start_pos=0, $max_length=100) {
    $str = $this->get($field_key);
    if(strlen($str) > $max_length) {
      $excerpt = trim(substr($str, $start_pos, $max_length-3));
      $excerpt .= '...';
    } else {
      $excerpt = $str;
    }
    return strip_tags($excerpt);
  }

 
  public function preLoadPostAsWordpressObject() {
    $query_string = parse_url($_SERVER['QUERY_STRING']);
    $query_string = parse_str($query_string['path'], $query_vars);

    if(!array_key_exists('post', $query_vars)) {
      return false;
    }
    $post_id = $query_vars['post'];
    $post_object = get_post($post_id);
    if(is_object($post_object)) {
      $this->loaded_post = $post_object;
      return true;
    }
    return false;
  }

  public function getNumberRepeated($field_prefix) {
    $fields = $this->getFields();
    $number_array = array(1);
    foreach($fields as $k => $v) {
      $field_prefix = preg_quote($field_prefix);
      if(preg_match("/$field_prefix_(\d+)/", $k, $matches)) {
        $number_array[] = $matches[0];
      }
    }
    return max($number_array);
  }

  public function getPostTypeConfig() {
    if(get_called_class() === 'Taquito') {
      return null;
    }
    return parent::getPostTypeConfig();
  }

  public static function getPostID() {
    global $post;

    if($post) {
      return $post->ID;
    }

    if(is_admin()) {
      if($_GET['post']) {
        return $_GET['post'];
      }
      if(!array_key_exists('post', $_GET)
        && !isset($post)) {
        return false;
      }
    }

    if(array_key_exists('ID', $_POST)) {
      return $_POST['ID'];
    }
    
    return false;
  }

  public static function repeatFields($array_fields, $field_prefix=null, $multiplier=null) {

    $post_id = self::getPostID();

    if($field_prefix === null && count($array_fields) === 1) {
      $field_prefix = key($array_fields);
    }
    if($field_prefix === null && count($array_fields) > 1) {
      throw new Exception('You must specifiy a prefix if your array contains more than one field.');
      return array();
    }
    if($post_id) {
      $repeat_amount = get_post_meta(
        $post_id, $field_prefix.'_repeat_amount', true
      );
    } else {
      $repeat_amount = 1;
    }
   
    if($multiplier) {
      $repeat_amount = $multiplier;
    }
    
    $fields_new = [];
    for($i = 1; $i < $repeat_amount+1; $i++) {
      foreach($array_fields as $k => $v) {
        if($k === $field_prefix) {
          $fields_new[$field_prefix.'_'.$i] = $v;
          break;
        }
        $fields_new[$field_prefix.'_'.$i.'_'.$k] = $v;
      }
    }
    return $fields_new;
  }

  public function getFieldsWithValues($fields) {
    $new_fields = array();
    foreach($fields as $key => $value) {
      $new_fields[$key] = array_merge(array('value'=>$this->get($key)), $fields[$key]);
    }
    return $new_fields;
  }

  public static function arrayManipulate($callback, $array) {
    $new = [];
    foreach($array as $k => $v) {
      $u = $callback($k, $v);
      if(!$u) continue;
      $new[key($u)] = current($u);
    }
    return $new;
  }

  /**
   * This method is usefull if you want to assign new defaults to fields upon load
   * It will respect admin values if they're defined
   * @param array $array
   */
  public function mergeWithAdminValues($array) {
    foreach($array as $k => $v) {
      if(!$this->get($k)) {
        $this->set($k, $v);
      }
    }
    return;
  }

  public function getAdminColumns() {
    return array('title');
  }


  public static function arrayReplaceAtKey($key, $array_to_insert, $array) {
    $new = [];
    $e = $array_to_insert;

    foreach($array as $k => $v) {
      if($k === $key) {
        if(count($e) > 1) {
          foreach($e as $kk => $vv) {
            $new[$kk] = $vv;
          }
        }
      }
      $new[$k] = $v;
    }
    unset($new[$key]);
    return $new;
  }


  public static function arrayInsertAfter($key, $array_to_insert, $array) {
    $new = [];
    $e = $array_to_insert;

    foreach($array as $k => $v) {
      $new[$k] = $v;
      if($k === $key) {
        if(count($e) > 1) {
          foreach($e as $kk => $vv) {
            $new[$kk] = $vv;
          }
          continue;
        }
        $new[key($e)] = current($e);
      }
    }
    return $new;
  }


  public static function arrayInsertBefore($key, $array_to_insert, $array) {
    $new = [];
    $e = $array_to_insert;
    $last_key = $key;

    foreach($array as $k => $v) {
      if($last_key == $k) {
        if(count($e) > 1) {
          foreach($e as $kk => $vv) {
            $new[$kk] = $vv;
          }
        }
        $new[key($e)] = current($e);
      }
      $new[$k] = $v;
    }
    return $new;
  }


  public static function memoize($func) {
    return function() use ($func) {
      static $cache = [];

      $args = func_get_args();
      $key = md5(serialize($args));

      if (!isset($cache[$key])) {
        $cache[$key] = call_user_func_array($func, $args);
      }
      return $cache[$key];
    };
  }


  public static function echoOnce($string) {
    if(!isset($has_already_fired)) {
      static $has_already_fired = false;
    }
    if(!$has_already_fired) {
      echo $string;
      $has_already_fired = true;
    }
    return;
  }


  public static function doOnce($func) {
    if(!isset($has_already_fired)) {
      static $has_already_fired = false;
    }
    if(!$has_already_fired && is_callable($func)) {
      $func();
      $has_already_fired = true;
    }
    return;
  }

  /* This function serves the purpose of including a php template and
   * be explicit about what vars it injects.
   * Typically, you would just set the variable above the include, but
   * doing it that way makes it hard to follow. In a sense, this also serves
   * another purpose of stopping the ugly html that some functions/methods generate.
   */
   public static function includeWith($path, $array_vars, $once=false) {
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

  // Example: $post->getWithSprint('%s(city), %s(state) %d(zip)');
  // This will return something like "Boulder, CO, 80301"
  public function getWithSprintF($string) {
    preg_match_all('/(\%\w)\((\w+)\)/', $string, $matches);
    $key_values = array_combine(
      array_values($matches[2]),
      array_values($matches[1])
    );
    $new_string = $string;
    foreach($key_values as $field_key => $type) {
      $new_string = preg_replace(
        "/$type\($field_key\)/",
        sprintf("$type", $this->get($field_key)),
        $new_string
      );
    }
    return $new_string;
  }


  // get an array of field-values
  public static function pack($fields, $context) {
    $packed = [];
    foreach($fields as $key) {
      if($context->get($key)) {
        $packed[$key] = $context->get($key);
      }
    }
    return $packed;
  }


  /**
   * Get a single field
   * @param string $key
   * @param bool $convert_value Convert the value (for select fields)
   */
//   public function get($key, $convert_value = false)
//   {
//       $val = (array_key_exists($key, $this->_info)) ? $this->_info[$key] : null;
//       if (!is_null($val) && $val !== '' && !$convert_value) return $val;

//       $cache = phpFastCache();
//       if($cache->get($key))
//       $field = $this->getField($key);
//       if (!$convert_value) {
//           if (!$field) return $val;
//           if (array_key_exists('default', $field)) return $field['default'];
//       }
//       return (array_key_exists('options', $field) && array_key_exists($val, $field['options']))
//           ? $field['options'][$val]
//           : $val;
//   }

  public function getPostImagePath($size='medium-large') {
    $post_image = $this->get('post_image');
    $post_has_featured_image = has_post_thumbnail($this->ID);

    // get post image id
    if($post_image) {
      // get absolute url -> defined in functions-app.php
      $post_image_id = get_image_id_from_url($post_image);
      // get image description
      $image_as_post = get_post($post_image_id);
    }
    // set featured image for post
    $post_image_path  = app_image_path( // defined in functions-app.php
      $this->get('post_image'), $size
    );
    
    if($post_image_path || $post_has_featured_image) {
      // if post_image
      $final_image_path = '';
      if($post_image_path) {
        $final_image_path = $post_image_path;
      } else if($post_has_featured_image) {
        $final_image_path = wp_get_attachment_image_src(
          get_post_thumbnail_id( $this->ID ),
            $size
          );
        return $final_image_path[0];
      }
    }
    return false;
  }

  public static function deletePosts($ids, $force=true) {
    foreach($ids as $id) {
      wp_delete_post($id, $force);
    }
  }
  
  public function getRelatedPosts($addbysearch_field=null, $number=2) {

    // first check addbysearch fields for when related posts are manually assigned
    if(!is_null($addbysearch_field) && $this->get($addbysearch_field)) {
      $related_posts = \AddBySearch\AddBySearch::getPostsFromOrder(
        $this->get($addbysearch_field)
      );

      if(Arr::iterable($related_posts)) {
        $related_posts = array_slice($related_posts, 0, $number);
        return $related_posts;
      }
    }

    // next get posts by related terms
    $tax_terms = $this->getTerms();
    if(Arr::iterable($tax_terms)) {
      $terms_filtered = [];
      foreach($tax_terms as $tax_name => $terms) {
        if($tax_name === 'category') continue;
        foreach($terms as $term) {
          if($term->get('slug') === 'uncategorized') continue;
          $terms_filtered[] = $term;
        }
      }
      if(Arr::iterable($terms_filtered)) {
        $key = array_rand($terms_filtered, 1);
        $term = $terms_filtered[$key];
      }
      
      if(is_object($term)) {
        
        $related = $this->getByTerm(
          $term->get('taxonomy'),
          $term->get('slug'),
          'slug',
          array('posts_per_page' => $number)
        );

        if(Arr::iterable($related)) {
          if(array_key_exists($this->ID, $related)) {
            unset($related[$this->ID]);
          }
          $filtered = [];
          foreach($related as $r) {
            if($this->ID == $r->ID) continue;
            $filtered[] = $r;
          }
          if(Arr::iterable($filtered)) {
            return $filtered;
          }
        }
      }
      return [];
      // still nothing?
      $related = $this->getAll();
      $keys = array_rand($related, $number);
      $rand = [];
      foreach($keys as $k) {
        $rand[$k] = $related[$k];
      }
      if(array_key_exists($this->ID, $rand)) {
        unset($rand[$this->ID]);
      }
      return $rand;
    }
  }
}
