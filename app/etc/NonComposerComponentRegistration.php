<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$pathList[] = dirname(__DIR__) . '/code/*/*/cli_commands.php';
$pathList[] = dirname(__DIR__) . '/code/*/*/registration.php';
$pathList[] = dirname(__DIR__) . '/design/*/*/*/registration.php';
$pathList[] = dirname(__DIR__) . '/i18n/*/*/registration.php';
$pathList[] = dirname(dirname(__DIR__)) . '/lib/internal/*/*/registration.php';
$pathList[] = dirname(dirname(__DIR__)) . '/lib/internal/*/*/*/registration.php';
foreach ($pathList as $path) {
    // Sorting is disabled intentionally for performance improvement
    $files = glob($path, GLOB_NOSORT);
    if ($files === false) {
        throw new \RuntimeException('glob() returned error while searching in \'' . $path . '\'');
    }
    foreach ($files as $file) {
        include $file;
    }
}
