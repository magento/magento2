<?php

/*
 * Bootstrap the library.
 */

namespace OAuth;

require_once __DIR__ . '/Common/AutoLoader.php';

$autoloader = new Common\AutoLoader(__NAMESPACE__, dirname(__DIR__));

$autoloader->register();
