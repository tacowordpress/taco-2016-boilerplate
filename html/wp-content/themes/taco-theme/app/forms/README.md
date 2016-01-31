# TacoForms

The objective of TacoForms in tangent with this boilerplate, it to literally allow you to make a form in less than 2 minutes. It's an API that taps into TacoWordPress to create Form configuration post types that allow both you the developer as well as the client admin to setup.

## A Basic example
```php
<?php
$contact_form_config = new TacoForm(
  array(
    'conf_name' => 'General Contact Form Configuration',
    'fields' => array(
      'first_name' => array('type' => 'text'),
      'email' => array('type' => 'email'),
      'message' => array('type' => 'textarea')
    )
  )
);

echo $contact_form_config->render();
```
#### So what's happening here?
* First we create a new TacoForm object
* Then we pass in array of settings
* Define some fields to be used in the form
* Lastly, render the form with the object's render method.

All of that gives you a form in the frontend that visitors can fill out, and be recorded as an entry post type in WordPress. It also gives the admin access to overriding common settings like admin emails (for notifications), success, and error messages.

#### TacoForm API configuration settings

These are the properties and values (defaults shown below) that can be used to setup a form configuration.

```php

    array(
      'conf_name' => '',
      'fields' => array(), 
      'css_class' => '',
      'id' => '',
      'method' => 'post',
      'action' => null,
      'novalidate' => false,
      'use_ajax' => false,
      'hide_labels' => false,
      'column_classes' => 'small-12 columns',
      'exclude_post_content' => false,
      'submit_button_text' => 'submit',
      'success_message' => null,
      'error_message' => null,
      'success_redirect_url' => null,
      'label_field_wrapper' => 'TacoForm::rowColumnWrap'
    );
```
Details on what each property/value does is coming soon.

## Customizing how the form gets rendered
The simplicity of TacoForms doesn't stop with the above. Custom rendering of a form is also a breeze and comes with a few different options.

##### Example 1 
```php
<?php 

echo (new TacoForm(
  array(
    'conf_name' => 'General Contact Form Configuration',
    'novalidate' => true,
    'fields' => array(
      'first_name' => array(),
      'email' => array('type' => 'email'),
      'message' => array('type' => 'textarea')
    ),
  )
))->render(function($form_conf) { ?>
  
  <div class="row">
    <div class="small-12 columns">
      %first_name%
    </div>
  </div>
  
  <div class="row">
    <div class="small-12 columns">
      %email%
    </div>
  </div>
  
  <div class="row">
    <div class="small-12 columns">
      %message%
    </div>
  </div>
  
   <div class="row">
    <div class="small-12 columns">
      <button type="submit">Submit</button>
    </div>
  </div>

  <div class="row">
    <div class="small-12 columns">
      %edit_link%
    </div>
  </div>
  
<?php }); ?>
```
Example 1 shows how easy it is to use your own template with HTML and template tags. 
Using %my_field_name% will render that field according to the configuration settings. Let's say you wanted to hide the labels for fields and use placeholders instead. In your config settings just set `"hide_labels" => true`.
You will notice "%edit_link%" in the example above. This renders a link to the WordPress admin, where client admin can go to the configuration to edit fields like success/error messages and admin emails.

##### Example 2 changing form messages

```php
<?php 

echo (new TacoForm(
  array(
    'conf_name' => 'General Contact Form Configuration',
    'novalidate' => true,
    'success_message' => 'Success! Thanks for your inquiry.',
    'error_message' => 'Oops! Looks like there was an error in the form. Please correct and try again.'
    'fields' => array(
      'first_name' => array(),
      'last_name' => array(),
      'email' => array('type' => 'email'),
      'message' => array('type' => 'textarea')
    ),
    
  )
))->render(function($form_conf) { ?>
  
  <div class="row">
    <div class="small-12 columns">
      %form_messages% // <- This is where success or error messages will print
    </div>
  </div>
  
  <div class="row">
    <div class="small-12 columns">
      %first_name%
    </div>
  </div>
  
  <div class="row">
    <div class="small-12 columns">
      %last_name%
    </div>
  </div>
  
  <div class="row">
    <div class="small-12 columns">
      %email%
    </div>
  </div>

  <div class="row">
    <div class="small-12 columns">
      %message%
    </div>
  </div>
  
  <div class="row">
    <div class="small-12 columns">
      <button type="submit">Submit</button>
    </div>
  </div>
  
  
<?php }); ?>
```


More to come...





