<?php

// autoload vendor files (composer backend)
require_once realpath(__DIR__.'/vendor/autoload.php');

// load frontend files from composer dir
require_once __DIR__.'/CustomLoader.php';
\TacoApp\CustomLoader::init();

// let's autoload some files
// load the psr-4 autoloader class file
require_once __DIR__.'/Psr4AutoloaderClass.php';
$loader = new Psr4AutoloaderClass;
$loader->register();

// assign namspaces and their corresponding autoload paths here
$loader->addNamespace('\AppLibrary\\', __DIR__.'/lib/AppLibrary/src');

// Initialize Taco
\Taco\Loader::init();


// traits

// settings
require_once __DIR__.'/posts/ThemeOptionBase.php';
require_once __DIR__.'/posts/ThemeOption.php';

//posts
require_once __DIR__.'/posts/Page.php';

//terms