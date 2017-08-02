<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Operation abstract class
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class AbstractModel extends \Magento\Framework\DataObject
{
    /**
     * Enable loging
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_debugMode = false;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_debugReplacePrivateDataKeys = [];

    /**
     * Contains all log information
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_logTrace = [];

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    protected $_varDirectory;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        array $data = []
    ) {
        $this->_logger = $logger;
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($data);
    }

    /**
     * Log debug data to file.
     * Log file dir: var/log/import_export/%Y/%m/%d/%time%_%operation_type%_%entity_type%.log
     *
     * @param mixed $debugData
     * @return $this
     * @since 2.0.0
     */
    public function addLogComment($debugData)
    {
        if (is_array($debugData)) {
            $this->_logTrace = array_merge($this->_logTrace, $debugData);
        } else {
            $this->_logTrace[] = $debugData;
        }

        if ($this->_debugMode) {
            $this->_logger->debug(var_export($debugData, true));
        }

        return $this;
    }

    /**
     * Return human readable debug trace.
     *
     * @return string
     * @since 2.0.0
     */
    public function getFormatedLogTrace()
    {
        $trace = '';
        $lineNumber = 1;
        foreach ($this->_logTrace as &$info) {
            $trace .= $lineNumber++ . ': ' . $info . "\n";
        }
        return $trace;
    }
}
