<?php

require_once dirname(__FILE__).'/app/config.php';

// load core app files through app.php
require_once dirname(__FILE__).'/app/taco-app.php';

// assign aliases for common util classes
class Arr extends \AppLibrary\Arr {}
class Collection extends \AppLibrary\Collection {}
class Color extends \AppLibrary\Color {}
class Html extends \AppLibrary\Html {}
class Num extends \AppLibrary\Num {}
class Str extends \AppLibrary\Str {}
class States extends \AppLibrary\States {}

require_once dirname(__FILE__).'/app/functions-generic.php';
require_once dirname(__FILE__).'/app/functions-app.php';