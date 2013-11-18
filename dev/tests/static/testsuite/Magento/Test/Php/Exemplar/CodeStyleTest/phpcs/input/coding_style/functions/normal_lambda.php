<?php
do_something(function ($argument) {
    if ($argument) {
        do_something_else();
    }
});

$greet = function ($name)
{
    echo "Hello, {$name}!";
};
