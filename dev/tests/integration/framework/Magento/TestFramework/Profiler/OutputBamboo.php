<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_metrics = isset($config['metrics']) ? (array)$config['metrics'] : [];
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
        array_map('preg_quote', $timerNames, ['/']);
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
        $result = ['Timestamp' => time()];
        foreach ($this->_metrics as $metricName => $timerNames) {
            $result[$metricName] = $this->_aggregateTimerValues($stat, $timerNames);
        }
        fputcsv($fileHandle, array_keys($result), $this->_delimiter, $this->_enclosure);
        fputcsv($fileHandle, array_values($result), $this->_delimiter, $this->_enclosure);
    }
}
