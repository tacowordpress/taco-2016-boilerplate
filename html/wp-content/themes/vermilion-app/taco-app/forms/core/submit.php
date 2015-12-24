<?php

/**
 * Redirect the referring page
 * Used to display form errors
 * @param array $data
 */
function redirect_to_referring_page($data) {
  $uri = (array_key_exists('HTTP_REFERER', $_SERVER))
    ? $_SERVER['HTTP_REFERER']
    : '/';
  header('Location: '.$uri);

  exit;
}

// Bootstrap WordPress
include getenv('HTTP_BOOTSTRAP_WP');


// Helpers
if(class_exists('FlashDataMessages')) {
  $flash_data = new FlashData;
  $flash_data_messages = new FlashDataMessages;
}


// Toggle to $_GET for easy debugging locally
$source = $_POST;

// Verify we're using a valid Taco class
if(
  !array_key_exists('class', $source)
  || !class_exists($source['class'])
  || !is_subclass_of($source['class'], '\Taco\Post')
) {

  // User message
  if(class_exists('FlashDataMessages')) {
    $content = 'There was an error processing your form, please try again.';
    $type = 'error';
    $flash_data_messages->add($content, $type);
    $flash_data->add($source, null, 'form_values');
  }
  // Log the messgae to the DB for future debugging
  if(class_exists('LogMessage')) {
    $log_message = new LogMessage(array(
      'type'=>$type,
      'user_message'=>$content,
      'system_message'=>'Class error',
      'vars'=>array(
        'taco_class'=>(array_key_exists('class', $source))
          ? $source['class']
          : null
      )
    ));
    $log_message->save();
  }
  redirect_to_referring_page($source);
}

$record = new $source['class'];
if(array_key_exists('ID', $source)) {
  $record = $record::find($source['ID']);
}

$reflection = new ReflectionObject($record);


// Verify the WP nonce for CSRF protection
$nonce = (array_key_exists($reflection->getConstant('KEY_NONCE'), $source))
  ? $source[$reflection->getConstant('KEY_NONCE')]
  : null;


if(!$record->verifyNonce($nonce)) {

  if(class_exists('FlashDataMessages')) {
    // User message
    $content = 'There was an error processing your form, please try again.';
    $type = 'error';
    $flash_data_messages->add($content, $type);
    $flash_data->add($source, null, 'form_values');
  }
  
  // Log the messgae to the DB for future debugging
  if(class_exists('LogMessage')) {
    $log_message = new LogMessage(array(
      'type'=>$type,
      'user_message'=>$content,
      'system_message'=>'Nonce error',
      'vars'=>array(
        'nonce'=>$nonce,
        'nonce_verified'=>false
      )
    ));
    $log_message->save();
  }
  
  redirect_to_referring_page($source);
}

// Get data for most fields
if(array_key_exists('form_config', $source)){
  $record->form_config_id = $source['form_config'];
}
$fields = $record->getFields();
$record_info = array();
foreach($fields as $k=>$field) {
  if(!array_key_exists($k, $source)) continue;
  
  $record_info[$k] = $source[$k];
}

// Validate user input
if(!$record->isValid($record_info)) {

  // User message
  if(class_exists('FlashDataMessages')) {
    $type = 'error';
    $content = 'There were some errors in your submission. Please try again.';
    $flash_data_messages->add($content, $type);
    $flash_data->add($record_info, null, $record->getPublicFormKey('values'));
    $flash_data->add($record->getMessages(), null, $record->getPublicFormKey('errors'));
  }
  
  if(class_exists('LogMessage')) {
    $log_message = new LogMessage(array(
      'type'=>$type,
      'user_message'=>$content,
      'system_message'=>'Validation error',
      'vars'=>$record->getMessages(),
    ));
    $log_message->save();
  }
  
  redirect_to_referring_page($source);
}


// Assign info and save
$record->assign($record_info);
//$record->assignDefaultValues();
if(!$record->save()) {
  // User message
  if(class_exists('FlashDataMessages')) {
    $type = 'error';
    $content = 'An error was encountered saving your submission. Please try again.';
    $flash_data_messages->add($content, $type);
    $flash_data->add($record_info, null, $record->getPublicFormKey('values'));
    $flash_data->add($record->getMessages(), null, $record->getPublicFormKey('errors'));
  }
  
  if(class_exists('LogMessage')) {
    $log_message = new LogMessage(array(
      'type'=>$type,
      'user_message'=>$content,
      'system_message'=>'Validation error',
      'vars'=>$record->getMessages(),
    ));
    $log_message->save();
  }
  redirect_to_referring_page($source);
}
if(class_exists('FlashDataMessages')) {
  $content = 'Thanks for your inquiry. We will be in contact with you shortly.';
  $flash_data_messages->add($content, $type);
}
// Redirect
$redirect_url = method_exists($record, 'getPermalinkAfterInsert')
  ? $record->getPermalinkAfterInsert()
  : $record->getPermalink();
if(!$redirect_url) {
  $redirect_url = '/';
}

header(sprintf('Location: %s', $redirect_url));
exit;