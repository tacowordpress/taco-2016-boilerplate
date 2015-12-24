<?php


class FormEntry extends \Taco\Post {
  use Taquito;
  public $form_config_id = null;

  public function getFields() {
    $fields = array(
      'form_config' => array(
        'type' => 'select',
        'options' => self::getFormConfigs()
      )
    );
    $form_conf_fields = self::getFieldsFromFormEntryConf();
    return array_merge(
      $fields,
      $form_conf_fields
    );
  }


  public function getFieldsFromFormEntryConf() {
    
    if(!$this->form_config_id) {
      $post_id = self::getPostID();
      if(!is_numeric($post_id)) return array();
      $conf_id = get_post_meta($post_id, 'form_config', true);
      if(!is_numeric($conf_id)) return array();
    } else {
      $conf_id = $this->form_config_id;
    }

 
    $conf_object = FormConfig::find($conf_id);
    if(!\AppLibrary\Obj::iterable($conf_object)) return array();
    if(!strlen($conf_object->get('form_fields'))) return array();

    $fields_objects = \AddBySearch\AddBySearch::getPostsFromOrder(
      $conf_object->get('form_fields')
    );
    
    $entry_fields = FormConfig::makeFieldsConfigFromFieldPosts(
      $fields_objects
    );

    return (Arr::iterable($entry_fields))
      ? $entry_fields
      : array();
  }

  public function isValid() {
    // do validation stuff
    if($invalid) {
      FormConfBase::setInvalid();
      return false;
    }
    return true;
  }

  public function save() {
    FormConfBase::setSuccess();
    return parent::save();
  }


  public function getPermalinkAfterInsert() {
    return header(sprintf('Location: %s', $_SERVER['HTTP_REFERER']));
  }

  public static function getFormConfigs() {
    return FormConfig::getPairs();
  }

  public function getSingular() {
    return 'Form Entry';
  }

  public function getPlural() {
    return 'Form Entries';
  }

  public function excludeFromSearch() {
    return true;
  }

  public function getAdminColumns() {
    return array('form_config');
  }
}