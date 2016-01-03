<?php

// Cache busting
// This automatically pulls from main.scss
define('THEME_VERSION', (ENVIRONMENT !== ENVIRONMENT_PROD) ? mt_rand() : (int) preg_replace('/((?:.*)\$version\:[\s]{1,}([\d]{1,});(?:.*))/si', '$2', file_get_contents(dirname(__FILE__).'/_/scss/main.scss')));
define('THEME_SUFFIX', sprintf('?v=%s', THEME_VERSION));
define('USER_SUPER_ADMIN', 'admin'); // vermilion_admin