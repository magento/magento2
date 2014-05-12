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

/**
 * Class that used for output Magento Profiler results in format compatible with Bamboo Jmeter plugin
 */
namespace Magento\TestFramework\Profiler;

class OutputBamboo extends \Magento\Framework\Profiler\Driver\Standard\Output\Csvfile
{
    /**
     * @var array
     */
    protected $_metrics;

    /**
     * Constructor
     *
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        parent::__construct($config);
        $this->_metrics = isset($config['metrics']) ? (array)$config['metrics'] : array();
    }

    /**
     * Calculate metric value from set of timer names
     *
     * @param \Magento\Framework\Profiler\Driver\Standard\Stat $stat
     * @param array $timerNames
     * @param string $fetchKey
     * @return int
     */
    protected function _aggregateTimerValues(
        \Magento\Framework\Profiler\Driver\Standard\Stat $stat,
        array $timerNames,
        $fetchKey = \Magento\Framework\Profiler\Driver\Standard\Stat::AVG
    ) {
        /* Prepare pattern that matches timers with deepest nesting level only */
        $nestingSep = preg_quote(\Magento\Framework\Profiler::NESTING_SEPARATOR, '/');
        array_map('preg_quote', $timerNames, array('/'));
        $pattern = '/(?<=' . $nestingSep . '|^)(?:' . implode('|', $timerNames) . ')$/';

        /* Sum profiler values for matched timers */
        $result = 0;
        foreach ($this->_getTimerIds($stat) as $timerId) {
            if (preg_match($pattern, $timerId)) {
                $result += $stat->fetch($timerId, $fetchKey);
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
     * @param \Magento\Framework\Profiler\Driver\Standard\Stat $stat
     */
    protected function _writeFileContent($fileHandle, \Magento\Framework\Profiler\Driver\Standard\Stat $stat)
    {
        /* First column must be a timestamp */
        $result = array('Timestamp' => time());
        foreach ($this->_metrics as $metricName => $timerNames) {
            $result[$metricName] = $this->_aggregateTimerValues($stat, $timerNames);
        }
        fputcsv($fileHandle, array_keys($result), $this->_delimiter, $this->_enclosure);
        fputcsv($fileHandle, array_values($result), $this->_delimiter, $this->_enclosure);
    }
}
