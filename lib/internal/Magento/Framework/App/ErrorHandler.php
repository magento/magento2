<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

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
        if (strpos($errorStr, 'DateTimeZone::__construct') !== false) {
            // there's no way to distinguish between caught system exceptions and warnings
            return false;
        }

        if (strpos($errorStr, 'Automatically populating $HTTP_RAW_POST_DATA is deprecated') !== false) {
            // this warning should be suppressed as it is a known bug in php 5.6.0 https://bugs.php.net/bug.php?id=66763
            // and workaround suggested here (http://php.net/manual/en/ini.core.php#ini.always-populate-raw-post-data)
            // is not compatible with HHVM
            return false;
        }

        $errorNo = $errorNo & error_reporting();
        if ($errorNo == 0) {
            return false;
        }
        $msg = isset($this->errorPhrases[$errorNo]) ? $this->errorPhrases[$errorNo] : "Unknown error ({$errorNo})";
        $msg .= ": {$errorStr} in {$errorFile} on line {$errorLine}";
        throw new \Exception($msg);
    }
}
