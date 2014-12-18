<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestFramework\ErrorLog;

class Logger extends \Magento\Framework\Logger
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

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $defaultFile
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, $defaultFile = '')
    {
        parent::__construct($filesystem, $defaultFile);
        $this->minimumErrorLevel = defined('TESTS_ERROR_LOG_LISTENER_LEVEL') ? TESTS_ERROR_LOG_LISTENER_LEVEL : -1;
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

    /**
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = \Zend_Log::DEBUG)
    {
        if ($level <= $this->minimumErrorLevel) {
            $this->messages[] = [
                'level' => $level,
                'message' => $message,
            ];
        }
        parent::log($message, $level);
    }
}
