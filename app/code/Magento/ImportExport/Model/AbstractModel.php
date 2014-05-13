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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ImportExport\Model;

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
    protected $_debugReplacePrivateDataKeys = array();

    /**
     * Contains all log information
     *
     * @var string[]
     */
    protected $_logTrace = array();

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
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\Logger\AdapterFactory $adapterFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Logger $logger,
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Logger\AdapterFactory $adapterFactory,
        array $data = array()
    ) {
        $this->_logger = $logger;
        $this->_varDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::VAR_DIR);
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
                array(
                    str_replace(':', '-', $this->getRunAt()),
                    $this->getScheduledOperationId(),
                    $this->getOperationType(),
                    $this->getEntity()
                )
            );
            $path = 'import_export/' . $dirName;
            $this->_varDirectory->create($path);

            $fileName = $path . $fileName . '.log';
            $this->_logInstance = $this->_adapterFactory->create(
                array('fileName' => $this->_varDirectory->getAbsolutePath($fileName))
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
