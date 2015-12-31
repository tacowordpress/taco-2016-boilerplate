<?php
add_action('init', 'FormConfBase::getSessionData');

class FormConfBase extends \Taco\Post {
  use FieldsPool;
  use FormValidators;

  public static $invalid = false;
  public static $success = false;
  public $fields_config;
  public $error_messages = array();
  public static $session_errors = null;

  function getFields() {
    return array(
      'form_fields' =>  array(
        'type' => 'text',
        'class' => 'addbysearch',
        'data-post-type' => 'FormField'
      ),
      'required' =>  array(
        'type' => 'text',
        'class' => 'addbysearch',
        'data-post-type' => 'FormField'
      ),
      // todo: develop
      'do_not_track_anything' => array(
        'type' => 'checkbox'
      ),
      // todo: develop
      'do_not_track_these_fields_in_the_database' =>  array(
        'type' => 'text',
        'class' => 'addbysearch',
        'data-post-type' => 'FormField'
      ),
      // todo: develop (do not rely on cron jobs)
      'auto_remove_database_records_after' => array(
        'type' => 'text',
        'placeholder' => 'days'
      ),
      'admin_emails' => array('type' => 'text'),
      'admin_email_notification_template' => array(
        'type' => 'textarea'
      ),
      // todo: develop
      'success_redirect_url' => array(
        'type' => 'url',
        'description' => 'If this field is filled out, the user will not see the sucess message and instead will be directed to the url specified'
      ),
      // todo: develop
      'send_daily_report_of_entries_to_admin' => array(
        'type' => 'checkbox'
      ),
      'form_success_message' => array(
        'type' => 'textarea',
        'default' => 'Thanks for your inquiry!'
      ),
      'form_error_message' => array(
        'type' => 'textarea',
        'default' => 'There were some errors with your form submission. Please correct and try again.'
      ),
      'disable_field_type_validation_for_these_fields' => array(
        'type' => 'text',
        'class' => 'addbysearch',
        'data-post-type' => 'FormField'
      )

    );
  }

  // convert hardcoded fields to database records for portability
  public function dataBizeFields() {
    if(!array_key_exists('post_type', $_GET)
      && !$_GET['post_type'] == 'form-config') return;

    $field_types = array_flip(FormField::getAllFieldTypes());

    $all_fields = self::getCommonFields();
    foreach($all_fields as $k => $v) {
      $record = FormField::getOneBy('name', $k);
      if(\AppLibrary\Obj::iterable($record)) continue;
      
      $record = new FormField;

      $record->set('name', $k);
      $record->set('post_title', $k.sprintf(
        ' [type: %s]', $v['type'])
      );

      $record->set(
        'type',
        array_key_exists('type', $v)
          ? $field_types[$v['type']]
          : 'text'
      );

      if($v['type'] == 'select'
        && array_key_exists('options', $v)) {
        $record->set('options', $v['options']);
      }

      $record->save();
    }
  }

  public static function rowColumnWrap($field, $column_classes='small-12 columns') {
    return sprintf(
      '<div class="row"><div class="%s">%s</div></div>',
      $column_classes,
      $field
    );
  }

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
    if(array_key_exists('form_field_errors', $_SESSION)
      && is_null(self::$session_errors)) {
      self::$session_errors = $_SESSION['form_field_errors'];
      unset($_SESSION['form_field_errors']);
      session_write_close();
    }
  }


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


  public function setInvalid() {
    session_start();
    if(!array_key_exists('form_conf_invalid', $_SESSION)) {
      $_SESSION['form_conf_invalid'] = true;
    }
    if(array_key_exists('form_conf_invalid', $_SESSION)
      && !$_SESSION['form_conf_invalid']) {
      $_SESSION['form_conf_invalid'] = true;
    }
    $_SESSION['form_field_errors'] = $this->error_messages;
    session_write_close();
  }

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

  public function render($args=array(), $custom_render_callback=null) {
    
    // assign defaults
    $defaults = array(
      'custom_template' => false,
      'css_class' => sprintf('taco-%s', $this->get('post_name')),
      'id' => '',
      'method' => 'post',
      'use_ajax' => false,
      'hide_labels' => false,
      'column_classes' => 'small-12 columns',
      'exclude_post_content' => false,
      'field_requirements' => null
    );
    
    // merge defaults with args
    extract(array_merge($defaults, $args));

    // get fields specified in admin ui
    $fields = \AddBySearch\AddBySearch::getPostsFromOrder(
      $this->get('form_fields')
    );

    $required_fields = \AddBySearch\AddBySearch::getPostsFromOrder(
      $this->get('required')
    );

    // field requirements
    if(Arr::iterable($field_requirements)) {
      //$this->setFieldRequirementsInSession($field_requirements);
    }

    if(Arr::iterable($required_fields)) {
      $required_fields = array_map(function($object) {
        return $object->ID;
      }, $required_fields);
    }

    // custom messages?
    $messages = [];
    // check for success and error message overrides
    if(isset($success_message)) {
      $messages['success_message'] = $success_message;
    }
    if(isset($error_message)) {
      $messages['error_message'] = $error_message;
    }

    // get called class for getting submit url
    $called_class = get_called_class();
    $action = (strlen($action)) ? $action : $called_class::URL_SUBMIT;

    $css_class = $css_class.' vermilion-forms';

    // start the form html using an array
    $html = [];
    $html[] = sprintf(
      '<form action="%s" method="%s" class="%s" id="%s" data-use-ajax="%s">',
      $action,
      $method,
      $css_class,
      $id,
      $use_ajax
    );

    // get neccessary fields CSRF protection
    $form_entry_helper = new FormEntry;
    $html[] = $form_entry_helper->getRenderPublicField('nonce');
    $html[] = $form_entry_helper->getRenderPublicField('class');
    $html[] = sprintf(
      '<input name="form_config" type="hidden" value="%d">',
      $this->ID
    );

    if($use_ajax) {
      $html[] = '<input type="hidden" name="use_ajax" value="1">';
    }

    // wrap with row and columns (foundation)
    if(!$exclude_post_content) {
      $html[] = self::rowColumnWrap(
        $this->getTheContent(),
        $column_classes
      );
    }

    // get form messages
    $html[] = self::rowColumnWrap(
      $this->getFormMessages($messages),
      $column_classes.' form-messages'
    );


    // does a custom template exist?
    if($custom_template) {
      $rendered_fields = $this->prepareRenderCustom(
        $fields,
        $required_fields
      );
      
      // render the custom template
      FormTemplate::create(
        array($rendered_fields),
        $custom_render_callback,
        null,
        $rendered_template,
        $this
      );

      $html[] = $rendered_template;
    }
    // iterate through fields, get settings, and add to html array
    if(!$custom_template) {
      foreach($fields as $field) {
        $html[] = '<div class="row">';
        
        $html[] = sprintf(
          '<div class="%s">',
          $column_classes
        );

        // get the config for the field (custom post type)
        $type = $field->get('type', true);
        $name = $field->get('name');
        $options = $field->get('options');
        
        // unserialize "$options" to make it an array
        if(!is_array($options) && strlen($options)) {
          $options = unserialize($options);
        }
        
        $required = in_array($field->ID, $required_fields)
          ? true
          : false;

          // set the field var to an array
        $field = array(
          'type' => $type,
        );

        if($required) {
          $field['required'] = true;
        }

        // if it's a select, it may have options
        if($type == 'select') {
          $field['options'] = $options;
        }

        if(\AppLibrary\Arr::iterable(self::$session_errors)) {
          $field_error = self::$session_errors[$name];
          $html[] = sprintf(
            '<span class="form-field-error">%s</span>',
            $field_error
          );
        }

        if($type == 'checkbox') {
          $html[] = sprintf(
            '<label>%s %s</label>',
            $this->getRenderPublicField($name, $field),
            \AppLibrary\Str::human($name)
          );
        }
        // should we hide the labels in favor of placeholders?
        elseif(!$hide_labels) {
          $html[] = sprintf(
            '<label>%s</label>',
            \AppLibrary\Str::human($name)
          );
          $html[] = $this->getRenderPublicField($name, $field);
        } else {
          $html[] = sprintf(
            '<label class="hidden-label">%s</label>',
            \AppLibrary\Str::human($name)
          );
          $field['placeholder'] = \AppLibrary\Str::human($name);
          $html[] = $this->getRenderPublicField($name, $field);
        }
        // close out the field columns and row
        $html[] = '</div>';
        $html[] = '</div>';
      }
    }

    // form submit
    if(!$custom_template) {
      $html[] = self::rowColumnWrap(
        '<button type="submit">submit</button>',
        $column_classes
      );
    }
  
    // add a usefull form edit link
    if(is_user_logged_in()) {
      $html[] = self::rowColumnWrap(sprintf(
        '<a href="/wp-admin/post.php?post=%d&action=edit">Edit this form</a>',
        $this->ID
      ), $column_classes);
    }

    // close out the form and return an html string
    $html[] = '</form>';

    return join('', $html);
  }

  public function validate($values) {

    $is_valid = true;
    $fields_objects = \AddBySearch\AddBySearch::getPostsFromOrder(
      $this->get('form_fields')
    );
    $required_fields = \AddBySearch\AddBySearch::getPostsFromOrder(
      $this->get('required')
    );

    if(Arr::iterable($required_fields)) {
      $required_fields = array_map(function($object) {
        return $object->ID;
      }, $required_fields);
    }

    foreach($fields_objects as $field_obj) {
      $is_required = false;
      if(in_array($field_obj->ID, $required_fields)) {
        $is_required = true;
      }
      if(strlen($field_obj->get('field_type_error'))) {
        $field_error_message = $field_obj->get('field_type_error');
      } else {
        $field_error_message = $this->getDefaultErrorForFieldType(
          $field_obj->get('type', true)
        );
      }
      $is_valid = $this->getFieldError(
        $is_valid,
        $field_obj->get('name'),
        $field_obj->get('type', true),
        $values,
        $is_required,
        $field_error_message
      );
    }
    if(!$is_valid) {
      $this->setInvalid();
    }
    return $is_valid;
  }
  
  public function getDefaultErrorForFieldType($type) {
    switch($type) {
      case 'email':
        return 'Please make sure you entered a valid email for "%s"';
      break;

      case 'tel':
        return 'Please make sure you entered a valid phone number for "%s"';
      break;

      case 'url':
        return 'Please make sure you entered a valid url for "%s"';
      break;

      case 'checkbox':
        return 'The checkbox "%s" is required';
      break;

      default:
        return 'Invalid field for %s';
    }
  }

  public function getFieldError(
    $is_valid,
    $field_name,
    $field_type,
    $values,
    $required=false,
    $message='') {
    
    $errors = array();
    $errors[$field_name] = array();
    
    $value = array_key_exists($field_name, $values)
      ? $values[$field_name]
      : null;
    
    if($required) {
      if(!$this->doesRequiredFieldHaveValue($value, 'required', $field_name)) {
        $errors[$field_name][] = $this->makeErrorMessage('required', $field_name, $message);
        $is_valid = false;
      }
    }
    if($field_type == 'email') {
      if(!$this->isEmailFieldValid($value, 'email', $field_name)) {
        $errors[$field_name][] = $this->makeErrorMessage('email', $field_name, $message);
         $is_valid = false;
      }
    }
    if($field_type == 'url') {
      if(!$this->isURLFieldValid($value, 'required', $field_name)) {
        $errors[$field_name][] = $this->makeErrorMessage('url', $field_name, $message);
        $is_valid = false;
      }
    }
    if(preg_match('/zip/', $field_name)) { // assumption
      if(!$this->isUSZipFieldValid($value, 'required', $field_name)) {
        $errors[$field_name][] = $this->makeErrorMessage('us_zip', $field_name, $message);
        $is_valid = false;
      }
    }
    $this->error_messages[$field_name] = join("<br>", $errors[$field_name]);
    return $is_valid;
  }

  public function makeErrorMessage($type, $key, $message='') {
    if(strlen($message)) {
      return sprintf($message, $key);
    }
    return sprintf('%s error with %s', $type, Str::human($key));
  }

  public static function makeFieldsConfigFromFieldPosts($fields, $required_fields=array()) {
    $fields_config = [];

    foreach($fields as $f) {
      $fields_config[$f->get('name')] = array(
        'type' => $f->get('type', true)
      );
      if(in_array($f->ID, $required_fields)) {
        $fields_config[$f->get('name')]['required'] = 'required';
      }
      if($f->get('type', true) === 'select') {
        if(!is_array($f->get('options'))) {
          $options = unserialize($f->get('options'));
        } else {
          $options = $f->get('options');
        }
        $fields_config[$f->get('name')]['options'] = $options;
      }
    }
    return $fields_config;
  }


  public function prepareRenderCustom($fields, $required_fields=array()) {
    // get an array like what the \Taco\Post\getFields method returns
    $fields = self::makeFieldsConfigFromFieldPosts(
      $fields, $required_fields
    );

    // provide options for getting a field with a label or without
    $rendered_fields = [];
    foreach($fields as $k => $v) {
      $rendered_fields[$k] = $this->getRenderPublicField($k, $fields[$k]);
      if($v['type'] == 'checkbox') {
        $rendered_fields[$k.'_with_label'] = sprintf(
          '<label for="%s">%s %s</label>',
          array_key_exists('id', $v) ? $v['id']: $k,
          $rendered_fields[$k],
          \AppLibrary\Str::human($k)
        );
      } else {
        $rendered_fields[$k.'_with_label'] = sprintf(
          '<label for="%s">%s</label>%s',
          array_key_exists('id', $v) ? $v['id']: $k,
          \AppLibrary\Str::human($k),
          $rendered_fields[$k]
        );
      }
      
    }
    return $rendered_fields;
  }

  public function getPublic() {
    return false;
  }

  public function excludeFromSearch() {
    return true;
  }
}