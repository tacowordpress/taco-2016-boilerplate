<?php

class FormField extends \Taco\Post {
  
  public function getFields() {
    return array(
      'name' => array('type' => 'text'),
      'type' => array(
        'type' => 'select',
        'options' => self::getAllFieldTypes()
      ),
      'options' => array(
        'type' => 'select',
        'options' => array()
      )
    );
  }

  public static function getAllFieldTypes() {
    return array(
      'text',
      'textarea',
      'checkbox',
      'select',
      'hidden',
      'radio',
      'color',
      'date',
      'datetime',
      'datetime-local',
      'email',
      'month',
      'number',
      'range',
      'search',
      'tel',
      'time',
      'url',
      'week',
    );
  }

  public function getSingular() {
    return 'Form Field';
  }

  public function getPlural() {
    return 'Form Fields';
  }

  public function getPublic() {
    return true;
  }

  public function excludeFromSearch() {
    return true;
  }

  public function getAdminColumns() {
    return array(
      'name',
      'type'
    );
  }
}