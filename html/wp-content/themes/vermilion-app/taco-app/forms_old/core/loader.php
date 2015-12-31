<?php
require_once __DIR__.'/FormValidators.php';
require_once __DIR__.'/FormErrors.php';
require_once __DIR__.'/FormTemplate.php';
require_once __DIR__.'/FieldsPool.php';
require_once __DIR__.'/FormConfBase.php';
require_once __DIR__.'/FormEntry.php';
require_once __DIR__.'/FormField.php';

// front-end
add_action('wp_enqueue_scripts', function() {
  wp_enqueue_style(
    'taco-forms-base',
    get_template_directory_uri().'/taco-app/forms/core/css/forms-base.css'
  );
});