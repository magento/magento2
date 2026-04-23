<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

require_once __DIR__ . '/../../../../app/autoload.php';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/tmp');
}

require_once __DIR__ . '/autoload.php';

setCustomErrorHandler();

\Magento\Framework\Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (extension_loaded('xdebug')) {
    ini_set('xdebug.max_nesting_level', '200');
}

/*  For data consistency between displaying (printing) and serialization a float number */
ini_set('precision', 14);
ini_set('serialize_precision', 14);

/**
 * PHPUnit error handler (set_error_handler callback; named for PHPMD).
 *
 * @param int $errNo
 * @param string $errStr
 * @param string $errFile
 * @param int $errLine
 * @return bool
 */
function magentoUnitTestsPhpErrorHandler(int $errNo, string $errStr, string $errFile, int $errLine): bool
{
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
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        $errName = $errorNames[$errNo] ?? '';

        throw new \PHPUnit\Framework\Exception(
            sprintf('%s: %s in %s:%s.', $errName, $errStr, $errFile, $errLine),
            $errNo
        );
    }

    return false;
}

/**
 * Set custom error handler
 */
function setCustomErrorHandler()
{
    set_error_handler('magentoUnitTestsPhpErrorHandler');
}
