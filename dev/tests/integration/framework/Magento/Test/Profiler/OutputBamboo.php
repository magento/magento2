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
 * @package     Magento_Test
 * @subpackage  integration_test
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class that used for output Magento Profiler results in format compatible with Bamboo Jmeter plugin
 */
class Magento_Test_Profiler_OutputBamboo extends Magento_Profiler_Output_Csvfile
{
    /**
     * @var array
     */
    protected $_metrics;

    /**
     * Constructor
     *
     * @param string $filename  Filename of the target file to write results to
     * @param array  $metrics   Metrics to be included into result.
     *                          Supported format: array(
     *                              'metric name 1' => array(
     *                                  'profiler key 1', ...
     *                              ), ...
     *                          );
     * @param string $delimiter
     * @param string $enclosure
     */
    public function __construct($filename, array $metrics, $delimiter = ',', $enclosure = '"')
    {
        parent::__construct($filename, null, $delimiter, $enclosure);
        $this->_metrics = $metrics;
    }

    /**
     * Calculate metric value from set of timer names
     *
     * @param array $timerNames
     * @param string $fetchKey
     * @return int
     */
    protected function _aggregateTimerValues(array $timerNames, $fetchKey = Magento_Profiler::FETCH_AVG)
    {
        /* Prepare pattern that matches timers with deepest nesting level only */
        $nestingSep = preg_quote(Magento_Profiler::NESTING_SEPARATOR, '/');
        array_map('preg_quote', $timerNames, array('/'));
        $pattern = '/(?<=' . $nestingSep . '|^)(?:' . implode('|', $timerNames) . ')$/';

        /* Sum profiler values for matched timers */
        $result = 0;
        foreach ($this->_getTimers() as $timerId) {
            if (preg_match($pattern, $timerId)) {
                $result += Magento_Profiler::fetch($timerId, $fetchKey);
            }
        }

        /* Convert seconds -> milliseconds */
        $result = round($result * 1000);

        return $result;
    }

    /**
     * Write content into an opened file handle
     *
     * @param resource $fileHandle
     */
    protected function _writeFileContent($fileHandle)
    {
        /* First column must be a timestamp */
        $result = array('Timestamp' => time());
        foreach ($this->_metrics as $metricName => $timerNames) {
            $result[$metricName] = $this->_aggregateTimerValues($timerNames);
        }
        fputcsv($fileHandle, array_keys($result), $this->_delimiter, $this->_enclosure);
        fputcsv($fileHandle, array_values($result), $this->_delimiter, $this->_enclosure);
    }
}
