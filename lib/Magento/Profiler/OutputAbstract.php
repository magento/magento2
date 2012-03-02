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
 * Abstract class that represents profiler output
 */
abstract class Magento_Profiler_OutputAbstract
{
    /**
     * PCRE Regular Expression for filter
     *
     * @var null|string
     */
    private $_filter;

    /**
     * List of threshold (minimal allowed) values for profiler data
     *
     * @var array
     */
    private $_thresholds = array(
        Magento_Profiler::FETCH_TIME    => 0.001,
        Magento_Profiler::FETCH_COUNT   => 10,
        Magento_Profiler::FETCH_EMALLOC => 10000,
    );

    /**
     * Initialize profiler output with timer identifiers filter
     *
     * @param string|null $filter PCRE pattern to filter timers by their identifiers
     */
    public function __construct($filter = null)
    {
        $this->_filter = $filter;
    }

    /**
     * Override in descendants to display profiling results in appropriate format
     */
    abstract public function display();

    /**
     * Retrieve the list of (column_label; column_id) pairs
     *
     * @return array
     */
    protected function _getColumns()
    {
        return array(
            'Timer Id' => 'timer_id',
            'Time'     => Magento_Profiler::FETCH_TIME,
            'Avg'      => Magento_Profiler::FETCH_AVG,
            'Cnt'      => Magento_Profiler::FETCH_COUNT,
            'Emalloc'  => Magento_Profiler::FETCH_EMALLOC,
            'RealMem'  => Magento_Profiler::FETCH_REALMEM,
        );
    }

    /**
     * Render statistics column value for specified timer
     *
     * @param string $timerId
     * @param string $columnId
     */
    protected function _renderColumnValue($timerId, $columnId)
    {
        if ($columnId == 'timer_id') {
            return $this->_renderTimerId($timerId);
        }
        $value = (string)Magento_Profiler::fetch($timerId, $columnId);
        if (in_array($columnId, array(Magento_Profiler::FETCH_TIME, Magento_Profiler::FETCH_AVG))) {
            $value = number_format($value, 6);
        } else {
            $value = number_format($value);
        }
        return $value;
    }

    /**
     * Render timer id column value
     *
     * @param string $timerId
     * @return string
     */
    protected function _renderTimerId($timerId)
    {
        return $timerId;
    }

    /**
     * Retrieve timer ids sorted to correspond the nesting
     *
     * @return array
     */
    private function _getSortedTimers()
    {
        $timerIds = Magento_Profiler::getTimers();
        if (count($timerIds) <= 2) {
            /* No sorting needed */
            return $timerIds;
        }

        /* Prepare PCRE once to use it inside the loop body */
        $nestingSep = preg_quote(Magento_Profiler::NESTING_SEPARATOR, '/');
        $patternLastTimerName = '/' . $nestingSep . '(?:.(?!' . $nestingSep . '))+$/';

        $prevTimerId = $timerIds[0];
        $result = array($prevTimerId);
        for ($i = 1; $i < count($timerIds); $i++) {
            $timerId = $timerIds[$i];
            /* Skip already added timer */
            if (!$timerId) {
                continue;
            }
            /* Loop over all timers that need to be closed under previous timer */
            while (strpos($timerId, $prevTimerId . Magento_Profiler::NESTING_SEPARATOR) !== 0) {
                /* Add to result all timers nested in the previous timer */
                for ($j = $i + 1; $j < count($timerIds); $j++) {
                    if (strpos($timerIds[$j], $prevTimerId . Magento_Profiler::NESTING_SEPARATOR) === 0) {
                        $result[] = $timerIds[$j];
                        /* Mark timer as already added */
                        $timerIds[$j] = null;
                    }
                }
                /* Go to upper level timer */
                $count = 0;
                $prevTimerId = preg_replace($patternLastTimerName, '', $prevTimerId, -1, $count);
                /* Break the loop if no replacements was done. It is possible when we are */
                /* working with top level (root) item */
                if (!$count) {
                    break;
                }
            }
            /* Add current timer to the result */
            $result[] = $timerId;
            $prevTimerId = $timerId;
        }
        return $result;
    }

    /**
     * Retrieve the list of timer Ids
     *
     * @return array
     */
    protected function _getTimers()
    {
        $pattern = $this->_filter;
        $timerIds = $this->_getSortedTimers();
        $result = array();
        foreach ($timerIds as $timerId) {
            /* Filter by timer id pattern */
            if ($pattern && !preg_match($pattern, $timerId)) {
                continue;
            }
            /* Filter by column value thresholds */
            $skip = false;
            foreach ($this->_thresholds as $fetchKey => $minAllowedValue) {
                $skip = (Magento_Profiler::fetch($timerId, $fetchKey) < $minAllowedValue);
                /* First value not less than the allowed one forces to include timer to the result */
                if (!$skip) {
                    break;
                }
            }
            if (!$skip) {
                $result[] = $timerId;
            }
        }
        return $result;
    }

    /**
     * Render a caption for the profiling results
     *
     * @return string
     */
    protected function _renderCaption()
    {
        $result = 'Code Profiler (Memory usage: real - %s, emalloc - %s)';
        $result = sprintf($result, memory_get_usage(true), memory_get_usage());
        return $result;
    }

    /**
     * Set threshold (minimal allowed) value for timer column.
     * Timer is being rendered if at least one of its columns is not less than the minimal allowed value.
     *
     * @param string $fetchKey
     * @param int|float|null $minAllowedValue
     */
    public function setThreshold($fetchKey, $minAllowedValue)
    {
        if ($minAllowedValue === null) {
            unset($this->_thresholds[$fetchKey]);
        } else {
            $this->_thresholds[$fetchKey] = $minAllowedValue;
        }
    }
}
