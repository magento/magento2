<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Logger model
 */
class Logger
{
    /**#@+
     * Keys that stand for particular log streams
     */
    const LOGGER_SYSTEM = 'system';

    const LOGGER_EXCEPTION = 'exception';

    /**#@-*/

    /**
     * @var array
     */
    protected $_loggers = [];

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $defaultFile
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, $defaultFile = '')
    {
        $this->_filesystem = $filesystem;
        $this->addStreamLog(self::LOGGER_SYSTEM, $defaultFile)->addStreamLog(self::LOGGER_EXCEPTION, $defaultFile);
    }

    /**
     * Add a logger by specified key
     *
     * Second argument is a file name (relative to log directory) or a PHP "wrapper"
     *
     * @param string $loggerKey
     * @param string $fileOrWrapper
     * @param string $writerClass
     * @return \Magento\Framework\Logger
     * @link http://php.net/wrappers
     */
    public function addStreamLog($loggerKey, $fileOrWrapper = '', $writerClass = '')
    {
        $file = $fileOrWrapper ?: "{$loggerKey}.log";
        if (!preg_match('#^[a-z][a-z0-9+.-]*\://#i', $file)) {
            $logDir = $this->_filesystem->getDirectoryWrite(DirectoryList::LOG);
            $logDir->create();
            $file = $logDir->getAbsolutePath($file);
        }
        if (!$writerClass || !is_subclass_of($writerClass, 'Zend_Log_Writer_Stream')) {
            $writerClass = 'Zend_Log_Writer_Stream';
        }
        /** @var $writer \Zend_Log_Writer_Stream */
        $writer = $writerClass::factory(['stream' => $file]);
        $writer->setFormatter(
            new \Zend_Log_Formatter_Simple('%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL)
        );
        $this->_loggers[$loggerKey] = new \Zend_Log($writer);
        return $this;
    }

    /**
     * Unset all declared loggers
     *
     * @return $this
     */
    public function unsetLoggers()
    {
        $this->_loggers = [];
        return $this;
    }

    /**
     * Check whether a logger exists by specified key
     *
     * @param string $key
     * @return bool
     */
    public function hasLog($key)
    {
        return isset($this->_loggers[$key]);
    }

    /**
     * Log a message
     *
     * @param string $message
     * @param int $level
     * @param string $loggerKey
     * @return void
     */
    public function log($message, $level = \Zend_Log::DEBUG, $loggerKey = self::LOGGER_SYSTEM)
    {
        if (!isset($this->_loggers[$loggerKey])) {
            return;
        }
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        /** @var $logger \Zend_Log */
        $logger = $this->_loggers[$loggerKey];
        $logger->log($message, $level);
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
        if (!$this->hasLog($file)) {
            $this->addStreamLog($file, $file);
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
    public function logDebug($message, $loggerKey = self::LOGGER_SYSTEM)
    {
        $this->log($message, \Zend_Log::DEBUG, $loggerKey);
    }

    /**
     * Log an exception
     *
     * @param \Exception $e
     * @return void
     */
    public function logException(\Exception $e)
    {
        $this->log("\n" . $e->__toString(), \Zend_Log::ERR, self::LOGGER_EXCEPTION);
    }
}
