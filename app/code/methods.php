<?php

require_once __DIR__ . '/../autoload.php';


// Does not support flag GLOB_BRACE
function glob_recursive($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
    }
    return $files;
}

$files = glob_recursive('*.php');


$data = [];
foreach ($files as $file) {
    if($file === 'methods.php'
        || (bool) stripos($file, 'interface')
        || (bool) stripos($file, 'unit')
    ) {
        continue;
    }

    $class = str_replace('.php','',$file);
    $class = str_replace('/','\\',$class);
    $class = str_replace('.\\','',$class);
    try {

        $reflectionClass = new ReflectionClass($class);

        foreach ($reflectionClass->getMethods() as $method) {
            if(!$method->isPrivate()) {
                continue;
            }
            $data[$method->getName()] = '';
        }
        echo $class . PHP_EOL;
    } catch (\Exception $e) {

    }

}

$count = count($data);

ksort($data);

file_put_contents('methods.json', json_encode($data, JSON_PRETTY_PRINT));