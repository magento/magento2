<?php
/**
 * Class that represents profiler output in CSV-file format
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Profiler_Driver_Standard_Output_Csvfile extends Magento_Profiler_Driver_Standard_OutputAbstract
{
    /**
     *
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
     * Constructor
     *
     * @param string $filename Target file to save CSV data
     * @param string $delimiter Delimiter for CSV format
     * @param string $enclosure Enclosure for CSV format
     */
    public function __construct($filename, $delimiter = ',', $enclosure = '"')
    {
        $this->_filename = $filename;
        $this->_delimiter = $delimiter;
        $this->_enclosure = $enclosure;
    }

    /**
     * Write profiling results to CSV-file
     *
     * @param Magento_Profiler_Driver_Standard_Stat $stat
     * @throws RuntimeException if output file cannot be opened
     */
    public function display(Magento_Profiler_Driver_Standard_Stat $stat)
    {
        $fileHandle = fopen($this->_filename, 'w');
        if (!$fileHandle) {
            throw new RuntimeException(sprintf('Can not open a file "%s".', $this->_filename));
        }

        $lockRequired = (strpos($this->_filename, 'php://') !== 0);
        $isLocked = false;
        while ($lockRequired && !$isLocked) {
            $isLocked = flock($fileHandle, LOCK_EX);
        }
        $this->_writeFileContent($fileHandle, $stat);
        if ($isLocked) {
            flock($fileHandle, LOCK_UN);
        }
        fclose($fileHandle);
    }

    /**
     * Write content into an opened file handle
     *
     * @param resource $fileHandle
     * @param Magento_Profiler_Driver_Standard_Stat $stat
     */
    protected function _writeFileContent($fileHandle, Magento_Profiler_Driver_Standard_Stat $stat)
    {
        foreach ($this->_getTimerIds($stat) as $timerName) {
            $row = array();
            foreach ($this->_columns as $column) {
                $row[] = $this->_renderColumnValue($stat->fetch($timerName, $column), $column);
            }
            fputcsv($fileHandle, $row, $this->_delimiter, $this->_enclosure);
        }
    }
}
