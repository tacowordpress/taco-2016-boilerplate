<?php

include getenv('HTTP_BOOTSTRAP_WP'); // bootstrap WordPress

header('Content-type: text/plain'); ?>
<?php if(ENVIRONMENT !== ENVIRONMENT_PROD): ?>
User-agent: *
Disallow: /
<?php else: ?>
User-agent: *
Disallow: /wp-admin/
Disallow: /wp-includes/
<?php endif; ?>

#Sitemap: <?php echo get_site_url(); ?>/sitemap.xml