<?php

// autoload vendor files (composer backend)
require_once realpath(__DIR__.'/core/vendor/autoload.php');

// load frontend files from composer dir
require_once __DIR__.'/core/CustomLoader.php';
\TacoApp\CustomLoader::init();

// let's autoload some files
// load the psr-4 autoloader class file
require_once __DIR__.'/core/Psr4AutoloaderClass.php';
$loader = new Psr4AutoloaderClass;
$loader->register();

// assign namespaces and their corresponding autoload paths here
$loader->addNamespace('\AppLibrary\\', __DIR__.'/lib/AppLibrary/src');

// Initialize Taco
\Taco\Loader::init();


// traits
require_once __DIR__.'/traits/Taquito.php';

// settings
require_once __DIR__.'/posts/AppOption.php';

//posts
require_once __DIR__.'/posts/Post.php';
require_once __DIR__.'/posts/Page.php';

//terms
