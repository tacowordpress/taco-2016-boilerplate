<?php

class FormConfig extends FormConfBase {
  
  public function getFields() {
    $fields = array();
    return array_merge(parent::getFields(), $fields);
  }

  public function getPublic() {
    return true;
  }

  public function excludeFromSearch() {
    return true;
  }
}