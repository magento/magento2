<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\ErrorLog;

class Logger extends \Magento\Framework\Logger\Monolog
{
    /** @var array */
    protected $messages = [];

    /**
     * Minimum error level to log message
     * Possible values: -1 ignore all errors, 2 - log errors with level 0, 1, 2
     * \Zend_Log::EMERG(0) to \Zend_Log::DEBUG(7)
     *
     * @var int
     */
    protected $minimumErrorLevel;

    public function __construct()
    {
        $this->minimumErrorLevel = defined('TESTS_ERROR_LOG_LISTENER_LEVEL') ? TESTS_ERROR_LOG_LISTENER_LEVEL : -1;
        parent::__construct('integration-test');
    }

    /**
     * @return void
     */
    public function clearMessages()
    {
        $this->messages = [];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
