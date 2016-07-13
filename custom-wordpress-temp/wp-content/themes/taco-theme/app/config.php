<?php

// Cache busting
// This automatically pulls from main.scss
define('THEME_VERSION', (ENVIRONMENT !== ENVIRONMENT_PROD) ? mt_rand() : (int) preg_replace('/((?:.*)\$version\:\s+(\d+);(?:.*))/si', '$2', file_get_contents(__DIR__.'/_/scss/main.scss')));
define('THEME_SUFFIX', sprintf('?v=%s', THEME_VERSION));
define('USER_SUPER_ADMIN', 'vermilion_admin'); // vermilion_admin