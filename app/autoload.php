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

/**
 * Ensure php backwards compatibility of laminas-code module
 *
 * Can be removed once https://github.com/laminas/laminas-code/pull/73 is released to 3.5.x
 * or PHP minimum version is 8.0
 * or laminas-code module is updated to version 4.0+ with tested PHP 7.4+ support
 */
if (!defined('T_NAME_QUALIFIED')) {
    define('T_NAME_QUALIFIED', 24001);
}
if (! defined('T_NAME_FULLY_QUALIFIED')) {
    define('T_NAME_FULLY_QUALIFIED', 24002);
}

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
