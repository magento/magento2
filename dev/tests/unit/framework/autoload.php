<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Filesystem\DirectoryList;

$autoloader = new \Magento\Framework\TestFramework\Unit\Autoloader\ExtensionGeneratorAutoloader(
    new \Magento\Framework\Code\Generator\Io(
        new \Magento\Framework\Filesystem\Driver\File(),
        TESTS_TEMP_DIR . '/'. DirectoryList::getDefaultConfig()[DirectoryList::GENERATION][DirectoryList::PATH]
    )
);
spl_autoload_register([$autoloader, 'load']);
