<?php

namespace AppLibrary;

class AppStr {
  
  public static function pascal($str) {
    $words = preg_split('/[\W_]/', strtolower($str));
    $words = array_filter(array_map('ucfirst', $words));
    return join('', $words);
  }
  
  
  public static function machineToCamel($input) {
    $separators = preg_replace('/[^_-]/', '', $input);
    if(!strlen($separators)) {
      return ucfirst($input);
    }
    $split = explode(substr($separators, 0, 1), $input);
    $split = array_map('ucfirst', $split);
    return join('', $split);
  }


  /**
   * Get an array of the shortest or most common stop words
   * Stop words are words which are filtered out prior to, or after, processing of natural language data
   * This has absolutely nothing to do with capitalization
   * @link http://www.textfixer.com/resources/common-english-words.txt
   * @return array
   */
  public static function shortStopWords() {
    return ['a', 'am', 'an', 'and', 'are', 'as', 'at', 'be', 'but', 'by', 'for', 'from', 'i', 'if', 'in', 'is', 'it', 'its', 'me', 'no', 'not', 'of', 'on', 'or', 'so', 'the', 'to', 'us', 'was', 'we', 'who', 'with'];
  }


  /**
   * Replace non-breaking spaces with regular spaces
   * @param string $input
   * @return string
   */
  public static function clean($input) {
    return Str::machine($input, ' ');
  }
  

  /**
   * Replace non-breaking spaces with regular spaces
   * @param string $input
   * @return string
   */
  public static function cleanSpaces($input) {
    // The third item in $search_spaces is a pasted non-breaking space
    $search_spaces = ['<p>&nbsp;</p>', '&nbsp;', ' '];
    $replace_spaces = ['', ' ', ' '];
    $out = str_replace($search_spaces, $replace_spaces, $input);
    $out = trim(preg_replace('/\s+/', ' ', $out));
    return $out;
  }
  
  
  /**
   * Get valid email addresses from input, separating multiple addresses
   * @param string $email
   * @param int $max_number
   * @return array
   */
  public static function validateEmails($email, $max_number=null) {
    $email = preg_split('/[^\w@\.-]/', $email);
    $email = array_map(function($el){
      $el = trim($el);
      if(!filter_var($el, FILTER_VALIDATE_EMAIL)) return false;
      return $el;
    }, $email);
    $email = array_filter($email);
    if(!is_null($max_number) && count($email) > $max_number) {
      return false;
    }
    return $email;
  }
  
}