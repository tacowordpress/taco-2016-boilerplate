<?php

class Employee extends \Taco\Post {
  
  public function getFields() {
    return array(
      'employee_title' => array('type' => 'text'),
      'affiliates' => array(
        'type' => 'text',
        'class' => 'addbysearch',
        'data-post-type' => 'Employee'
      )
    );
  }

  public function getSingular() {
    return 'Employee';
  }

  public function getPlural() {
    return 'Employees';
  }
}