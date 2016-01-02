<?php get_header(); ?>
<style>.hide_label { display: none; }</style>
<?php
echo (new TacoForm(
  array(
    'conf_name' => 'RSVP test form',
    'hide_labels' => false,
    'success_message' => 'great!',
    'error_message' => 'not great!',
    'novalidate' => true,
    'fields' =>  array(
      'first_name' => array('type' => 'text', 'required' => true),
      'last_name' => array('type' => 'text'),
      'email_address' => array('type' => 'email', 'required' => true),
      'essay' => array('type' => 'textarea', 'maxlength' => 400),
      'subscribe' => array('type' => 'checkbox')
    )
  )
))->render(); exit; ?>
?>


<!-- old -->
<?php $form = FormConfig::find(29); ?>
<?php
  echo $form->render(
    array(
      'css_class' => 'site-general-contact-form',
      'id' => 'asfasf',
      'method' => 'post',
      'hide_labels' => true,
      'column_classes' => 'small-12 columns medium-6 columns medium-centered',
      'exclude_post_content' => false,
      'field_requirements' => array(
        'first_name' => array('required'),
        'email' => array('required', 'email'),
      )
    )
  ); ?>


  <?php
  echo $form->render(
    array(
      'custom_template' => true,
      'exclude_post_content' => true,
      'success_message' => 'Yay! Form success!',
      'error_message' => 'Nah! Try again!',
      'use_ajax' => false,
    ),
    function($form) { ?>

      <div class="row">
        <div class="small-8 columns">
          <?php echo $form->getTheContent(); ?>
        </div>
      </div>

      <div class="row">
        <div class="small-8 columns">
          %email_error%
          %email_with_label%
        </div>
      </div>

      <div class="row">
        <div class="small-8 columns">
          %last_name_error%
          %last_name_with_label%
        </div>
      </div>

      <div class="row">
        <div class="small-8 columns">
          %address_error%
          %address_with_label%
        </div>
      </div>

      <div class="row">
        <div class="small-8 columns">
          %subscribe_with_label%
        </div>
      </div>

      <div class="row">
        <div class="small-8 columns">
         <button type="submit">Submit</button>
        </div>
      </div>
    <?php }
  ); ?>


<?php get_footer(); ?>