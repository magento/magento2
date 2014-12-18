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
     * @internal param int $level
     */
    public function info($message)
    {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        //$this->log($message, $level);
    }

    /**
     * Log a message with "debug" level
     *
     * @param string $message
     * @return void
     */
    public function debug($message)
    {
        $this->info($message);
    }

    /**
     * Log an exception
     *
     * @param \Exception $e
     * @return void
     */
    public function critical(\Exception $e)
    {
        $this->info("\n" . $e->__toString());
    }
}
