<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once __DIR__ . '/../../../../app/autoload.php';

$testsBaseDir = dirname(__DIR__);
$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\TestFramework\\', $testsBaseDir . '/framework/Magento/TestFramework/');
