<?php

// An API for the FormConf class

add_action('init', 'TacoForm::getSessionData');

class TacoForm {
  use FormValidators;

  private $settings = [];
  
  public static $invalid = false;
  public static $success = false;
  public static $session_field_errors = array();
  public static $session_field_values = array();

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
      'success_redirect_url' => null,
      'label_field_wrapper' => 'TacoForm::rowColumnWrap',
      'on_success' => null
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
      if($k === 'fields' && $args['fields'] !== 'auto') {
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

    // if a callback is defined call it on success
    if(self::$success === true) {
      if($this->settings['on_success']
        && is_callable($this->settings['on_success'])) {
        $this->settings['on_success']($this);
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

    // wrapper label/field method (is it the default, and is it a method or func?)
    if(is_string($this->settings['label_field_wrapper'])) {
      $wrapper_callable = explode(
        '::', $this->settings['label_field_wrapper']
      );
      if(count($wrapper_method) > 1) {
        $wrapper_callable = current($wrapper_callable);
      }
      $this->settings['label_field_wrapper'] = $wrapper_callable;
    }
    
    // assign post title to instance
    $this->conf_instance->set('post_title', $this->get('conf_name'));
    
    if($this->settings['fields'] !== 'auto') {
      $this->conf_instance->set('fields', serialize($this->fields));
    }

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
   * automatically generate a fields/attribs from template tags
   * @param $callback callable
   * @return callable
   */
  public function getFieldsAutomatically($callback) {
    if(!($callback !== null && is_callable($callback))) {
      throw new Exception('$callback must be a valid callback');
    }
    
    $html_template = null;
   
    ob_start();
      $callback($this->conf_instance);
    $html_template = ob_get_clean();

    preg_match_all('/\%(.*)\%/', $html_template, $parts);
    $parts = $parts[1];

    $fields_raw = [];

    foreach($parts as $part) {
      $arg_key_values = [];
      if(preg_match('/\|/', $part)) {
        $part_args = explode('|', $part);
        $key = current($part_args);
        $part_args = array_slice($part_args, 1);
        foreach($part_args as $arg) {
          $keyvalues = explode('=', $arg);
          $arg_key_values[current($keyvalues)] = end($keyvalues);
        }
      } else {
        $key = $part;
        $part_args = [];
      }

      if(in_array($key, array('form_messages','post_content', 'edit_link'))) continue;
      if(in_array($key, array('post_content', 'edit_link'))) continue;
      $fields_raw[$key] = $arg_key_values;
      if(isset($key) && !array_key_exists('type', $fields_raw[$key])) {
        $fields_raw[$key]['type'] = 'text';
      }
    }
    $this->fields = $fields_raw;
    $this->conf_instance->set('fields', serialize($fields_raw));
    $this->conf_instance->save();
    
    return $this->convertToPropperTemplate(
      $html_template
    );
  }


  /**
   * convert an html template that has fields with args to just fields keys
   * @param $html_template string
   * @return callable
   */
  public function convertToPropperTemplate($html_template) {

    preg_match_all('/\%(.*)%/', $html_template, $originals);
    preg_match_all('/\%([a-z_]*)/m', $html_template, $replacements);
    $replacements = array_values(
      array_filter($replacements[1])
    );
    $originals = $originals[0];
    
    $new_html = $html_template;
    $inc = 0;
    foreach($originals as $o) {
      $new_html = str_replace(
        $o,
        '%'.$replacements[$inc].'%',
        $new_html
      );
      $inc++;
    }
    return function() use ($new_html) {
      echo $new_html;
    };
  }


  /**
   * renders the form head
   * @return string html
   */
  public function render($callback=null) {
    if($this->get('fields') == 'auto') {
      $callback = $this->getFieldsAutomatically($callback);
    }
    if($callback !== null && is_callable($callback)) {
      return $this->renderCustom($callback);
    }
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
  public function renderFormHead($using_custom=false) {

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
    $html[] = '<input name="taco_form_submission" type="hidden" value="true">';

    if($this->settings['use_ajax']) {
      $html[] = '<input type="hidden" name="use_ajax" value="1">';
    }

    // wrap with row and columns (foundation)
    if(!$this->settings['exclude_post_content'] && !$using_custom) {
      $html[] = $this->settings['label_field_wrapper'](
        $this->conf_instance->getTheContent(),
        $this->settings['column_classes']
      );
    }

    //get form messages
    if(!$using_custom) {
      $html[] = $this->settings['label_field_wrapper'](
        $this->getFormMessages($messages),
        $this->settings['column_classes'].' form-messages'
      );
      $this->renderMessages();
    }
    
    return join('', $html);
  }


  public function renderMessages() {
    $messages = [];
    // check for success and error message overrides
    if(strlen($this->get('success_message'))) {
      $messages['success_message'] = $this->get('success_message');
    }
    if(strlen($this->get('error_message'))) {
      $messages['error_message'] = $this->get('error_message');
    }
    return $this->getFormMessages($messages);
  }


  /**
   * renders the form fields
   * @return string html
   */
  public function renderFormFields($return_as_array=false) {
    
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

      // get the value if it exists
      $v['value'] = (self::getSessionFieldValue($k)) ?
        self::getSessionFieldValue($k)
        : '';

      if(array_key_exists('type', $v) && $v['type'] === 'checkbox') {
        $html[$k] = $this->settings['label_field_wrapper'](
          $this->renderCheckBox($k, $v),
          $error_columns_class,
          $k
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
      
      $html[$k] = $this->settings['label_field_wrapper'](
        self::renderFieldErrors($k)
        .' '.$label.' '
        .$this->conf_instance->getRenderPublicField($k, $v),
        $error_columns_class,
        $k
      );
    }
    return (!$return_as_array)
      ? join('', $html)
      : $html;
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
   * get a custom form rendering defined by an html callback
   * @param $callback callable
   * @return boolean
   */
  private function renderCustom($callback) {
    
    $html = [];
    $html[] = $this->renderFormHead(true);
    $rendered_fields = $this->renderFormFields(true);

    // add other useful content
    $rendered_fields['post_content'] = $this->conf_instance->getTheContent();
    $rendered_fields['edit_link'] = $this->renderFormEditLink();
    $rendered_fields['form_messages'] = $this->renderMessages();
    
    // render the custom template
    FormTemplate::create(
      array($rendered_fields),
      $callback,
      $rendered_template, // by reference
      $this->conf_instance
    );

    $html[] = $rendered_template;
    return join('', $html);
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
    $html[] = $this->settings['label_field_wrapper'](sprintf(
      '<button type="submit">%s</button>',
      $this->get('submit_button_text')
    ));
    $html[] = $this->renderFormEditLink();
    return join('', $html);
  }


  /**
   * renders the the form edit link
   * @return string html
   */
  public function renderFormEditLink() {
    if(is_user_logged_in() && is_super_admin()) {
      return $this->settings['label_field_wrapper'](
        sprintf('<a href="/wp-admin/post.php?post=%d&action=edit">Edit this form\'s settings</a>',
          $this->conf_instance->ID
        )
      );
    }
    return '';
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
  public static function rowColumnWrap($field, $column_classes='small-12 columns', $field_key=null) {
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
      self::setValuesIfErrorsExist($source_fields);
      self::setInvalid();
      return false;
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
    if(array_key_exists('session_field_values', $_SESSION)
      && $_SESSION['session_field_values']) {
      self::$session_field_values = $_SESSION['session_field_values'];
      unset($_SESSION['session_field_values']);
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


  /**
   * set values after submission if the form has errors
   * @return void
   */
  public static function setValuesIfErrorsExist($source_fields) {
    session_start();
    if(!array_key_exists('session_field_values', $_SESSION)) {
      $_SESSION['session_field_values'] = array();
      foreach($source_fields as $k => $v) {
        $_SESSION['session_field_values'][$k] = $v;
      }
    }
    session_write_close();
  }


  /**
   * get a session field value
   * @param  $key string
   * @return string or boolean
   */
  private function getSessionFieldValue($key) {
    if(array_key_exists($key, self::$session_field_values)) {
      return self::$session_field_values[$key];
    }
    return false;
  }

}