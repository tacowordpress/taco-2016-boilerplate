<?php

//setup the page
$page = \Taco\Post\Factory::create($post);

get_header(); ?>

on the page
          
<?php echo $page->getTheTitle(); ?>
<?php echo $page->getTheContent(); ?>
    

<?php get_footer(); ?>