<?php

// put taco app related config here

// load frontend files from composer dir
require_once __DIR__.'/CustomLoader.php';

// lib
require_once __DIR__.'/lib/States.php';
require_once __DIR__.'/lib/tacojs/src/taco-js.php';

// traits

// settings
require_once __DIR__.'/posts/ThemeOptionBase.php';
require_once __DIR__.'/posts/ThemeOption.php';

//posts
require_once __DIR__.'/posts/Page.php';
require_once __DIR__.'/posts/Employee.php';

//terms