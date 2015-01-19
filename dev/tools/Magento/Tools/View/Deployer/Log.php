<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * If last output printed inline
     *
     * @var bool
     */
    private $isInline = false;

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
            $this->terminateLine();
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
            $this->terminateLine();
            echo "ERROR: {$msg}\n";
        }
    }

    /**
     * Log a debug message
     *
     * @param string $msg
     * @param string $altInline Alternative message for normal mode (printed inline)
     * @return void
     */
    public function logDebug($msg, $altInline = '')
    {
        if ($this->verbosity & self::DEBUG) {
            $this->terminateLine();
            echo "{$msg}\n";
        } elseif ($altInline && $this->verbosity !== self::SILENT) {
            echo $altInline;
            $this->isInline = true;
        }
    }

    /**
     * Ensures the next log message will be printed on new line
     *
     * @return void
     */
    private function terminateLine()
    {
        if ($this->isInline) {
            $this->isInline = false;
            echo "\n";
        }
    }
}
