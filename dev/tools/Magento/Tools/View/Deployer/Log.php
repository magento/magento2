<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\View\Deployer;

/**
 * An echo-logger with separating types of messages
 */
class Log
{
    /**#@+
     * Bitmasks for verbosity level
     */
    const SILENT = 0;
    const ERROR = 1;
    const DEBUG = 2;
    /**#@-*/

    /**
     * @var int
     */
    private $verbosity;

    /**
     * @param int $verbosity
     */
    public function __construct($verbosity)
    {
        $this->verbosity = (int)$verbosity;
    }

    /**
     * Log anything
     *
     * @param string $msg
     * @return void
     */
    public function logMessage($msg)
    {
        if ($this->verbosity !== self::SILENT) {
            echo "{$msg}\n";
        }
    }

    /**
     * Log an error
     *
     * @param string $msg
     * @return void
     */
    public function logError($msg)
    {
        if ($this->verbosity & self::ERROR) {
            echo "ERROR: {$msg}\n";
        }
    }

    /**
     * Log a debug message
     *
     * @param string $msg
     * @return void
     */
    public function logDebug($msg)
    {
        if ($this->verbosity & self::DEBUG) {
            echo "{$msg}\n";
        }
    }
}
