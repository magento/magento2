<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework;

/**
 * Logger model
 */
class Logger
{
    /**#@-*/

    /**
     * @var array
     */
    protected $_loggers = [];

    /**
     * Log a message
     *
     * @param string $message
     * @param int $level
     * @return void
     */
    public function log($message, $level = \Zend_Log::DEBUG)
    {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        $this->log($message, $level);
    }

    /**
     * Log a message in specific file
     *
     * @param string $message
     * @param int $level
     * @param string $file
     * @return void
     */
    public function logFile($message, $level = \Zend_Log::DEBUG, $file = '')
    {
        if (!isset($file)) {
            $this->log($message, $level);
        }
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        /** @var $logger \Zend_Log */
        $this->log($message, $level, $file);
    }

    /**
     * Log a message with "debug" level
     *
     * @param string $message
     * @param string $loggerKey
     * @return void
     */
    public function logDebug($message)
    {
        $this->log($message, \Zend_Log::DEBUG);
    }

    /**
     * Log an exception
     *
     * @param \Exception $e
     * @return void
     */
    public function critical(\Exception $e)
    {
        $this->log("\n" . $e->__toString(), \Zend_Log::ERR);
    }
}
