<?php

require_once __DIR__.'/app/config.php';

// load core app files through app.php
require_once __DIR__.'/app/taco-app.php';

// assign aliases for common util classes
class Arr extends \AppLibrary\Arr {}
class Collection extends \AppLibrary\Collection {}
class Color extends \AppLibrary\Color {}
class Html extends \AppLibrary\Html {}
class Num extends \AppLibrary\Num {}
class Obj extends \AppLibrary\Obj {}
class Str extends \AppLibrary\Str {}
class States extends \AppLibrary\States {}

require_once __DIR__.'/functions/functions-generic.php';
require_once __DIR__.'/functions/functions-app.php';