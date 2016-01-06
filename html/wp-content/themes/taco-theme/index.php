<?php get_header(); ?>
<style>.hide_label { display: none; }</style>


<?php $contact_form = new TacoForm(
  array(
    'conf_name' => 'test_form_conf',
    'novalidate' => true,
    'hide_labels' => true,
    'fields' => 'auto'
  )
);

echo $contact_form->render(function($form_conf) { ?>
  <div class="row">
    <div class="small-8 columns">
      %post_content%
    </div>
  </div>
  <div class="row">
    <div class="small-8 columns">
      %first_name|required=true%
    </div>
  </div>
  <div class="row">
    <div class="small-8 columns">
      %last_name|required=true%
    </div>
  </div>
  <div class="row">
    <div class="small-8 columns">
      %email|required=true%
    </div>
  </div>
  <div class="row">
    <div class="small-8 columns">
      %bio|maxlength=100%
    </div>
  </div>

  <div class="row">
    <div class="small-8 columns">
      <button type="submit">submit</button>
    </div>
  </div>

  <div class="row">
    <div class="small-8 columns">
      %edit_link%
    </div>
  </div>
<?php }); exit; ?>

<?php get_footer(); ?>