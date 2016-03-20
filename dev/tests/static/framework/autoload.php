<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$baseDir = realpath(__DIR__ . '/../../../../');
require $baseDir . '/app/autoload.php';
$testsBaseDir = $baseDir . '/dev/tests/static';
$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\', $testsBaseDir . '/testsuite/Magento/');
$autoloadWrapper->addPsr4(
    'Magento\\TestFramework\\',
    [
        $testsBaseDir . '/framework/Magento/TestFramework/',
        $testsBaseDir . '/../integration/framework/Magento/TestFramework/',
    ]
);
$autoloadWrapper->addPsr4('Magento\\', $baseDir . '/var/generation/Magento/');
