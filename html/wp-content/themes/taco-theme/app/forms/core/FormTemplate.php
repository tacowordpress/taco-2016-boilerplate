<?php

Class FormTemplate extends \AppLibrary\QuickTemplate {
  
  /**
   * Uses output buffer in callback function to return a string of new values
   * @param array $data
   * @param function $callback
   * @param bool $return_content (returned by reference)
   * @param object $object (pass the form object into the custom template)
   * @return string
   */
  public static function create($data, $callback, $template=null, &$return_content=false, $object=null) {
    ob_start(function($buffer) use ($data, &$return_content) {
      $self = __CLASS__;
      $updated = $self::template_iterator($data, $buffer);
      
      if(is_null($return_content)) {
        $return_content = $updated;
      } else {
        return $updated;
      }
    });
    if($template) {
      echo $template;
    }
    $callback($object);
    ob_end_flush();
  }

}