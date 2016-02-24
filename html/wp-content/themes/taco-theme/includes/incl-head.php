<!doctype html>
<?php
$theme = AppOption::getInstance();
?>
<html class="no-js" <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title><?php echo app_get_page_title(); ?></title>

  <?php include __DIR__.'/incl-open-graph-meta.php'; ?>

  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">

  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

  <?php echo get_app_icons(); ?>

  <script src="<?php echo get_asset_path('lib/modernizr/modernizr.js'); ?>"></script>

  <?php wp_head(); ?>

</head>

<?php global $body_class; ?>
<body <?php body_class((isset($body_class)) ? $body_class : null); ?>>

<?php include __DIR__.'/incl-google-tag-manager.php'; ?>
