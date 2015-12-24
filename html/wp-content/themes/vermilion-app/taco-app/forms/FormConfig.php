<?php

class FormConfig extends FormConfBase {
  use FieldsPool;

  const URL_SUBMIT = '/wp-content/themes/vermilion-app/taco-app/forms/core/submit.php';

  public function getFields() {

    // convert hardcoded fields to database records
    self::dataBizeFields();

    return array_merge(
      parent::getFields(),
      self::getFormFields()
    );
  }

  public static function getFormFields() {
    return self::getCommonFields();
  }

  public function getMetaBoxes() {
    return array(
      'config' => array_keys(parent::getFields())
    );
  }

  public function getPublic() {
    return true;
  }

  public function getPlural() {
    return 'Form Configs';
  }

  public function getSingular() {
    return 'Form Config';
  }

  public function getAdminColumns() {
    return array();
  }
}