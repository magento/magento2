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
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Operation abstract class
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Model;

abstract class AbstractModel extends \Magento\Object
{
    /**
     * Enable loging
     *
     * @var boolean
     */
    protected $_debugMode = false;

    /**
     * Loger instance
     * @var \Magento\Core\Model\Log\Adapter
     */
    protected $_logInstance;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    /**
     * Contains all log information
     *
     * @var array
     */
    protected $_logTrace = array();

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @var \Magento\Core\Model\Log\AdapterFactory
     */
    protected $_adapterFactory;

    /**
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\App\Dir $dir
     * @param \Magento\Core\Model\Log\AdapterFactory $adapterFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\App\Dir $dir,
        \Magento\Core\Model\Log\AdapterFactory $adapterFactory,
        array $data = array()
    ) {
        $this->_logger = $logger;
        $this->_dir = $dir;
        $this->_adapterFactory = $adapterFactory;
        parent::__construct($data);
    }

    /**
     * Log debug data to file.
     * Log file dir: var/log/import_export/%Y/%m/%d/%time%_%operation_type%_%entity_type%.log
     *
     * @param mixed $debugData
     * @return \Magento\ImportExport\Model\AbstractModel
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
            $dirName  = date('Y' . DS .'m' . DS .'d' . DS);
            $fileName = join('_', array(
                str_replace(':', '-', $this->getRunAt()),
                $this->getScheduledOperationId(),
                $this->getOperationType(),
                $this->getEntity()
            ));
            $dirPath = $this->_dir->getDir('var') . DS . \Magento\ImportExport\Model\Scheduled\Operation::LOG_DIRECTORY
                . $dirName;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0777, true);
            }
            $fileName = substr(strstr(\Magento\ImportExport\Model\Scheduled\Operation::LOG_DIRECTORY, DS), 1)
                . $dirName . $fileName . '.log';
            $this->_logInstance = $this->_adapterFactory->create(array('fileName' => $fileName))
                ->setFilterDataKeys($this->_debugReplacePrivateDataKeys);
        }
        $this->_logInstance->log($debugData);
        return $this;
    }

    /**
     * Return human readable debug trace.
     *
     * @return array
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
