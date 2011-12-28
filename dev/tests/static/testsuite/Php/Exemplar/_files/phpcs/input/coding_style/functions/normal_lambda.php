<?php
spl_autoload_register(function ($class) {
    $file = str_replace('_', '/', $class) . '.php';
    require_once $file;
});

$greet = function ($name)
{
    echo 'Hello!';
};
