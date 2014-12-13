<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Logger;

/**
 * Log Adapter
 */
class Adapter
{
    /**
     * Log file name
     *
     * @var string
     */
    protected $_logFileName = '';

    /**
     * Data to log
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Fields that should be replaced in debug data with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = [];

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * Set log file name
     *
     * @param \Magento\Framework\Logger $logger
     * @param string $fileName
     */
    public function __construct(\Magento\Framework\Logger $logger, $fileName)
    {
        $this->_logFileName = $fileName;
        $this->_logger = $logger;
    }

    /**
     * Perform forced log data to file
     *
     * @param mixed $data
     * @return $this
     */
    public function log($data = null)
    {
        if ($data === null) {
            $data = $this->_data;
        } else {
            if (!is_array($data)) {
                $data = [$data];
            }
        }
        $data = $this->_filterDebugData($data);
        $data['__pid'] = getmypid();
        $this->_logger->logFile($data, \Zend_Log::DEBUG, $this->_logFileName);
        return $this;
    }

    /**
     * Log data setter
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * Setter for private data keys, that should be replaced in debug data with '***'
     *
     * @param array $keys
     * @return $this
     */
    public function setFilterDataKeys($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $this->_debugReplacePrivateDataKeys = $keys;
        return $this;
    }

    /**
     * Recursive filter data by private conventions
     *
     * @param mixed $debugData
     * @return string|array
     */
    protected function _filterDebugData($debugData)
    {
        if (is_array($debugData) && is_array($this->_debugReplacePrivateDataKeys)) {
            foreach ($debugData as $key => $value) {
                if (in_array($key, $this->_debugReplacePrivateDataKeys)) {
                    $debugData[$key] = '****';
                } else {
                    if (is_array($debugData[$key])) {
                        $debugData[$key] = $this->_filterDebugData($debugData[$key]);
                    }
                }
            }
        }
        return $debugData;
    }
}
