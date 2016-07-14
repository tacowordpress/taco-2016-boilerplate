<?php

class Post extends \Taco\Post {

  public function getFields() {
    return array(
      'created_for_testing' => array('type' => 'checkbox')
    );
  }

  public function getSingular() {
    return 'Post';
  }

  public function getPlural() {
    return 'Posts';
  }

  // Hide extra option from left nav in admin UI
  public function getPostTypeConfig() {
    return null;
  }
}