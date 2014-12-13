<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require __DIR__ . '/../../../../app/autoload.php';
$testsBaseDir = realpath(__DIR__ . '/../');

$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\', $testsBaseDir . '/testsuite/Magento/');
$autoloadWrapper->addPsr4('Magento\\TestFramework\\', $testsBaseDir . '/framework/Magento/TestFramework/');
