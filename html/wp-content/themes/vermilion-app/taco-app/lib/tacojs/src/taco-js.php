<?php

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_script(
    'requirejs',
    '/wp-content/taco-app/lib/tacojs/src/scripts/require.js'
  );
  wp_enqueue_script(
    'tacojs',
    '/wp-content/taco-app/lib/tacojs/src/config.js',
    array('requirejs')
  );
});