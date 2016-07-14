<?php
get_header();
// get page
$blog_post = \Taco\Post\Factory::create($post);
?>

on the post
          
<?php echo $blog_post->getTheTitle(); ?>
<?php echo $blog_post->getTheContent(); ?>
    

<?php get_footer(); ?>