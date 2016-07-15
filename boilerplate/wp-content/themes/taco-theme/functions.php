<?php

require_once __DIR__.'/app/config.php';

// load core app files through app.php
require_once __DIR__.'/app/taco-app.php';

// assign aliases for common util classes
class Arr extends \Taco\Util\Arr {}
class Collection extends \Taco\Util\Collection {}
class Color extends \Taco\Util\Color {}
class Html extends \Taco\Util\Html {}
class Num extends \Taco\Util\Num {}
class Obj extends \Taco\Util\Obj {}
class Str extends \Taco\Util\Str {}
class States extends \AppLibrary\States {}

require_once __DIR__.'/functions/functions-generic.php';
require_once __DIR__.'/functions/functions-app.php';