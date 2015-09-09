<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/../../../../app/autoload.php';

$testsBaseDir = dirname(__DIR__);
$integrationTestsDir = realpath("{$testsBaseDir}/../integration/");

$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\TestFramework\\', "{$testsBaseDir}/framework/Magento/TestFramework/");
$autoloadWrapper->addPsr4('Magento\\TestFramework\\', "{$integrationTestsDir}/framework/Magento/TestFramework/");
$autoloadWrapper->addPsr4('Magento\\', "{$testsBaseDir}/testsuite/Magento/");

// registration of name spaces under '../_files'
$_filesDir = realpath("{$testsBaseDir}/_files");

// TestModule1
$autoloadWrapper->addPsr4('Magento\\TestModule1\\Controller\\CookieTester', "{$_filesDir}/TestModule1/Controller/CookieTester/");
$autoloadWrapper->addPsr4('Magento\\TestModule1\\Controller', "{$_filesDir}/TestModule1/Controller/");
$autoloadWrapper->addPsr4('Magento\\TestModule1\\Service\\V1', "{$_filesDir}/TestModule1/Service/V1/");
$autoloadWrapper->addPsr4('Magento\\TestModule1\\Service\\V1\\Entity', "{$_filesDir}/TestModule1/Service/V1/Entity/");
$autoloadWrapper->addPsr4('Magento\\TestModule1\\Service\\V2', "{$_filesDir}/TestModule1/Service/V2/");
$autoloadWrapper->addPsr4('Magento\\TestModule1\\Service\\V2\\Entity', "{$_filesDir}/TestModule1/Service/V2/Entity/");

// TestModule2
$autoloadWrapper->addPsr4('Magento\\TestModule2\\Controller\\CookieTester', "{$_filesDir}/TestModule2/Controller/CookieTester/");
$autoloadWrapper->addPsr4('Magento\\TestModule2\\Controller', "{$_filesDir}/TestModule2/Controller/");
$autoloadWrapper->addPsr4('Magento\\TestModule2\\Service\\V1', "{$_filesDir}/TestModule2/Service/V1/");
$autoloadWrapper->addPsr4('Magento\\TestModule2\\Service\\V1\\Entity', "{$_filesDir}/TestModule2/Service/V1/Entity/");
$autoloadWrapper->addPsr4('Magento\\TestModule2\\Service\\V2', "{$_filesDir}/TestModule2/Service/V2/");
$autoloadWrapper->addPsr4('Magento\\TestModule2\\Service\\V2\\Entity', "{$_filesDir}/TestModule2/Service/V2/Entity/");

// TestModule3
$autoloadWrapper->addPsr4('Magento\\TestModule3\\Controller\\CookieTester', "{$_filesDir}/TestModule3/Controller/CookieTester/");
$autoloadWrapper->addPsr4('Magento\\TestModule3\\Controller', "{$_filesDir}/TestModule3/Controller/");
$autoloadWrapper->addPsr4('Magento\\TestModule3\\Service\\V1', "{$_filesDir}/TestModule3/Service/V1/");
$autoloadWrapper->addPsr4('Magento\\TestModule3\\Service\\V1\\Entity', "{$_filesDir}/TestModule3/Service/V1/Entity/");
$autoloadWrapper->addPsr4('Magento\\TestModule3\\Service\\V2', "{$_filesDir}/TestModule3/Service/V2/");
$autoloadWrapper->addPsr4('Magento\\TestModule3\\Service\\V2\\Entity', "{$_filesDir}/TestModule3/Service/V2/Entity/");

// TestModule4
$autoloadWrapper->addPsr4('Magento\\TestModule4\\Controller\\CookieTester', "{$_filesDir}/TestModule4/Controller/CookieTester/");
$autoloadWrapper->addPsr4('Magento\\TestModule4\\Controller', "{$_filesDir}/TestModule4/Controller/");
$autoloadWrapper->addPsr4('Magento\\TestModule4\\Service\\V1', "{$_filesDir}/TestModule4/Service/V1/");
$autoloadWrapper->addPsr4('Magento\\TestModule4\\Service\\V1\\Entity', "{$_filesDir}/TestModule4/Service/V1/Entity/");
$autoloadWrapper->addPsr4('Magento\\TestModule4\\Service\\V2', "{$_filesDir}/TestModule4/Service/V2/");
$autoloadWrapper->addPsr4('Magento\\TestModule4\\Service\\V2\\Entity', "{$_filesDir}/TestModule4/Service/V2/Entity/");

// TestModule5
$autoloadWrapper->addPsr4('Magento\\TestModule5\\Controller\\CookieTester', "{$_filesDir}/TestModule5/Controller/CookieTester/");
$autoloadWrapper->addPsr4('Magento\\TestModule5\\Controller', "{$_filesDir}/TestModule5/Controller/");
$autoloadWrapper->addPsr4('Magento\\TestModule5\\Service\\V1', "{$_filesDir}/TestModule5/Service/V1/");
$autoloadWrapper->addPsr4('Magento\\TestModule5\\Service\\V1\\Entity', "{$_filesDir}/TestModule5/Service/V1/Entity/");
$autoloadWrapper->addPsr4('Magento\\TestModule5\\Service\\V2', "{$_filesDir}/TestModule5/Service/V2/");
$autoloadWrapper->addPsr4('Magento\\TestModule5\\Service\\V2\\Entity', "{$_filesDir}/TestModule5/Service/V2/Entity/");

// TestModuleIntegrationFromConfig
$autoloadWrapper->addPsr4('Magento\TestModuleIntegrationFromConfig\Setup', "{$_filesDir}/TestModuleIntegrationFromConfig/Setup/");

// TestModuleJoinDirectives
$autoloadWrapper->addPsr4('Magento\\TestModuleJoinDirectives\\Api', "{$_filesDir}/TestModuleJoinDirectives/Api/");
$autoloadWrapper->addPsr4('Magento\\TestModuleJoinDirectives\\Model', "{$_filesDir}/TestModuleJoinDirectives/Model/");

// TestModuleMSC
$autoloadWrapper->addPsr4('Magento\\TestModuleMSC\\Api', "{$_filesDir}/TestModuleMSC/Api/");
$autoloadWrapper->addPsr4('Magento\\TestModuleMSC\\Api\\Data', "{$_filesDir}/TestModuleMSC/Api/Data/");
$autoloadWrapper->addPsr4('Magento\\TestModuleMSC\\Model', "{$_filesDir}/TestModuleMSC/Model/");
$autoloadWrapper->addPsr4('Magento\\TestModuleMSC\\Model\\Data', "{$_filesDir}/TestModuleMSC/Model/Data/");
$autoloadWrapper->addPsr4('Magento\\TestModuleMSC\\Model\\Resource', "{$_filesDir}/TestModuleMSC/Model/Resource");



