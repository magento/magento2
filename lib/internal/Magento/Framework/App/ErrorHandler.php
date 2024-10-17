<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\Exception\ErrorHandlerException;

/**
 * An error handler that converts runtime errors into exceptions
 */
class ErrorHandler
{
    /**
     * Error messages
     *
     * @var array
     */
    protected $errorPhrases = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated Functionality',
        E_USER_DEPRECATED => 'User Deprecated Functionality',
    ];

    /**
     * Custom error handler
     *
     * @param int $errorNo
     * @param string $errorStr
     * @param string $errorFile
     * @param int $errorLine
     * @return bool
     * @throws \Exception
     */
    public function handler($errorNo, $errorStr, $errorFile, $errorLine)
    {
        if ($errorStr !== null && strpos($errorStr, 'DateTimeZone::__construct') !== false) {
            // there's no way to distinguish between caught system exceptions and warnings
            return false;
        }

        $errorNo = $errorNo & error_reporting();
        if ($errorNo == 0) {
            return false;
        }

        $msg = isset($this->errorPhrases[$errorNo]) ? $this->errorPhrases[$errorNo] : "Unknown error ({$errorNo})";
        $msg .= ": {$errorStr} in {$errorFile} on line {$errorLine}";
        // phpcs:ignore Magento2.Exceptions.DirectThrow
        throw new ErrorHandlerException($msg, 0, $errorNo, $errorFile, $errorLine);
    }
}
