<?php

trait FormValidators {
  public static function checkRequired($value, $property_value=null) {
    if(empty($value)) {
      return true;
    }
    return false;
  }

  public static function checkEmail($value, $property_value=null) {
    if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
      return true;
    }
    return false;
  }

  public static function checkURL($value, $property_value=null) {
    if(!strlen($value)) return true;
    if(!filter_var($value, FILTER_VALIDATE_URL)) {
      return false;
    }
    return true;
  }

  public static function checkMaxLength($value, $property_value=null) {
    if(strlen($value) > $property_value) return true;
    return false;
  }

  public static function checkZip($value, $property_value=null) {
    $is_zip_regex = '^\d{5}([\-]?\d{4})?$';
    if(!preg_match("/".$is_zip_regex."/i", $value)) {
      return true;
    }
    return false;
  }
}