<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Identify class list files */
if (isset($argv[1]) && realpath($argv[1])) {
    $path = realpath($argv[1]);
} else {
    $path = __DIR__ . '/log';
}

if (is_dir($path)) {
    $files = glob($path . '/*.ser');
} else {
    $files = [$path];
}

/* Load class names array */
$classes = [];
foreach ($files as $file) {
    $fileClasses = unserialize(file_get_contents($file));
    $classes = array_merge($classes, $fileClasses);
}

sort($classes);
$baseDir = realpath(__DIR__ . '/../../../../../') . '/';
$sources = ['app/code', 'lib/internal'];

$map = [];
foreach ($classes as $class) {
    $file = '/' . str_replace('_', '/', $class) . '.php';
    foreach ($sources as $folder) {
        $classFile = $baseDir . $folder . $file;
        if (file_exists($classFile)) {
            $map[$class] = $folder . $file;
        }
    }
}

echo serialize($map);
