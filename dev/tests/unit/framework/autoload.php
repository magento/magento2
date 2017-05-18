<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionGeneratorAutoloader;
use Magento\Framework\TestFramework\Unit\Autoloader\GeneratedClassesAutoloader;
use Magento\Framework\TestFramework\Unit\Autoloader\ObjectManager;

$generatorIo = new Io(
    new File(),
    TESTS_TEMP_DIR . '/' . DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_CODE][DirectoryList::PATH]
);
spl_autoload_register([new ExtensionGeneratorAutoloader($generatorIo), 'load']);

$codeGenerator = new \Magento\Framework\Code\Generator(
    $generatorIo,
    [Factory::ENTITY_TYPE => Factory::class]
);
$codeGenerator->setObjectManager(new ObjectManager());
spl_autoload_register([new GeneratedClassesAutoloader($codeGenerator), 'load']);
