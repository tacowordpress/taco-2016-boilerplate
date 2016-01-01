<?php

// An API for the FormConf class

add_action('init', 'TacoForm::getSessionData');

class TacoForm {
  use FormValidators;

  private $settings = [];
  
  public static $invalid = false;
  public static $success = false;
  public static $session_field_errors = array();
  public $fields = null;
  public $template_html = null;
  public $conf_instance = null;
  public $conf_machine_name = null;
  public $conf_ID = null;

  public static $messages_reference = array(
    'form_conf_invalid',
    'form_conf_success'
  );

  /**
   * creates a new TacoForm and associated configuration
   * @param  $args array
   * @param  $template_callback callable
   * @return TacoFrom object
   */
  public function __construct($args, $template_callback=null) {

    $defaults = array(
      'conf_name' => '',
      'fields' => array(),
      'css_class' => '',
      'id' => '',
      'method' => 'post',
      'action' => null,
      'novalidate' => false,
      'use_ajax' => false,
      'hide_labels' => false,
      'column_classes' => 'small-12 columns',
      'exclude_post_content' => false,
      'use_cache' => false,
      'cache_expiration' => null,
      'submit_button_text' => 'submit',
      'success_message' => null,
      'error_message' => null,
      'success_redirect_url' => null
    );

    // we need this to uniquely identify the form conf that will get created or loaded
    if(!(array_key_exists('conf_name', $args) && strlen($args['conf_name']))) {
      throw new Exception('conf_name must be defined in the args array');
      exit;
    }


    $db_conf = $this->findFormConfigInstance($args['conf_name']);
    if($db_conf) {
      $this->conf_instance = $db_conf;
    } else {
      $this->conf_instance = new FormConfig;
    }

    $conf_fields = $this->conf_instance->getFields();

    // load global defaults
    $global_defaults = include __DIR__.'/../forms-defaults.php';

    // get the default form action
    $defaults['action'] = array_key_exists('form_action', $global_defaults)
      ? $global_defaults['form_action']
      : null;

    // assign only the fields specified above and in the form conf
    foreach($args as $k => $v) {
      if($k === 'fields') {
        $this->fields = $v;
        continue;
      }
      if(array_key_exists($k, $conf_fields)) {
        $this->conf_instance->set($args[$k], $args[$k]);
      }
      if(array_key_exists($k, $defaults)) {
        $this->settings[$k] = $args[$k];
      }
    }

    
    // --- messages ---
    
    // first get global default messages
    $defaults['success_message'] = $global_defaults['success_message'];
    $defaults['error_message'] = $global_defaults['error_message'];

    // second get developer's hardcoded per form settings from $args
    if(array_key_exists('success_message', $this->settings)) {
      $defaults['success_message'] = $this->settings['success_message'];
    }
    if(array_key_exists('error_message', $this->settings)) {
      $defaults['error_message'] = $this->settings['error_message'];
    }

    // lastly use the WordPress admin's message settings
    if(strlen($this->conf_instance->get('form_success_message'))) {
      $this->settings['success_message'] = $this->conf_instance->get(
        'form_success_message'
      );
    }
    if(strlen($this->conf_instance->get('form_error_message'))) {
      $this->settings['error_message'] = $this->conf_instance->get(
        'form_error_message'
      );
    }

    // merge default settings with user settings
    $this->settings = array_merge(
      $defaults,
      $this->settings
    );
    
    // assign post title to instance
    $this->conf_instance->set('post_title', $this->get('conf_name'));
    $this->conf_instance->set('fields', serialize($this->fields));

    // assign conf machine name
    $this->conf_machine_name = \AppLibrary\Str::machine($this->get('conf_name'), '-');
    $this->conf_instance->set('post_name', $this->conf_machine_name);

    // assign redirect url from dev's settings
    // if the admin adds this value, it will need to be overridden from wp-admin
    if(!strlen($this->conf_instance->get('success_redirect_url'))) {
      $this->conf_instance->set(
        'success_redirect_url',
        $this->settings['success_redirect_url']
      );
    }
    
    // if the entry doesn't exist create it in the db
    $this->conf_ID = $this->conf_instance->save();
    // get the updated form conf after save
    $this->conf_instance = FormConfig::find($this->conf_ID);

    return $this;
  }


  /**
   * get property values from the settings array
   * @return string
   */
  public function __get($key) {
    return $this->get($key);
  }


  /**
   * get property values from the settings array
   * @return string
   */
  public function get($key) {
    if(array_key_exists($key, $this->settings)) {
      return $this->settings[$key];
    }
    return;
  }


  /**
   * find a form conf taco object in the db
   * @return $this
   */
  private function findFormConfigInstance($conf_name) {
    $db_instance = FormConfig::getOneBy(
      'post_name', \AppLibrary\Str::machine($conf_name, '-')
    );
    if(\AppLibrary\Obj::iterable($db_instance)) {
      return $db_instance;
    }
    return false;
  }


  /**
   * renders the form head
   * @return string html
   */
  public function render() {
    $html = [];
    $html[] = $this->renderFormHead();
    $html[] = $this->renderFormFields();
    $html[] = $this->renderFormFooter();
    return join('', $html);
  }


  /**
   * renders the form head
   * @return string html
   */
  public function renderFormHead() {

    // start the form html using an array
    $html = [];
    $html[] = sprintf(
      '<form action="%s" method="%s" class="%s" id="%s" data-use-ajax="%s" %s>',
      $this->settings['action'],
      $this->settings['method'],
      $this->settings['css_class']. ' taco-forms',
      $this->settings['id'],
      $this->settings['use_ajax'],
      ($this->settings['novalidate']) ? 'novalidate' : ''
    );

    // get neccessary fields CSRF protection
    $form_entry_helper = new FormEntry;
    $html[] = $form_entry_helper->getRenderPublicField('nonce');
    $html[] = $form_entry_helper->getRenderPublicField('class');
    $html[] = sprintf(
      '<input name="form_config" type="hidden" value="%d">',
      $this->conf_ID
    );
    $html[] = '<input name="taco-form-submission" type="hidden" value="true>';

    if($this->settings['use_ajax']) {
      $html[] = '<input type="hidden" name="use_ajax" value="1">';
    }

    // wrap with row and columns (foundation)
    if(!$this->settings['exclude_post_content']) {
      $html[] = self::rowColumnWrap(
        $this->conf_instance->getTheContent(),
        $this->settings['column_classes']
      );
    }

    //get form messages
    $messages = [];
    // check for success and error message overrides
    if(strlen($this->get('success_message'))) {
      $messages['success_message'] = $this->get('success_message');
    }
    if(strlen($this->get('error_message'))) {
      $messages['error_message'] = $this->get('error_message');
    }

    $html[] = self::rowColumnWrap(
      $this->getFormMessages($messages),
      $this->settings['column_classes'].' form-messages'
    );
    return join('', $html);
  }


  /**
   * renders the form fields
   * @return string html
   */
  public function renderFormFields() {
    
    $html = [];
    foreach($this->fields as $k => $v) {
      
      if(array_key_exists('id', $v)) {
        $id = $v['id'];
      } else {
        $id = \AppLibrary\Str::machine($k, '-');
        $v['id'] = $id;
      }

      $hidden_class = ($this->hide_labels)
        ? 'hide_label'
        : '';
      
      // does this field have an error
      $has_error = self::hasError($k);
      $error_columns_class = ($has_error)
        ? 'small-12 columns taco-field-error' :
        'small-12 columns';

      if(array_key_exists('type', $v) && $v['type'] === 'checkbox') {
        $html[] = self::rowColumnWrap(
          $this->renderCheckBox($k, $v),
          $error_columns_class
        );
        continue;
      }

      $label = sprintf(
        '<label for="%s" class="%s">%s</label>',
        $id,
        $hidden_class,
        \AppLibrary\Str::human($k)
      );

      if($this->get('hide_labels')
        && !array_key_exists('placeholder', $v)) {
        $v['placeholder'] = \AppLibrary\Str::human($k);
      }
      
      $html[] = self::rowColumnWrap(
        self::renderFieldErrors($k)
        .' '.$label.' '
        .$this->conf_instance->getRenderPublicField($k, $v),
        $error_columns_class
      );
    }
    return join('', $html);
  }


  /**
   * renders a field's errors inline
   * @param $key string
   * @return string html
   */
  public function renderFieldErrors($key) {
    if(array_key_exists($key, self::$session_field_errors)) {
      return sprintf(
        '<span class="taco-field-error-message">%s</span>',
        self::$session_field_errors[$key]
      );
    }
    return '';
  }


  /**
   * does a field error exist
   * @param $key string
   * @return boolean
   */
  public function hasError($key) {
    if(array_key_exists($key, self::$session_field_errors)) {
      return true;
    }
    return false;
  }


  /**
   * renders a checkbox with label wraped around it
   * @param $key string
   * @param $value array
   * @return string html
   */
  public function renderCheckBox($key, $value) {
    $html = [];
    $html[] = sprintf(
      '<label for="%s">%s %s</label>',
      \AppLibrary\Str::machine($key, '-'),
      $this->conf_instance->getRenderPublicField($key, $value),
      \AppLibrary\Str::human($key)
    );
    return join('', $html);
  }


  /**
   * renders the form footer (includes submit button)
   * @return string html
   */
  public function renderFormFooter() {
    $html = [];
    $html[] = self::rowColumnWrap(sprintf(
      '<button type="submit">%s</button>',
      $this->get('submit_button_text')
    ));
    $html[] = self::rowColumnWrap(
      sprintf('<a href="/wp-admin/post.php?post=%d&action=edit">Edit</a>',
        $this->conf_instance->ID
      )
    );
    return join('', $html);
  }


  /**
   * gets any messages for the form like general errors or success messages
   * @return string html
   */
  public function getFormMessages($messages) {
    if(self::$invalid) {
      if(array_key_exists('error_message', $messages)
        && strlen($messages['error_message'])) {
        return $messages['error_message'];
      }
      return $this->get('form_error_message');
    }
    if(self::$success) {
      if(array_key_exists('success_message', $messages)
        && strlen($messages['success_message'])) {
        return $messages['success_message'];
      }
      return $this->get('form_success_message');
    }
  }


  /**
   * wraps a string in a foundation row + columns
   * @param $field string
   * @param $column_classes string of the classes that can be passed in
   * @return string html
   */
  public static function rowColumnWrap($field, $column_classes='small-12 columns') {
    return sprintf(
      '<div class="row"><div class="%s">%s</div></div>',
      $column_classes,
      $field
    );
  }


  /**
   * validate the form
   * @return boolean
   */
  public static function validate($source_fields, $form_config) {
    if(array_key_exists('form_config', $source_fields)) {
      unset($source_fields['form_config']);
    }
    $invalid_array = [];
    $fields = unserialize(unserialize($form_config->get('fields')));

    foreach($fields as $k => $v) {
      $validation_types  = [];

      if(array_key_exists($k, $source_fields)) {
        $source_value = $source_fields[$k];
      } else {
        $source_value = null;
      }

      // $validation_types[string] where string is the method name
      // of the trait method in FormValidators.php
      if(array_key_exists('required', $v)) {
        $validation_types['checkRequired'] = true;
      }
      if(array_key_exists('type', $v) && $v['type'] === 'email') {
        $validation_types['checkEmail'] = 1;
      }
      if(array_key_exists('type', $v) && $v['type'] === 'url') {
        $validation_types['checkURL'] = 1;
      }
      if(array_key_exists('maxlength', $v)) {
        $validation_types['checkMaxLength'] = $v['maxlength'];
      }
      if(\AppLibrary\Arr::iterable($validation_types)) {
        list($invalid, $errors) = self::validateFieldRequirements(
          $validation_types,
          $source_value,
          $k
        );
        if($invalid) {
          $invalid_array[] = true;
        }
        self::pushErrors($k, join(', ', $errors)); // field key, $errors
        unset($errors);
        unset($validation_types);
      }
    }
    if(in_array(true, $invalid_array)) {
      self::setInvalid();
    }
    self::setSuccess();
    return true;
  }


  /**
   * push errors
   * @param $key string
   * @param $errors array
   * @return void
   */
  public static function pushErrors($key, $errors) {
    session_start();
    if(is_array($errors)) {
      $errors = join(', ', $errors);
    }
    if(!array_key_exists('session_field_errors', $_SESSION)) {
      $_SESSION['session_field_errors'] = [];
    }
    $_SESSION['session_field_errors'][$key] = $errors;
    session_write_close();
  }


  /**
   * check all field requirements
   * @param $types array of requirements
   * @param $value string
   * @return array (boolean, array(error1, error2...))
   */
  public static function validateFieldRequirements($types, $value, $key) {
    $invalid_array = [];
    $errors = [];

    foreach($types as $method_name => $property_value) {
      $bool = self::$method_name(
        $value,
        $property_value
      );
      if($bool) {
        $invalid_array[] = true;
      }
    }
    if(in_array(true, $invalid_array)) {
      $invalid = true;
      $errors[] = sprintf('%s is invalid', \AppLibrary\Str::human($key));
    }
    return array($invalid, $errors);
  }


  /**
   * gets session messages and cache it in static class vars
   * @return void
   */
  public static function getSessionData() {
    session_start();
    if(array_key_exists('form_conf_invalid', $_SESSION)
      && $_SESSION['form_conf_invalid']) {
      self::$invalid = true;
      $_SESSION['form_conf_invalid'] = false;
    }
    if(array_key_exists('form_conf_success', $_SESSION)
      && $_SESSION['form_conf_success']) {
      self::$success = true;
      $_SESSION['form_conf_success'] = false;
    }
    if(array_key_exists('session_field_errors', $_SESSION)
      && $_SESSION['session_field_errors']) {
      self::$session_field_errors = $_SESSION['session_field_errors'];
      unset($_SESSION['session_field_errors']);
    }
    session_write_close();
  }


  /**
   * set the form invalid
   * @return void
   */
  public function setInvalid() {
    session_start();
    if(!array_key_exists('form_conf_invalid', $_SESSION)) {
      $_SESSION['form_conf_invalid'] = true;
    }
    if(array_key_exists('form_conf_invalid', $_SESSION)
      && !$_SESSION['form_conf_invalid']) {
      $_SESSION['form_conf_invalid'] = true;
    }
    session_write_close();
  }


  /**
   * set the form as successful
   * @return void
   */
  public static function setSuccess() {
    session_start();
    if(!array_key_exists('form_conf_success', $_SESSION)) {
      $_SESSION['form_conf_success'] = true;
    }
    if(array_key_exists('form_conf_success', $_SESSION)
      && !$_SESSION['form_conf_success']) {
      $_SESSION['form_conf_success'] = true;
    }
    session_write_close();
  }


  /**
   * clear messages in the session
   * @return void
   */
  public static function clearMessages() {
    session_start();
    foreach(self::$messages_reference as $m) {
      if(array_key_exists($m, $_SESSION)) {
        unset($_SESSION[$m]);
      }
    }
    session_write_close();
  }

}