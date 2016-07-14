<?php

namespace AppLibrary;

class AppArr {
  
  /**
   * Wrap each element in quotes
   * @param array $array
   * @return array
   */
  public static function quote($array) {
    return array_map(function($el){
      if(is_array($el)) {
        $el = join(',', $el);
      }
      return "'".$el."'";
    }, $array);
  }
  
  
  /**
   * Convert values to machine format
   * @param array $array
   * @param string $separator
   * @return array
   */
  public static function machine($array, $separator='_') {
    $array = array_map(function($el) use ($separator){
      return Str::machine($el, $separator);
    }, $array);
    return $array;
  }
  
  
  /**
   * Convert keys to machine format
   * @param array $array
   * @param string $separator
   * @return array
   */
  public static function machineKeys($array, $separator='_') {
    $keys = array_keys($array);
    $keys = array_map(function($el) use ($separator){
      return Str::machine($el, $separator);
    }, $keys);
    return array_combine($keys, $array);
  }
  
}