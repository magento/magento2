<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Api\Code\Generator\DataBuilder;
use Magento\Framework\Api\Code\Generator\Mapper;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator;
use Magento\Framework\Api\Code\Generator\SearchResults;
use Magento\Framework\Api\Code\Generator\SearchResultsBuilder;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\ObjectManager\Code\Generator\Converter;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Persistor;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use Magento\Framework\ObjectManager\Code\Generator\Repository;
use Magento\Tools\Di\Code\Scanner;
use Magento\Tools\Di\Compiler\Log\Writer;
use Magento\Tools\Di\Definition\Compressor;

/**
 * Enable code generation for the undeclared classes.
 */
$generationDir = TESTS_TEMP_DIR . '/var/generation';
$generatorIo = new \Magento\Framework\Code\Generator\Io(
    new \Magento\Framework\Filesystem\Driver\File(),
    $generationDir
);
$generator = new \Magento\Framework\Code\Generator(
    $generatorIo,
    [
        Interceptor::ENTITY_TYPE => 'Magento\Framework\Interception\Code\Generator\Interceptor',
        Proxy::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Proxy',
        Factory::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Factory',
        Mapper::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\Mapper',
        Persistor::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Persistor',
        Repository::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Repository',
        Converter::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Converter',
        SearchResults::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\SearchResults',
        ExtensionAttributesInterfaceGenerator::ENTITY_TYPE =>
            'Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator',
        ExtensionAttributesGenerator::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator'
    ]
);
/** Initialize object manager for code generation based on configs */
$magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
$objectManager = $magentoObjectManagerFactory->create($_SERVER);
$generator->setObjectManager($objectManager);

$autoloader = new \Magento\Framework\Code\Generator\Autoloader($generator);
spl_autoload_register([$autoloader, 'load']);
$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\', $generationDir . '/Magento/');
