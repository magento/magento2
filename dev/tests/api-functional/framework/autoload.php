<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require_once __DIR__ . '/../../../../app/autoload.php';

$testsBaseDir = dirname(__DIR__);
$integrationTestsDir = realpath("{$testsBaseDir}/../integration/");

$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\TestFramework\\', "{$testsBaseDir}/framework/Magento/TestFramework/");
$autoloadWrapper->addPsr4('Magento\\TestFramework\\', "{$integrationTestsDir}/framework/Magento/TestFramework/");
$autoloadWrapper->addPsr4('Magento\\', "{$testsBaseDir}/testsuite/Magento/");
