<?php

class FormConfBase extends \Taco\Post {

  public function getFields() {
    return array(
      'unique_id' => ['type' => 'text'],
      'fields' => array('type' => 'hidden'),
      'form_description' => array('type' => 'textarea'),
      'do_not_track_anything' => array(
        'type' => 'checkbox'
      ),
      'auto_remove_database_records_after' => array(
        'type' => 'text',
        'placeholder' => 'days'
      ),
      'admin_emails' => array('type' => 'text'),
      'success_redirect_url' => array(
        'type' => 'url',
        'description' => 'If this field is filled out, the user will not see the sucess message and instead will be directed to the url specified'
      ),
      'send_daily_report_of_entries_to_admin' => array(
        'type' => 'checkbox'
      ),
      'form_success_message' => array(
        'type' => 'textarea',
      ),
      'form_error_message' => array(
        'type' => 'textarea'
      ),
      'on_success' => array(
        'type' => 'hidden'
      )
    );
  }

  public function getPublic() {
    return false;
  }

  public function excludeFromSearch() {
    return true;
  }

  public function getAdminColumns() {
    return array('form_description', 'author');
  }
}
