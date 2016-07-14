<?php

class Page extends \Taco\Post {

  public $loaded_post = null;
  
  public function getFields() {
    $this->loadPost();
    return array_merge(
      $this->getDefaultFields(),
      $this->getFieldsByPageTemplate(
        get_page_template_slug($this->loaded_post->ID)
      )
    );
  }

  public function getDefaultFields() {
    return array(
      'other_pages' => array(
        'type' => 'text',
        'class' => 'addbysearch',
        'data-post-type' => 'Page')
    );
  }
  
  public function getFieldsByPageTemplate($template_file_name) {
    $template_fields = [];
    
    if($template_file_name === 'tmpl-example.php') {
      $template_fields = array_merge($template_fields, [
        'field_name' => [
          'type' => 'text',
          'description' => '',
        ],
      ]);
    }
    
    return $template_fields;
  }
  
  public function loadPost() {
    // For some reason, the global post var cannot be accessed at this state. So we have to load it manually
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
}