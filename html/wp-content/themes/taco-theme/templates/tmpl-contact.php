<?php /* Template Name: Contact */ ?>
<?php
get_header();
$app_option = AppOption::getInstance();
?>

<div class="contact-info" itemscope itemtype="http://schema.org/Organization">
  <span itemprop="name">Client Name</span>
  <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
    <span itemprop="streetAddress">
      <?php echo $app_option->get('contact_street'); ?>
    </span>
    <span itemprop="addressLocality">
      <?php echo $app_option->get('contact_city'); ?>
    </span>,
    <span itemprop="addressRegion">
      <?php echo $app_option->get('contact_state'); ?>
    </span>
    <span itemprop="postalCode">
      <?php echo $app_option->get('contact_zip'); ?>
    </span>
  </div>
  <span itemprop="telephone">
    <?php echo $app_option->get('contact_phone'); ?>
  </span>
  <span itemprop="faxNumber">[fax number]</span>,
  <span itemprop="email">
    <a href="mailto:<?php echo $app_option->get('contact_email'); ?>"><?php echo $app_option->get('contact_email'); ?></a>
  </span>
</div><?php // end contact info ?>

<div class="contact-form">
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
</div><?php // end contact form ?>

<?php get_footer(); ?>
