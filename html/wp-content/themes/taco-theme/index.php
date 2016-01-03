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
      'state' => array('type' => 'select' , 'options' => \AppLibrary\States::getAll()),
      'subscribe' => array('type' => 'checkbox')
    )
  )
))->render(function($form) { ?>
  <div class="row">
    <div class="small-8 columns">
      <?php echo $form->getTheContent(); ?>
    </div>
  </div>

  <div class="row">
    <div class="small-8 columns">
      %first_name_with_label%
    </div>
  </div>

  <div class="row">
    <div class="small-8 columns">
      %last_name_with_label%
    </div>
  </div>

  <div class="row">
    <div class="small-8 columns">
      %email_address%
    </div>
  </div>

  <div class="row">
    <div class="small-8 columns">
      %essay%
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
<?php }); ?>



<?php get_footer(); ?>