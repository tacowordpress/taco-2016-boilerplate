<?php

class ThemeOption extends \Taco\Post {
  public $class = __CLASS__;

  public static $_instance;
  const KEY_IS_ACTIVE = 'is_active';
  
  public function getFields() {
    return array_merge(self::getBaseFields(), array(
      'social_facebook'=>array('type'=>'link', 'label'=>'Facebook', 'default'=>'ReadingPartners.org'),
      'social_twitter'=>array('type'=>'link', 'label'=>'Twitter', 'default'=>'readingpartners'),
      
      'contact_street'=>array('type'=>'text', 'label'=>'Street', 'default'=>''),
      'contact_city'=>array('type'=>'text', 'label'=>'City', 'default'=>''),
      'contact_state'=>array('type'=>'select', 'label'=>'State', 'options'=>States::getAll(), 'default'=>''),
      'contact_zip'=>array('type'=>'text', 'label'=>'Zip', 'default'=>''),
      'contact_email'=>array('type'=>'email', 'label'=>'Email', 'default'=>''),
      'contact_phone'=>array('type'=>'text', 'label'=>'Phone', 'default'=>''),
      
      'analytics_key'=>array('type'=>'text', 'label'=>'Google Analytics ID', 'default'=>''),
      'analytics_tag_manager_key'=>array('type'=>'text', 'label'=>'Google Tag Manager ID', 'default'=>''),
    ));
  }

  
  /**
   * Admin fields
   * You should array_merge your fields with parent::getFields when extending this class
   */
  public function getBaseFields() {
    return array(
      self::KEY_IS_ACTIVE=>array('type'=>'checkbox'),
    );
  }
  
  
  /**
   * Which fields belong in the admin view?
   */
  public function getAdminColumns() {
    return array(self::KEY_IS_ACTIVE);
  }
  
  
  /**
   * Which core fields does this support?
   */
  public function getSupports() {
    return array('title', 'editor');
  }
  
  
  
  /**
   * Exclude theme options from search
   */
  public function getExcludeFromSearch() {
    return true;
  }
  
  
  /**
   * Get the meta boxes
   * Note: If you're on PHP 5.4, you'll get warning b/c you're using a non-static method in a static contest
   *       In that case, you'll need to switch ThemeOption::getFields() to static::getFields()
   */
  public function getMetaBoxes() {
    $groups = $this->getPrefixGroupedMetaBoxes();
    unset($groups['is']);
    $groups = array_merge(array('is_active'=>array('is_active')), $groups);
    return $groups;
  }
  
  
  /**
   * Get the active instance of ThemeOption
   */
  public static function getInstance() {
    if(!isset(self::$_instance)) {
      $class = __CLASS__;
      $helper = new $class;
      self::$_instance = $helper->getOneBy(self::KEY_IS_ACTIVE, true);
      
      // If the default theme-option post is deleted from WordPress
      // then just create an instance on the fly which will use the default values in getFields
      if(!is_object(self::$_instance)) self::$_instance = new $class;
    }
    return self::$_instance;
  }
  
  
  /**
   * Get a value by key but allow defaults from getFields
   * @param string $key
   * @return mixed
   */
  public function get($key) {
    $val = parent::get($key);
    if($val) return $val;
    
    // Account for cases when $val==='0'
    // Which should return the actual value, not default
    if(!is_null($val) && $val !== '') return $val;
    
    $field = $this->getField($key);
    if(!$field) return $val;
    if(array_key_exists('default', $field)) return $field['default'];
    
    return $val;
  }
  
  
  /**
   * Save
   * @param bool $exclude_post
   * @return bool
   */
  public function save($exclude_post=false) {
    // Only one theme option configuration can be active
    if($this->get(self::KEY_IS_ACTIVE)) {
      $instance = self::getInstance();
      if($instance->get('ID') && $instance->get('ID') !== $this->get('ID')) {
        $instance->set(self::KEY_IS_ACTIVE, false);
        $instance->save(true); // Passing true to avoid recursion
      }
    }
    
    return parent::save($exclude_post);
  }
  
  
  /**
   * Create the default instance if necessary
   * @return bool
   */
  public static function createDefaultInstanceIfNecessary() {
    if(self::getCount()) return;
    
    $class = __CLASS__;
    $instance = new $class;
    $instance->set('post_title', 'Default');
    $instance->set('is_active', true);
    $instance->save();
  }

  public static function getStatIconOptions() {
    return array(
      'icon-classroom'=>'Apple and Books',
      'icon-grad'=>'Graduation Hat',
      'icon-user'=>'Person',
      'icon-clock'=>'Clock',
    );
  }
}

// Make sure a record exists
add_action('plugins_loaded', 'ThemeOption::createDefaultInstanceIfNecessary');