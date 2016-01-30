# TacoForms

The objective of TacoForms in tangent with this boilerplate, it to literally allow you to make a form in less than 2 minutes. It's API that taps into TacoWordPress to create Form configuration post types that allow both you the developer as well as the client admin to setup.

## A Basic example
```php
<?php
$contact_form_config = new TacoForm(
  array(
    'conf_name' => 'General Contact Form Configurations',
    'fields' => array(
      'first_name' => array('type' => 'text'),
      'email' => array('type' => 'email'),
      'message' => array('type' => 'textarea')
    )
  )
);

echo $contact_form_config->render();
```
#### So What's happening here?
* First we create a new TacoForm object
* Then we pass in array of settings
* Define some fields to be used in the form
* Lastly, render the form with the object's render method.

All of that gives you a form in the frontned that visitors can fill out, and be recorded as an entry post type in WordPress. It also gives the admin access to overriding common settings like admin emails (for notifications), success, and error messages.

