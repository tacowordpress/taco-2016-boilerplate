<?php

trait FormChecker {
  public function doesRequiredFieldHaveValue($value, $requirement, $key) {
    if($requirement === 'required') {
      if(empty($value)) {
        array_push(
          $this->error_messages,
          $this->makeErrorMessage('required', $key)
        );
        return false;
      }
    }
    return true;
  }

  public function makeErrorMessage($type, $key) {
    $errors = self::getErrorMessages();

    if($type == 'required') {
      return sprintf($errors['required'], Str::human($key));
    }
    if($type == 'email') {
      return sprintf($errors['email'], Str::human($key));
    }
    if($type == 'us_zip') {
      return sprintf($errors['us_zip'], Str::human($key));
    }
    if($type == 'url') {
      return sprintf($errors['url'], Str::human($key));
    }
  }

  public function isEmailFieldValid($value, $requirement, $key) {
    if(!filter_var($value, FILTER_VALIDATE_EMAIL)
      && $requirement === 'email') {
      array_push(
        $this->error_messages,
        $this->makeErrorMessage('email', $key)
      );
      return false;
    }
    return true;
  }

  public function isURLFieldValid($value, $requirement, $key) {
    if(!strlen($value)) return true;
    if(!filter_var($value, FILTER_VALIDATE_URL)
      && $requirement === 'url') {
      array_push(
        $this->error_messages,
        $this->makeErrorMessage('url', $key)
      );
      return false;
    }
    return true;
  }

  public function isUSZipFieldValid($value, $requirement, $key) {
    $is_zip_regex = '^\d{5}([\-]?\d{4})?$';
    if(!preg_match("/".$is_zip_regex."/i", $value) && $requirement === 'us_zip') {
      array_push(
        $this->error_messages,
        $this->makeErrorMessage('us_zip', $key)
      );
      return false;
    }
    return true;
  }

  public function checkValidFilters($value, $req, $key) {
    $is_valid = true;
    if($req === 'required') {
      $is_valid = $this->doesRequiredFieldHaveValue($value, $req, $key);
    }
    if($req === 'email') {
      $is_valid = $this->isEmailFieldValid($value, $req, $key);
    }
    if($req === 'us_zip') {
      $is_valid = $this->isUSZipFieldValid($value, $req, $key);
    }
    if($req === 'url') {
      $is_valid = $this->isURLFieldValid($value, $req, $key);
    }
    return $is_valid;
  }

  public function isValid($fields) {
    $this->error_messages = array();
    $is_valid = true;
    $field_requirements = self::getFieldRequirements();
    foreach($field_requirements as $k => $requirements) {
      foreach($requirements as $req) {
        if(!$this->checkValidFilters($fields[$k], $req, $k)) {
          $is_valid = false;
        }
      }
    }
    if(!$is_valid) {
      http_response_code(400);
      echo json_encode(
        array(
          'error' => true,
          'message' => $this->error_messages
        )
      );
      exit;
    }
    return true;
  }
}