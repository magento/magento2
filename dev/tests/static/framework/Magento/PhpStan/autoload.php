<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// phpcs:disable

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesGenerator;
use Magento\Framework\TestFramework\Unit\Autoloader\ExtensionAttributesInterfaceGenerator;
use Magento\Framework\TestFramework\Unit\Autoloader\FactoryGenerator;
use Magento\Framework\TestFramework\Unit\Autoloader\GeneratedClassesAutoloader;

if (!defined('TESTS_TEMP_DIR')) {
    //phpcs:ignore Magento2.Functions.DiscouragedFunction
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/../../tmp');
}

$generatorIo = new Io(
    new File(),
    TESTS_TEMP_DIR . '/' . DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_CODE][DirectoryList::PATH]
);
$generatedCodeAutoloader = new GeneratedClassesAutoloader(
    [
        new ExtensionAttributesGenerator(),
        new ExtensionAttributesInterfaceGenerator(),
        new FactoryGenerator(),
    ],
    $generatorIo
);
spl_autoload_register([$generatedCodeAutoloader, 'load']);
