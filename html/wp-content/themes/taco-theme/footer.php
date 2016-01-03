</div>
<!--/.page-wrap -->
something needed in the footer?



<?php wp_footer(); ?>

<script>
  //$(document).foundation();
</script>
</body>
</html>
<?php
if(class_exists('FlashData')) {
  $flash_data = new FlashData;
  $flash_data->flush();
}