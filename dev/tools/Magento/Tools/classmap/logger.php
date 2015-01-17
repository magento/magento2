<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$classes = get_declared_classes();
foreach ($classes as $index => $class) {
    if (strpos($class, '_') === false) {
        unset($classes[$index]);
    }
}
sort($classes);
$file = __DIR__ . '/log/magento' . trim(str_replace('/', '_', $_SERVER['REQUEST_URI']), '_') . '.ser';
file_put_contents($file, serialize($classes));
