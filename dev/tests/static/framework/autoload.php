<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../../app/autoload.php';
$testsBaseDir = realpath(__DIR__ . '/../');

$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\', $testsBaseDir . '/testsuite/Magento/');
$autoloadWrapper->addPsr4('Magento\\TestFramework\\', $testsBaseDir . '/framework/Magento/TestFramework/');
