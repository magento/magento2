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
 * @package     Magento_Profiler
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class that represents profiler output in Html format
 */
class Magento_Profiler_Output_Csvfile extends Magento_Profiler_OutputAbstract
{
    /**
     * @var string
     */
    protected $_filename;

    /**
     * @var string
     */
    protected $_delimiter;

    /**
     * @var string
     */
    protected $_enclosure;

    /**
     * Start output buffering
     *
     * @param string      $filename Target file to save CSV data
     * @param string|null $filter Pattern to filter timers by their identifiers (SQL LIKE syntax)
     * @param string      $delimiter Delimiter for CSV format
     * @param string      $enclosure Enclosure for CSV format
     */
    public function __construct($filename, $filter = null, $delimiter = ',', $enclosure = '"')
    {
        parent::__construct($filter);

        $this->_filename = $filename;
        $this->_delimiter = $delimiter;
        $this->_enclosure = $enclosure;
    }

    /**
     * Display profiling results
     */
    public function display()
    {
        $fileHandle = fopen($this->_filename, 'w');
        if (!$fileHandle) {
            throw new Varien_Exception(sprintf('Can not open a file "%s".', $this->_filename));
        }

        $needLock = (strpos($this->_filename, 'php://') !== 0);
        $isLocked = false;
        while ($needLock && !$isLocked) {
            $isLocked = flock($fileHandle, LOCK_EX);
        }

        $this->_writeFileContent($fileHandle);

        if ($isLocked) {
            flock($fileHandle, LOCK_UN);
        }
        fclose($fileHandle);
    }

    /**
     * Write content into an opened file handle
     *
     * @param resource $fileHandle
     */
    protected function _writeFileContent($fileHandle)
    {
        foreach ($this->_getTimers() as $timerId) {
            $row = array();
            foreach ($this->_getColumns() as $columnId) {
                $row[] = $this->_renderColumnValue($timerId, $columnId);
            }
            fputcsv($fileHandle, $row, $this->_delimiter, $this->_enclosure);
        }
    }
}
