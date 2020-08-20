<?php
/**
 * Register basic autoloader that uses include path
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Autoload\ClassLoaderWrapper;

/**
 * Shortcut constant for the root directory
 */
\define('BP', \dirname(__DIR__));

\define('VENDOR_PATH', BP . '/app/etc/vendor_path.php');

if (!\is_readable(VENDOR_PATH)) {
    throw new \Exception(
        'We can\'t read some files that are required to run the Magento application. '
         . 'This usually means file permissions are set incorrectly.'
    );
}

$vendorAutoload = (
    static function (): ?string {
        $vendorDir = require VENDOR_PATH;

        $vendorAutoload = BP . "/{$vendorDir}/autoload.php";
        if (\is_readable($vendorAutoload)) {
            return $vendorAutoload;
        }

        $vendorAutoload = "{$vendorDir}/autoload.php";
        if (\is_readable($vendorAutoload)) {
            return $vendorAutoload;
        }

        return null;
    }
)();

if ($vendorAutoload === null) {
    throw new \Exception(
        'Vendor autoload is not found. Please run \'composer install\' under application root directory.'
    );
}

$composerAutoloader = include $vendorAutoload;
AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));
