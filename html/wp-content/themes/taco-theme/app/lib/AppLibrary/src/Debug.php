<?php

namespace AppLibrary;

class Debug {

  public static function varDumpy($var, $return=false, $return_array=false, $breadcrumbs='', $level=0) {
    // If $var isn't iterable just do a plain old var_dump
    $is_function_var = (is_object($var) && is_callable($var));
    $is_object_var = (is_object($var) && !is_callable($var));
    $is_iterable = ((is_array($var) || $is_object_var) && count($var) > 0);
    if(!$is_iterable) {
      if($return) ob_start();
      var_dump($var);
      return trim(ob_get_clean());
    }
    
    // Config
    $indent = str_repeat(' ', 2);
    $equals = ' = ';
    $var_name = '$var';
    
    // Collect output into an array
    $out = array();
    
    // Start output
    if($level === 0) $prefix = $var_name . $equals;
    else $prefix = str_repeat($indent, $level) . $var_name . $breadcrumbs . $equals;
    
    if(is_array($var)) $out[] = $prefix . sprintf('%s(%s) {', gettype($var), count($var));
    elseif(is_object($var)) $out[] = $prefix . sprintf('%s(%s) {', get_class($var), count($var));
    
    // Nested output
    $original_breadcrumbs = $breadcrumbs;
    foreach($var as $k=>$v) {
      $line = null;
      
      // Breadcrumbs
      if(is_array($var)) $breadcrumbs .= sprintf('[%s]', (is_string($k)) ? sprintf('"%s"', $k) : $k);
      elseif(is_object($var)) $breadcrumbs .= sprintf('->%s', $k);
      
      // Display prefix and values based on type
      $is_function_v = (is_object($v) && is_callable($v));
      $is_object_v = (is_object($v) && !is_callable($v));
      $is_iterable_v = ((is_array($v) || $is_object_v) && count($v) > 0);
      if($is_function_v) {
        ob_start();
        var_dump($v);
        $line = ob_get_clean();
      }
      elseif($is_iterable_v) $out = array_merge($out, var_dumpy($v, true, true, $breadcrumbs, $level+1));
      elseif(is_array($v))  $line = sprintf('%s(%s) { }', 'array', count($v));
      elseif(is_object($v)) $line = sprintf('%s(%s) { }', get_class($v), count($v));
      elseif(is_string($v)) $line = sprintf('%s(%s) "%s"', 'string', strlen($v), $v);
      elseif(is_int($v))    $line = sprintf('%s(%s)', 'int', $v);
      elseif(is_null($v))   $line = 'NULL';
      elseif(is_bool($v))   $line = sprintf('%s(%s)', 'bool', ($v) ? 'true' : 'false');
      else                  $line = sprintf('%s(%s)', gettype($v), $v);
      
      // Add line
      $prefix = str_repeat($indent, $level+1) . $var_name . $breadcrumbs . $equals;
      if($line) $out[] = $prefix . preg_replace('/\{[\r\n\s\t]+\}/s', '{ }', $line);
      
      // Reset the breadcrumbs
      $breadcrumbs = $original_breadcrumbs;
    }
    
    // Close outer open curly braces
    if(is_array($var) || $is_object_var) $out[] = str_repeat($indent, $level) . '}';
    
    if($return) return ($return_array) ? $out : join("\n", $out);
    echo join("\n", $out);

  }
}