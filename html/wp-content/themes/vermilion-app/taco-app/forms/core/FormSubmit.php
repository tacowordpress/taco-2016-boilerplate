<?php

// a class for processing form submissions specfically for Taco WordPress

include getenv('HTTP_BOOTSTRAP_WP'); // bootstrap WordPress

if(array_key_exists('taco-form-submission', $_POST)
  && $_POST['taco-form-submission'] == true) {
  FormSubmit::processSubmission();
}


class FormSubmit {
    
  public static $record = null;


  /**
   * redirect to the referring page
   * Used to display form errors
   * @param array $data
   */
  public static function redirectToReferringPage() {
    $uri = (array_key_exists('HTTP_REFERER', $_SERVER))
      ? $_SERVER['HTTP_REFERER']
      : '/';
    header('Location: '.$uri);
    exit;
  }


  /**
   * process the form and redirect
   * @return callable (redirect method)
   */
  public static function processSubmission() {
    if(!self::checkSubmission()) {
      return self::redirectToReferringPage();
    }
    return self::redirectAfterSuccess();
  }


  /**
   * redirect after a successful form submission
   * @return callable (redirect method)
   */
  public static function redirectAfterSuccess() {
    $record = self::$record;
    $redirect_url = method_exists($record, 'getURLAfterSuccess')
      ? $record->getURLAfterSuccess()
      : $record->getPermalink();
    
    if(!$redirect_url) $redirect_url = '/';

    header(sprintf('Location: %s', $redirect_url));
    exit;
  }


  /**
   * check if the class exists in the $_POST and if it's a Taco class
   * @param $source array
   * @return boolean
   */
  public static function isTaco($source) {
    if(!array_key_exists('class', $source)) return false;
    if(!class_exists($source['class'])) return false;
    if(!is_subclass_of($source['class'], '\Taco\Post')) return false;
    return true;
  }


  /**
   * do all the validation
   * @param  $record object
   * @return boolean
   */
  public static function checkSubmission() {
    // Toggle to $_GET for easy debugging locally
    $source = $_POST;
    // Verify we're using a valid Taco class
    if(!self::isTaco($source)) return false;

    $record = new $source['class'];
    self::$record = $record;

    // Is this an update and not a first time save
    if(array_key_exists('ID', $source)) {
      $record = $record::find($source['ID']);
    }

    // Is the nonce valid
    if(!self::isNonceValid($source, $record)) return false;

    // correlate the entry with the form configuration if there is one
    if(array_key_exists('form_config', $source)){
      $record->form_config_id = $source['form_config'];
    }
    
    $validated = self::validateInput($record, $source);
    if(!$validated) return false;
    $record_info = $validated;
    
    // Assign info and save
    $record->assign($record_info);
    if(!$record->save()) {
      return false;
    }
    return true;
  }


  /**
   * validate the fields comparing the source the Taco object
   * @param  $record object
   * @param  $source array
   * @return boolean or array
   */
  public static function validateInput($record, $source) {
    $fields = $record->getFields();
    $record_info = array();
    foreach($fields as $k => $field) {
      if(!array_key_exists($k, $source)) continue;
      $record_info[$k] = $source[$k];
    }
    // Validate user input
    if(!$record->isValid($record_info)) {
      return false;
    }
    return $record_info;
  }


  /**
   * is the nonce valid
   * @param  $source array
   * @param  $record object
   * @return boolean
   */
  public static function isNonceValid($source, $record) {
    $reflection = new ReflectionObject($record);
    // Verify the WP nonce for CSRF protection
    $nonce = (array_key_exists($reflection->getConstant('KEY_NONCE'), $source))
      ? $source[$reflection->getConstant('KEY_NONCE')]
      : null;

    if(!$record->verifyNonce($nonce)) {
      return false;
    }

    return true;
  }
}