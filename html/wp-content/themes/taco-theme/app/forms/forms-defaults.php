<?php
/* This file allows defaults to be assigned globally for all Taco WordPress forms.
 * These defaults will be overridden if the developer specifies these values
 *   in the form config (hardcoded), or if the admin adds values in the wp-admin (if applicable).
 */
return array(
  'form_action' => '/wp-content/themes/taco-theme/app/forms/core/FormSubmit.php',
  'error_message' => 'There were some errors with your form submission. Please correct and try again.',
  'success_message' => 'Thanks for your message',
);
