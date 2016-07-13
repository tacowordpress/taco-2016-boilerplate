<?php

class FormConfBase extends \Taco\Post {

  public function getFields() {
    return array(
      'unique_id' => ['type' => 'hidden'],
      'fields' => array('type' => 'hidden'),
      'form_description' => array('type' => 'textarea'),
      'admin_emails' => array('type' => 'text'),
      'success_redirect_url' => array(
        'type' => 'url',
        'description' => 'If this field is filled out, the user will not see the success message and instead will be directed to the url specified'
      ),
      'form_success_message' => array(
        'type' => 'textarea',
      ),
      'form_error_message' => array(
        'type' => 'textarea'
      ),
      'on_success' => array(
        'type' => 'hidden'
      ),
      'use_honeypot' => array(
        'type' => 'hidden'
      ),
      'honeypot_field_name' => array(
        'type' => 'hidden'
      )
    );
  }

  public function getPublic() {
    return false;
  }

  public function getExcludeFromSearch() {
    return true;
  }

  public function getAdminColumns() {
    return array('form_description', 'author');
  }
}
