<?php

trait FormValidators {
  public function doesRequiredFieldHaveValue($value, $requirement) {
    if($requirement === 'required') {
      if(empty($value)) {
        return false;
      }
    }
    return true;
  }

  public function isEmailFieldValid($value, $requirement) {
    if(!filter_var($value, FILTER_VALIDATE_EMAIL)
      && $requirement === 'email') {
      return false;
    }
    return true;
  }

  public function isURLFieldValid($value, $requirement) {
    if(!strlen($value)) return true;
    if(!filter_var($value, FILTER_VALIDATE_URL)
      && $requirement === 'url') {
      return false;
    }
    return true;
  }

  public function isUSZipFieldValid($value, $requirement) {
    $is_zip_regex = '^\d{5}([\-]?\d{4})?$';
    if(!preg_match("/".$is_zip_regex."/i", $value) && $requirement === 'us_zip') {
      return false;
    }
    return true;
  }
}