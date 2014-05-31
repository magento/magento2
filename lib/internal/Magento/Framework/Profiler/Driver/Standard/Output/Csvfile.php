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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

use Magento\Framework\Profiler\Driver\Standard\AbstractOutput;
use Magento\Framework\Profiler\Driver\Standard\Stat;

class Csvfile extends AbstractOutput
{
    const DEFAULT_FILEPATH = '/var/log/profiler.csv';

    /**
     *
     * @var string
     */
    protected $_filePath;

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
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        parent::__construct($config);
        $this->_filePath = $this->_parseFilePath($config);
        $this->_delimiter = isset($config['delimiter']) ? $config['delimiter'] : ',';
        $this->_enclosure = isset($config['enclosure']) ? $config['enclosure'] : '"';
    }

    /**
     * Parses file path
     *
     * @param array|null $config
     * @return string
     */
    protected function _parseFilePath(array $config = null)
    {
        $result = isset($config['filePath']) ? $config['filePath'] : self::DEFAULT_FILEPATH;
        if (isset($config['baseDir'])) {
            $result = rtrim($config['baseDir'], '/') . '/' . ltrim($result, '/');
        }
        return $result;
    }

    /**
     * Write profiling results to CSV-file
     *
     * @param Stat $stat
     * @return void
     * @throws \RuntimeException if output file cannot be opened
     */
    public function display(Stat $stat)
    {
        $fileHandle = fopen($this->_filePath, 'w');
        if (!$fileHandle) {
            throw new \RuntimeException(sprintf('Can not open a file "%s".', $this->_filePath));
        }

        $lockRequired = strpos($this->_filePath, 'php://') !== 0;
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
     * @param Stat $stat
     * @return void
     */
    protected function _writeFileContent($fileHandle, Stat $stat)
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
