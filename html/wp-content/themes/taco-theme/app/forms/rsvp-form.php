<?php
return array(
  'conf_name' => 'RSVP test form',
  'hide_labels' => true,
  'success_message' => 'great!',
  'error_message' => 'not great!',
  'novalidate' => false,
  'fields' =>  array(
    'first_name' => array('type' => 'text', 'required' => true),
    'last_name' => array('type' => 'text'),
    'email_address' => array('type' => 'email', 'required' => true),
    'essay' => array('type' => 'textarea', 'maxlength' => 400),
    'state' => array('type' => 'select' , 'options' => \AppLibrary\States::getAll()),
    'subscribe' => array('type' => 'checkbox')
  )
);