<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
