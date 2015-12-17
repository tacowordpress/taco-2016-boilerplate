<?php

// autoload vendor files
require_once realpath(__DIR__.'/taco-app/vendor/autoload.php');

// Initialize Taco
\Taco\Loader::init();

require_once dirname(__FILE__).'/taco-app/config.php';
require_once dirname(__FILE__).'/taco-app/taco-app.php';

// load any frontned files for composer packages
\TacoApp\CustomLoader::init();

require_once dirname(__FILE__).'/taco-app/functions-taco.php';
require_once dirname(__FILE__).'/taco-app/functions-generic.php';
require_once dirname(__FILE__).'/taco-app/functions-app.php';