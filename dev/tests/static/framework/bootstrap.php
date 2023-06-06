<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// phpcs:disable

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Framework\View\Design\Theme\ThemePackageFactory;

require __DIR__ . '/autoload.php';

error_reporting(E_ALL);
if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/tmp');
}

// PHP 8 compatibility. Define constants that are not present in PHP < 8.0
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 80000) {
    if (!defined('T_NAME_QUALIFIED')) {
        define('T_NAME_QUALIFIED', 24001);
    }
    if (!defined('T_NAME_FULLY_QUALIFIED')) {
        define('T_NAME_FULLY_QUALIFIED', 24002);
    }
}

setCustomErrorHandler();

$componentRegistrar = new ComponentRegistrar();
$dirSearch = new DirSearch($componentRegistrar, new ReadFactory(new DriverPool()));
$themePackageList = new ThemePackageList($componentRegistrar, new ThemePackageFactory());
$serializer = new \Magento\Framework\Serialize\Serializer\Json();
$regexIteratorFactory = new Magento\Framework\App\Utility\RegexIteratorFactory();
\Magento\Framework\App\Utility\Files::setInstance(
    new Files($componentRegistrar, $dirSearch, $themePackageList, $serializer, $regexIteratorFactory)
);

/**
 * Set custom error handler
 */
function setCustomErrorHandler()
{
    set_error_handler(
        function ($errNo, $errStr, $errFile, $errLine) {
            $errLevel = error_reporting();
            if (($errLevel & $errNo) !== 0) {
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

                throw new \PHPUnit\Framework\Exception(
                    sprintf("%s: %s in %s:%s.", $errName, $errStr, $errFile, $errLine),
                    $errNo
                );
            }
        }
    );
}
