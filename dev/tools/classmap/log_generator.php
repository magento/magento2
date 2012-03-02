<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     tools
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    $files = array($path);
}

/* Load class names array */
$classes = array();
foreach ($files as $file) {
    $fileClasses = unserialize(file_get_contents($file));
    $classes = array_merge($classes, $fileClasses);
}

sort($classes);
$baseDir = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR;
$sources = array('app/code/local', 'app/code/community', 'app/code/core', 'lib',);

$map = array();
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
