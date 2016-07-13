<?php

namespace AppLibrary;


class Util {
  
  /**
   * Get page sibling/ancestor navigation links
   * Pass 0 to $page_id for top-level nav only
   * @param int $page_id
   * @param array $args WP_Query args to be merged with defaults
   * @return string HTML
   */
  public static function getPageNav($page_id=null, $args=array()) {
    if(is_null($page_id)) {
      global $post;
      $page_id = $post->ID;
    }
    $top_level_page_id = null;
    if($page_id === 0) {
      $top_level_page_id = 0;
      $args['depth'] = 1;
    } else {
      $ancestors = get_post_ancestors($page_id);
      if(Arr::iterable($ancestors)) {
        $top_level_page_id = reset($ancestors);
      } else {
        $top_level_page_id = $page_id;
      }
    }
    
    return wp_list_pages(array_merge(array(
      'child_of' => $top_level_page_id,
      'depth' => 2,
      'title_li' => '',
      'echo' => 0
    ), $args));
  }
  
  
  /**
   * Try to intelligently display parent, child, and/or sibling nav
   * Work in progress, aka zero functionality
   * @param int $page_id
   * @param array $args
   * @return string HTML
   */
  public static function getAutoPageNav($page_id=null, $args=array()) {
    if(is_null($page_id)) {
      global $post;
      $page_id = $post->ID;
    }
    
    /*
    get current page
    
    if page has children
      display page and its siblings
      display children
      return
    
    if page has ancestors
      display parent and its siblings
      display parent's children (page siblings)
      return
    
    display page and its siblings
    return
    */
    
    return wp_list_pages(array_merge(array(
      'child_of' => $top_level_page_id,
      'title_li' => '',
      'echo' => 0
    ), $args));
  }
  
  
  /**
   * Get list of links of available taxonomy terms
   * @param string $taxonomy
   * @param string $current_class
   * @param string $link_prefix
   * @param obj $current_term
   * @param obj $current_post
   * @return string HTML
   */
  public static function getTermsNav($taxonomy='category', $current_class='current-term', $link_prefix=null, $current_term=null, $current_post=null) {
    $terms = \Taco\Term\Factory::createMultiple(get_terms($taxonomy));
    if(!Arr::iterable($terms)) return null;
    
    if(!Obj::iterable($current_term)) {
      $current_term = \Taco\Term\Factory::create(get_queried_object());
    }
    
    $term_links = array();
    $current_class = ' class="'.$current_class.'"';
    if(is_null($link_prefix)) $link_prefix = '/'.$taxonomy.'/';
    
    foreach($terms as $term) {
      $term_class = null;
      if(Obj::iterable($current_term)) {
        // We are on a taxonomy/category template
        if((int) $current_term->term_id === (int) $term->term_id) {
          $term_class = $current_class;
        }
      } else {
        // We are on a single page/post
        if(!Obj::iterable($current_post)) {
          global $post;
          $current_post = \Taco\Post\Factory::create($post);
        }
        $current_post_term = reset($current_post->getTerms($taxonomy));
        if((int) $current_post_term->term_id === (int) $term->term_id) {
          $term_class = $current_class;
        }
      }
      $term_links[] = sprintf(
        '<li%s><a href="%s%s/">%s</a></li>',
        $term_class,
        $link_prefix,
        $term->slug,
        $term->name
      );
    }
    
    return sprintf(
      '<ul>
        %s
      </ul>',
      join('', $term_links)
    );
  }
  
  
/**
 * Get pagination
 * $args contains arguments for both wrapper and page numbers
 * @param int $current_page
 * @param int $total_posts
 * @param array $args
 * Wrapper:
 * - string $label
 * - string $container_class
 * Page numbers:
 * - int $max_pages
 * - int $per_page
 * - string $link_prefix
 * - string $previous_label
 * - string $next_label
 * - bool $is_ajax
 * Both wrapper and page numbers:
 * - bool $is_select
 * @return string HTML
 */
  public static function getPagination($current_page, $total_posts, $args=array()) {
    $default_args = array(
      'label' => 'Page:',
      'container_class' => 'page-numbers clearfix',
      'is_select' => false,
    );
    $args = (Arr::iterable($args))
      ? array_merge($default_args, $args)
      : $default_args;
    extract($args);
    
    $label = (strlen($label)) ? '<p>'.$label.'</p>' : null;
    $list_element = ($is_select) ? 'select' : 'ul';
    $pagination = self::getPaginationItems($current_page, $total_posts, $args);
    if(is_null($pagination)) return null;
    
    return sprintf(
      '<div class="%s">
        %s
        <%s>
          %s
        </%s>
      </div>',
      $container_class,
      $label,
      $list_element,
      $pagination,
      $list_element
    );
  }


  /**
   * Get page numbers for pagination, either as links or option elements
   * @param int $current_page
   * @param int $total_posts
   * @param array $args
   * - int $max_pages
   * - int $per_page
   * - string $link_prefix
   * - string $previous_label
   * - string $next_label
   * - bool $is_ajax
   * - bool $is_select
   * @return string HTML
   */
  public static function getPaginationItems($current_page, $total_posts, $args=array()) {
    if(is_null($current_page) || is_null($total_posts)) return null;
    
    $default_args = array(
      'max_pages' => 5,
      'per_page' => 10,
      'link_prefix' => null,
      'previous_label' => '&hellip;',
      'next_label' => '&hellip;',
      'is_ajax' => false,
      'is_select' => false,
    );
    $args = (Arr::iterable($args))
      ? array_merge($default_args, $args)
      : $default_args;
    extract($args);
    
    if($total_posts <= $per_page) return null;
    
    $pagination = array();
    $page_count = ceil($total_posts / $per_page);
    
    if($is_select) {
      $options = array();
      for($i = 1; $i <= $page_count; $i++) {
        $current_page_attr = ($i == $current_page)
          ? ' selected'
          : null;
        $page_number_value = (!$is_ajax)
          ? $link_prefix.$i.'/'
          : $i;
        $options[] = sprintf(
          '<option%s value="%s" data-page="%d">%d</option>',
          $current_page_attr,
          $page_number_value,
          $i,
          $i
        );
      }
      return join("\n", $options);
    }
    
    // Set first and last page numbers to be displayed
    // between "previous" and "next" links
    $first_page_number = ($max_pages < $page_count)
      ? max($current_page - floor($max_pages / 2), 1)
      : 1;
    $last_page_number = min($first_page_number + ($max_pages - 1), $page_count);
    
    // Evaluate first page number again, for when
    // one of the last pages is selected
    $first_page_number = ($max_pages < $page_count)
      ? min($first_page_number, $last_page_number - ($max_pages - 1))
      : $first_page_number;
    
    $link_prefix = null;
    if(!$is_ajax) {
      $link_prefix = $link_prefix.'/page/';
      
      // Remove consecutive slashes
      $link_prefix = preg_replace('/\/+/', '/', $link_prefix);
    }
    
    // Previous link
    if($first_page_number != 1) {
      $previous_link = (!$is_ajax)
        ? $link_prefix.($first_page_number - 1).'/'
        : '#';
      $pagination[] = sprintf(
        '<li data-page="%d"><a href="%s">%s</a></li>',
        ($first_page_number - 1),
        $previous_link,
        $previous_label
      );
    }
    
    // Page number links
    for($i = $first_page_number; $i <= $last_page_number; $i++) {
      $current_page_class = ($i == $current_page)
        ? ' class="on"'
        : null;
      $page_number_link = (!$is_ajax)
        ? $link_prefix.$i.'/'
        : '#';
      $pagination[] = sprintf(
        '<li%s data-page="%d"><a href="%s">%d</a></li>',
        $current_page_class,
        $i,
        $page_number_link,
        $i
      );
    }
    
    // Next link
    if($last_page_number < $page_count) {
      $next_link = (!$is_ajax)
        ? $link_prefix.($last_page_number + 1).'/'
        : '#';
      $pagination[] = sprintf(
        '<li data-page="%d"><a href="%s">%s</a></li>',
        ($last_page_number + 1),
        $next_link,
        $next_label
      );
    }
    
    return join("\n", $pagination);
  }
  
  
  /**
   * Get human-readable date range from two dates
   * @param string $date_start
   * @param string $date_end
   * @param array $args
   * - string $incoming_format
   * - bool $return_short
   * - bool $return_dates_only
   * @return string/array
   */
  public static function getDateRange($date_start, $date_end=null, $args=array()) {
    if(empty($date_start)) return null;
    
    $default_args = array(
      'incoming_format' => 'Y-m-d',
      'return_short' => false,
      'return_dates_only' => true,
    );
    $args = (Arr::iterable($args))
      ? array_merge($default_args, $args)
      : $default_args;
    extract($args);
    
    $date_start = date_format(date_create_from_format($incoming_format, $date_start), 'U');
    if(strlen($date_end)) {
      $date_end = date_format(date_create_from_format($incoming_format, $date_end), 'U');
    }
    
    $date_range = null;
    $is_single_day = false;
    $year = ($return_short) ? '' : ', Y';
    $month = ($return_short) ? 'M ' : 'F ';
    $day = 'j';
    if(
      date('Y-m-d', $date_start) == date('Y-m-d', $date_end)
      || empty($date_end)
    ) {
      $date_range = date($month.$day.$year, $date_start);
      $is_single_day = true;
    } elseif(date('Y-m', $date_start) == date('Y-m', $date_end)) {
      $date_range = date($month.$day, $date_start).'&#8211;'.date($day.$year, $date_end);
    } elseif(date('Y', $date_start) == date('Y', $date_end)) {
      $date_range = date($month.$day, $date_start).' &#8211; '.date($month.$day.$year, $date_end);
    } else {
      $date_range = date($month.$day.$year, $date_start).' &#8211; '.date($month.$day.$year, $date_end);
    }
    
    return ($return_dates_only)
      ? $date_range
      : array(
          'dates' => $date_range,
          'is_single_day' => $is_single_day,
        );
  }
  
  
  /**
   * Get human-readable time range from two times
   * Expects 24-hour input time without seconds
   * @param string $time_start
   * @param string $time_end
   * @param bool $return_short
   * @return string
   */
  public static function getTimeRange($time_start, $time_end=null, $return_short=false) {
    
    // Clean up
    if($time_start == $time_end) $time_end = null;
    $times = array(
      'start' => array('time' => $time_start),
      'end' => array('time' => $time_end),
    );
    foreach($times as &$time) {
      $time['time'] = trim($time['time']);
      $time = array_filter($time);
      unset($time);
    }
    $times = array_filter($times);
    if(!Arr::iterable($times)) return null;
    
    // Enforce 24-hour time format
    $time_pattern = '/^(0?[0-9]|1[0-9]|2[0-3])\:([0-5][0-9])$/';
    foreach($times as $time) {
      if(!preg_match($time_pattern, $time['time'])) return null;
    }
    
    // Determine period and adjust hour accordingly
    foreach($times as &$time) {
      $the_time =& $time['time'];
      $hour = (int) substr($the_time, 0, strpos($the_time, ':'));
      $time['period'] = ($hour < 12) ? 'am' : 'pm';
      if($hour > 12) {
        $hour -= 12;
        $the_time = $hour.substr($the_time, strpos($the_time, ':'));
      }
      $search_zero = array('/^0{2}/', '/^0/');
      $replace_zero = array('12', '');
      $the_time = preg_replace($search_zero, $replace_zero, $the_time);
      unset($time);
    }
    
    // Shorten format to something like 6:30-10p
    if($return_short) {
      foreach($times as &$time) {
        $the_time =& $time['time'];
        if(strpos($the_time, ':00') !== false) {
          $hour = (int) substr($the_time, 0, strpos($the_time, ':'));
          $the_time = $hour;
        }
        $time['period'] = substr($time['period'], 0, 1);
        unset($time);
      }
    }
    
    $thin_space = '&#8201;';
    $six_per_em_space = '&#8198;';
    $range_separator = $six_per_em_space.'&#8211;'.$six_per_em_space;
    
    // Consolidate times and periods
    if($times['start']['period'] == $times['end']['period']) {
      $times['start']['period'] = null;
    }
    foreach($times as &$time) {
      if(!is_null($time['period'])) {
        $time = $time['time'].$thin_space.$time['period'];
      } else {
        $time = $time['time'];
      }
      unset($time);
    }
    
    return join($range_separator, $times);
  }
  
  
  /**
   * Get the column class from a staggered index
   * Alternating wide and narrow grid items
   * @param int $staggered_index
   * @param string $wide_class
   * @param string $narrow_class
   * @return string
   */
  public static function getColumnClassFromStaggeredIndex($staggered_index, $wide_class='medium-6 average-8', $narrow_class='medium-6 average-4') {
    if(is_null($staggered_index)) return null;
    
    $is_first_in_odd_row = (
      $staggered_index % 2 === 0
      && floor($staggered_index / 2) % 2 === 0
    );
    $is_second_in_even_row = (
      $staggered_index % 2 !== 0
      && floor($staggered_index / 2) % 2 !== 0
    );
    
    if($is_first_in_odd_row || $is_second_in_even_row) {
      return $wide_class;
    }
    return $narrow_class;
  }
  
  
  /**
   * Split array into rendered columns
   * @param array $array
   * @param int $num_columns
   * @param string $columns_class
   * @return string
   */
  public static function splitIntoColumns($array, $num_columns=2, $columns_class='small-6') {
    if(!Arr::iterable($array)) return null;
    
    $columns = array();
    $groups = Arr::apportion($array, $num_columns);
    foreach($groups as $group) {
      $group_items = array();
      foreach($group as $item) {
        $group_items[] = '<li>'.$item.'</li>';
      }
      $columns[] = sprintf(
        '<div class="columns %s">
          <ul>
            %s
          </ul>
        </div>',
        $columns_class,
        join("\n", $group_items)
      );
    }
    return sprintf(
      '<div class="row">
        %s
      </div>',
      join("\n", $columns)
    );
  }
  
  
  /**
   * Split array into rendered columns
   * @param array $array
   * @param int $num_columns
   * @param string $columns_class
   * @param bool $use_full_row
   * @param string $insert_between
   * @param int $steal
   * @return string
   */
  public static function splitIntoColumnsOriginal($array, $num_columns=2, $columns_class='small-6', $use_full_row=true, $insert_between=null, $steal=0) {
    if(!Arr::iterable($array)) return null;
    
    $columns = array();
    while($num_columns > 0) {
      $items_per_column = ceil(count($array) / $num_columns);
      $last_column_class = ($num_columns === 1 && !$use_full_row)
        ? ' end'
        : null;
      $items_per_column_adjusted = ($num_columns === 1)
        ? count($array)
        : $items_per_column - $steal;
      
      $group_items = array();
      for($i = 0; $i < $items_per_column_adjusted; $i++) {
        $item = array_shift($array);
        $group_items[] = '<li>'.$item.'</li>';
      }
      
      $columns[] = sprintf(
        '<div class="columns %s%s">
          <ul class="clearfix">
            %s
          </ul>
          %s
        </div>',
        $columns_class,
        $last_column_class,
        join("\n", $group_items),
        ($num_columns !== 1) ? $insert_between : null
      );
      
      $num_columns--;
    }
    
    return sprintf(
      '<div class="row">
        %s
      </div>',
      join("\n", $columns)
    );
  }
  
  
  /**
   * Get breadcrumbs HTML
   * @param \Taco\Post $post_obj
   * @return string HTML
   */
  public static function getBreadcrumbs(\Taco\Post $post_obj) {
    $separator = ' &raquo; ';
    $post_id = $post_obj->get('ID');
    $post_type = $post_obj->getPostType();
    $ancestor_links = array();
    $post_title = null;
    
    if(is_archive()) {
      // This is the blog archive, what to do for breadcrumbs here?
      return null;
    }
    if($post_type == 'page') {
      $post_title = $post_obj->getTheTitle();
      $ancestors = get_post_ancestors($post_id);
      if(Arr::iterable($ancestors)) {
        $ancestors = array_reverse($ancestors);
        foreach($ancestors as $ancestor_post_id) {
          $ancestor = \Taco\Post\Factory::create($ancestor_post_id, false);
          $single_post = get_post($post_id);
          $ancestor_links[] = sprintf(
            '<li><a href="%s">%s</a></li>',
            $ancestor->getPermalink(),
            $ancestor->getTheTitle()
          );
        }
      }
    } elseif($post_type == 'article') {
      $ancestor_links[] = '<li><a href="'.URL_NEWS.'">News</a></li>';
      $topics = $post_obj->getTerms('topic');
      if(Arr::iterable($topics)) {
        $topic = reset($topics);
        $ancestor_links[] = sprintf(
          '<li><a href="%s">%s</a></li>',
          $topic->getPermalink(),
          $topic->get('name')
        );
      }
    }
    
    // Don't display breadcrumbs unless there's at least one ancestor
    if(!Arr::iterable($ancestor_links)) return null;
    
    return sprintf(
      '<ul class="bread-crumbs">
        %s
        <li>%s</li>
      </ul>',
      join('', $ancestor_links),
      $post_title
    );
  }
  
  
  /**
   * Prevent short words from being displayed alone on the last line
   * @param string $text
   * @param int $min_last_word_length
   * @return string HTML
   */
  public static function adoptOrphans($text, $min_last_word_length=8) {
    $text = AppStr::cleanSpaces($text);
    if(strlen($text) <= $min_last_word_length) return $text;
    
    $all_words = explode(' ', $text);
    $word_count = count($all_words);
    for($i = 0; $i < $word_count; $i++) {
      $word = $all_words[$i];
      if(strpos($word, '-') === false) continue;
      
      // If any hyphenated word starts or ends with a one- or two-letter
      // segment, replace hyphens with non-breaking hyphens
      $word_segments = explode('-', $word);
      if(
        strlen(current($word_segments)) <= 2
        || strlen(end($word_segments)) <= 2
      ) {
        array_splice($all_words, $i, 1, str_replace('-', '&#8209;', $word));
      }
    }
    
    $all_words = join(' ', $all_words);
    $new_text = substr($all_words, 0, strlen($all_words) - $min_last_word_length);
    $new_text .= str_replace(' ', '&nbsp;', substr($all_words, strlen($all_words) - $min_last_word_length));
    
    return $new_text;
  }
  
}