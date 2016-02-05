<?php /* Template Name: contact */ ?>
<?php get_header(); ?>
<style>.hide_label { display: none; }</style>


<?php $contact_form = new TacoForm(
  array(
    'conf_name' => 'Contact Form Configuration',
    'fields' => 'auto',
    'column_classes' => 'small-12 medium-8 medium-centered columns',
    'novalidate' => true,
    'hide_labels' => true
  )
); ?>

<?php
echo $contact_form->render(function($form_conf) { ?>
  
  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
      %post_content%
    </div>
  </div>

  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
      %form_messages%
    </div>
  </div>

  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
      %first_name|required=true%
    </div>
  </div>

  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
      %last_name%
    </div>
  </div>

  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
      %email|required=true%
    </div>
  </div>

  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
      %message|type=textarea|required=true%
    </div>
  </div>

  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
     <button type="submit">Submit</button>
    </div>
  </div>

  <div class="row">
    <div class="small-12 medium-8 medium-centered columns">
      %edit_link%
    </div>
  </div>
<?php }); ?>


<?php get_footer(); ?>