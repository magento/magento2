<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

defined('MTF_BOOT_FILE') || define('MTF_BOOT_FILE', __FILE__);
defined('MTF_BP') || define('MTF_BP', str_replace('\\', '/', (__DIR__)));
defined('MTF_TESTS_PATH') || define('MTF_TESTS_PATH', MTF_BP . '/tests/app/');
defined('MTF_STATES_PATH') || define('MTF_STATES_PATH', MTF_BP . '/lib/Magento/Mtf/App/State/');

require_once __DIR__ . '/../../../app/bootstrap.php';
restore_error_handler();
$vendorAutoload = __DIR__ . '/vendor/autoload.php';

if (isset($composerAutoloader)) {
    /** var $mtfComposerAutoload \Composer\Autoload\ClassLoader */
    $mtfComposerAutoload = include $vendorAutoload;
    $composerAutoloader->addClassMap($mtfComposerAutoload->getClassMap());
} else {
    $composerAutoloader = include $vendorAutoload;
}

setCustomErrorHandler();

/**
 * Set custom error handler
 */
function setCustomErrorHandler()
{
    set_error_handler(
        function ($errNo, $errStr, $errFile, $errLine) {
            if (error_reporting()) {
                $errorNames = [
                    E_ERROR => 'Error',
                    E_WARNING => 'Warning',
                    E_PARSE => 'Parse',
                    E_NOTICE => 'Notice',
                    E_CORE_ERROR => 'Core Error',
                    E_CORE_WARNING => 'Core Warning',
                    E_COMPILE_ERROR => 'Compile Error',
                    E_COMPILE_WARNING => 'Compile Warning',
                    E_USER_ERROR => 'User Error',
                    E_USER_WARNING => 'User Warning',
                    E_USER_NOTICE => 'User Notice',
                    E_STRICT => 'Strict',
                    E_RECOVERABLE_ERROR => 'Recoverable Error',
                    E_DEPRECATED => 'Deprecated',
                    E_USER_DEPRECATED => 'User Deprecated',
                ];

                $errName = isset($errorNames[$errNo]) ? $errorNames[$errNo] : "";

                throw new \PHPUnit_Framework_Exception(
                    sprintf("%s: %s in %s:%s.", $errName, $errStr, $errFile, $errLine),
                    $errNo
                );
            }
        }
    );
}
