<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ImportExport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Operation abstract class
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractModel extends \Magento\Framework\Object
{
    /**
     * Enable loging
     *
     * @var bool
     */
    protected $_debugMode = false;

    /**
     * Logger instance
     * @var \Magento\Framework\Logger\Adapter
     */
    protected $_logInstance;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = [];

    /**
     * Contains all log information
     *
     * @var string[]
     */
    protected $_logTrace = [];

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_varDirectory;

    /**
     * @var \Magento\Framework\Logger\AdapterFactory
     */
    protected $_adapterFactory;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Logger\AdapterFactory $adapterFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Logger\AdapterFactory $adapterFactory,
        array $data = []
    ) {
        $this->_logger = $logger;
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_adapterFactory = $adapterFactory;
        parent::__construct($data);
    }

    /**
     * Log debug data to file.
     * Log file dir: var/log/import_export/%Y/%m/%d/%time%_%operation_type%_%entity_type%.log
     *
     * @param mixed $debugData
     * @return $this
     */
    public function addLogComment($debugData)
    {
        if (is_array($debugData)) {
            $this->_logTrace = array_merge($this->_logTrace, $debugData);
        } else {
            $this->_logTrace[] = $debugData;
        }
        if (!$this->_debugMode) {
            return $this;
        }

        if (!$this->_logInstance) {
            $dirName = date('Y/m/d/');
            $fileName = join(
                '_',
                [
                    str_replace(':', '-', $this->getRunAt()),
                    $this->getScheduledOperationId(),
                    $this->getOperationType(),
                    $this->getEntity()
                ]
            );
            $path = 'import_export/' . $dirName;
            $this->_varDirectory->create($path);

            $fileName = $path . $fileName . '.log';
            $this->_logInstance = $this->_adapterFactory->create(
                ['fileName' => $this->_varDirectory->getAbsolutePath($fileName)]
            )->setFilterDataKeys(
                $this->_debugReplacePrivateDataKeys
            );
        }
        $this->_logInstance->log($debugData);
        return $this;
    }

    /**
     * Return human readable debug trace.
     *
     * @return string
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
