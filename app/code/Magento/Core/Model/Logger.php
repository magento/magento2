<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Logger model
 */
namespace Magento\Core\Model;

class Logger
{
    /**#@+
     * Keys that stand for particular log streams
     */
    const LOGGER_SYSTEM    = 'system';
    const LOGGER_EXCEPTION = 'exception';
    /**#@-*/

    /**
     * @var array
     */
    protected $_loggers = array();

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirs = null;

    /**
     * @var \Magento\Io\File
     */
    protected $_fileSystem;

    /**
     * @param \Magento\App\Dir $dirs
     * @param \Magento\Io\File $fileSystem
     * @param string $defaultFile
     */
    public function __construct(\Magento\App\Dir $dirs, \Magento\Io\File $fileSystem, $defaultFile = '')
    {
        $this->_dirs = $dirs;
        $this->_fileSystem = $fileSystem;
        $this->addStreamLog(\Magento\Core\Model\Logger::LOGGER_SYSTEM, $defaultFile)
            ->addStreamLog(\Magento\Core\Model\Logger::LOGGER_EXCEPTION, $defaultFile);
    }

    /**
     * Add a logger by specified key
     *
     * Second argument is a file name (relative to log directory) or a PHP "wrapper"
     *
     * @param string $loggerKey
     * @param string $fileOrWrapper
     * @param string $writerClass
     * @return \Magento\Core\Model\Logger
     * @link http://php.net/wrappers
     */
    public function addStreamLog($loggerKey, $fileOrWrapper = '', $writerClass = '')
    {
        $file = $fileOrWrapper ?: "{$loggerKey}.log";
        if (!preg_match('#^[a-z][a-z0-9+.-]*\://#i', $file)) {
            $logDir = $this->_dirs->getDir(\Magento\App\Dir::LOG);
            $this->_fileSystem->checkAndCreateFolder($logDir);
            $file = $logDir . DIRECTORY_SEPARATOR . $file;
        }
        if (!$writerClass || !is_subclass_of($writerClass, 'Zend_Log_Writer_Stream')) {
            $writerClass = 'Zend_Log_Writer_Stream';
        }
        /** @var $writer \Zend_Log_Writer_Stream */
        $writer = $writerClass::factory(array('stream' => $file));
        $writer->setFormatter(
            new \Zend_Log_Formatter_Simple('%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL)
        );
        $this->_loggers[$loggerKey] = new \Zend_Log($writer);
        return $this;
    }

    /**
     * Reset all loggers and initialize them according to store configuration
     *
     * @param \Magento\Core\Model\Store $store
     * @param \Magento\Core\Model\ConfigInterface $config
     */
    public function initForStore(\Magento\Core\Model\Store $store, \Magento\Core\Model\ConfigInterface $config)
    {
        $this->_loggers = array();
        if ($store->getConfig('dev/log/active')) {
            $writer = (string)$config->getNode('global/log/core/writer_model');
            $this->addStreamLog(self::LOGGER_SYSTEM, $store->getConfig('dev/log/file'), $writer);
            $this->addStreamLog(self::LOGGER_EXCEPTION, $store->getConfig('dev/log/exception_file'), $writer);
        }
    }

    /**
     * Add a logger if store configuration allows
     *
     * @param string $loggerKey
     * @param \Magento\Core\Model\Store $store
     */
    public function addStoreLog($loggerKey, \Magento\Core\Model\Store $store)
    {
        if ($store->getConfig('dev/log/active')) {
            $this->addStreamLog($loggerKey);
        }
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
     */
    public function logDebug($message, $loggerKey = self::LOGGER_SYSTEM)
    {
        $this->log($message, \Zend_Log::DEBUG, $loggerKey);
    }

    /**
     * Log an exception
     *
     * @param \Exception $e
     */
    public function logException(\Exception $e)
    {
        $this->log("\n" . $e->__toString(), \Zend_Log::ERR, self::LOGGER_EXCEPTION);
    }
}
