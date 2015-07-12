<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$autoloader = new \Magento\Framework\TestFramework\Unit\Autoloader\ExtensionGeneratorAutoloader(
    new \Magento\Framework\Code\Generator\Io(
        new \Magento\Framework\Filesystem\Driver\File(),
        TESTS_TEMP_DIR . '/var/generation'
    )
);
spl_autoload_register([$autoloader, 'load']);
