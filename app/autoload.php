<?php
/**
 * Register basic autoloader that uses include path
 *
 * Copyright 2012 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Autoload\ClassLoaderWrapper;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable Magento2.Functions.DiscouragedFunction
// phpcs:disable Magento2.Security.IncludeFile
// phpcs:disable Magento2.Exceptions.DirectThrow

/**
 * Shortcut constant for the root directory
 */
\define('BP', \dirname(__DIR__));

\define('VENDOR_PATH', BP . '/app/etc/vendor_path.php');

$vendorAutoload = (
    static function (): ?string {
        if (\is_readable(VENDOR_PATH)) {
            $vendorDir = require VENDOR_PATH;
        } elseif (\file_exists(VENDOR_PATH) || !\is_readable(\dirname(VENDOR_PATH))) {
            throw new \Exception(
                'We can\'t read some files that are required to run the Magento application. '
                . 'This usually means file permissions are set incorrectly.'
            );
        } else {
            $vendorDir = 'vendor';
        }

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
