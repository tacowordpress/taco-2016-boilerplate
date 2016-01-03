<?php

/* Contact forms tend to share a lot of common fields
 * Let's create these fields so they can be shared
 * with multiple form configurations.
 */

trait FieldsPool {
  public static function getCommonFields() {
    $fields = array(
      'first_name' => array('type' => 'text'),
      'last_name' => array('type' => 'text'),
      'email' => array('type' => 'email'),
      'phone' => array('type' => 'tel'),
      'subject' => array('type' => 'text'),
      'message' => array('type' => 'textarea'),
      'lat' => array('type' => 'text'),
      'long' => array('type' => 'text'),
      'address' => array('type' => 'text'),
      'address_2' => array('type' => 'text'),
      'city' => array('type' => 'text'),
      'state' => array('type' => 'select', 'options' => \AppLibrary\States::getAll()),
      'zip' => array('type' => 'text'),
      'country' => array('type' => 'text'), // todo: get an array of countries
    );

    // all for creation of fields outside of core
    if(file_exists(__DIR__.'/../FormFields.php')) {
      return array_merge(
        $fields,
        include __DIR__.'/../FormFields.php'
      );
    }
    return $fields;
    
  }
}