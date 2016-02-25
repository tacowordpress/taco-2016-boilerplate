<?php
get_header();
//setup the page
$page = \Taco\Post\Factory::create($post);
?>

on the page
          
<?php echo $page->getTheTitle(); ?>
<?php echo $page->getTheContent(); ?>
    

<?php get_footer(); ?>