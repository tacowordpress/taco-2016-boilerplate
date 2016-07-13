<?php

class Resource extends \Taco\Post {
  public function getFields() {

    return array(
      'resource_authors'=>array(
        'type'=>'textarea',
        'description'=>'Separate author names with a semicolon and a space.',
        'style'=>''
      ),
      'external_url'=>array(
        'type'=>'url',
        'style'=>''
      ),
      'downloadable_file'=>array(
        'type'=>'file'
      ),
      'resource_image' => array('type' => 'image'),
      'download_button_text'=>array(
        'type'=>'text',
        'description'=>'If no download button text is provided, the default text specific to the resource type will be used.',
        'style'=>''
      ),
      'related_resources'=>array(
        'type'=>'text',
        'class'=>'addbysearch',
        'data-post-type'=>'Resource'
      ),
      'created_for_testing' => array('type' => 'checkbox')
    );
  }

  public function getTaxonomies() {
    return array(
      'resource-topic',
      'resource-type'
    );
  }

  public function getSingular() {
    return 'Resource';
  }

  public function getPlural() {
    return 'Resources';
  }

  public function getAdminColumns() {
    return array('resource-type', 'resource-topic', 'resource_image');
  }
}