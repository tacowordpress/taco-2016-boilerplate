<?php

Class FormTemplate extends \AppLibrary\QuickTemplate {
  
  /**
   * Uses output buffer in callback function to return a string of new values
   * @param array $data
   * @param $callback callable
   * @param $return_content bool|output buffer (returned by reference)
   * @param $object object (pass the form object into the custom template)
   * @return string
   */
  public static function create($data, $callback, &$return_content=false, $object=null) {
    ob_start(function($buffer) use ($data, &$return_content) {
      $self = __CLASS__;
      $updated = $self::template_iterator($data, $buffer);
      if(is_null($return_content)) {
        $return_content = $updated;
      } else {
        return $updated;
      }
    });
    $callback($object);
    ob_end_flush();
  }

}