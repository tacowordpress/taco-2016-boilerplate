<?php

require_once dirname(__FILE__).'/taco-app/config.php';

// load core app files through taco-app.php
require_once dirname(__FILE__).'/taco-app/taco-app.php';

// assign aliases for common util classes
class Arr extends \AppLibrary\Arr {}
class Collection extends \AppLibrary\Collection {}
class Color extends \AppLibrary\Color {}
class Html extends \AppLibrary\Html {}
class Num extends \AppLibrary\Num {}
class Str extends \AppLibrary\Str {}
class States extends \AppLibrary\States {}

require_once dirname(__FILE__).'/taco-app/functions-generic.php';
require_once dirname(__FILE__).'/taco-app/functions-app.php';