<?php
/**
 * Storage for timers statistics
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard;

use Magento\Framework\Profiler;

class Stat
{
    /**
     * #@+ Timer statistics data keys
     */
    const ID = 'id';

    const START = 'start';

    const TIME = 'sum';

    const COUNT = 'count';

    const AVG = 'avg';

    const REALMEM = 'realmem';

    const REALMEM_START = 'realmem_start';

    const EMALLOC = 'emalloc';

    const EMALLOC_START = 'emalloc_start';

    /**#@-*/

    /**
     * Array of timers statistics data
     *
     * @var array
     */
    protected $_timers = [];

    /**
     * Starts timer
     *
     * @param string $timerId
     * @param int $time
     * @param int $realMemory Real size of memory allocated from system
     * @param int $emallocMemory Memory used by emalloc()
     * @return void
     */
    public function start($timerId, $time, $realMemory, $emallocMemory)
    {
        if (empty($this->_timers[$timerId])) {
            $this->_timers[$timerId] = [
                self::START => false,
                self::TIME => 0,
                self::COUNT => 0,
                self::REALMEM => 0,
                self::EMALLOC => 0,
            ];
        }

        $this->_timers[$timerId][self::REALMEM_START] = $realMemory;
        $this->_timers[$timerId][self::EMALLOC_START] = $emallocMemory;
        $this->_timers[$timerId][self::START] = $time;
        $this->_timers[$timerId][self::COUNT]++;
    }

    /**
     * Stops timer
     *
     * @param string $timerId
     * @param int $time
     * @param int $realMemory Real size of memory allocated from system
     * @param int $emallocMemory Memory used by emalloc()
     * @return void
     * @throws \InvalidArgumentException if timer doesn't exist
     */
    public function stop($timerId, $time, $realMemory, $emallocMemory)
    {
        if (empty($this->_timers[$timerId])) {
            throw new \InvalidArgumentException(sprintf('Timer "%s" doesn\'t exist.', $timerId));
        }

        $this->_timers[$timerId][self::TIME] += $time - $this->_timers[$timerId]['start'];
        $this->_timers[$timerId][self::START] = false;
        $this->_timers[$timerId][self::REALMEM] += $realMemory;
        $this->_timers[$timerId][self::REALMEM] -= $this->_timers[$timerId][self::REALMEM_START];
        $this->_timers[$timerId][self::EMALLOC] += $emallocMemory;
        $this->_timers[$timerId][self::EMALLOC] -= $this->_timers[$timerId][self::EMALLOC_START];
    }

    /**
     * Get timer statistics data by timer id
     *
     * @param string $timerId
     * @return array
     * @throws \InvalidArgumentException if timer doesn't exist
     */
    public function get($timerId)
    {
        if (empty($this->_timers[$timerId])) {
            throw new \InvalidArgumentException(sprintf('Timer "%s" doesn\'t exist.', $timerId));
        }
        return $this->_timers[$timerId];
    }

    /**
     * Retrieve statistics on specified timer
     *
     * @param string $timerId
     * @param string $key Information to return
     * @return string|bool|int|float
     * @throws \InvalidArgumentException
     */
    public function fetch($timerId, $key)
    {
        if ($key === self::ID) {
            return $timerId;
        }
        if (empty($this->_timers[$timerId])) {
            throw new \InvalidArgumentException(sprintf('Timer "%s" doesn\'t exist.', $timerId));
        }
        /* AVG = TIME / COUNT */
        $isAvg = $key == self::AVG;
        if ($isAvg) {
            $key = self::TIME;
        }
        if (!isset($this->_timers[$timerId][$key])) {
            throw new \InvalidArgumentException(sprintf('Timer "%s" doesn\'t have value for "%s".', $timerId, $key));
        }
        $result = $this->_timers[$timerId][$key];
        if ($key == self::TIME && $this->_timers[$timerId][self::START] !== false) {
            $result += microtime(true) - $this->_timers[$timerId][self::START];
        }
        if ($isAvg) {
            $count = $this->_timers[$timerId][self::COUNT];
            if ($count) {
                $result = $result / $count;
            }
        }
        return $result;
    }

    /**
     * Clear collected statistics for specified timer or for all timers if timer id is omitted
     *
     * @param string|null $timerId
     * @return void
     */
    public function clear($timerId = null)
    {
        if ($timerId) {
            unset($this->_timers[$timerId]);
        } else {
            $this->_timers = [];
        }
    }

    /**
     * Get ordered list of timer ids filtered by thresholds and pcre pattern
     *
     * @param array|null $thresholds
     * @param string|null $filterPattern
     * @return array
     */
    public function getFilteredTimerIds(array $thresholds = null, $filterPattern = null)
    {
        $timerIds = $this->_getOrderedTimerIds();
        if (!$thresholds && !$filterPattern) {
            return $timerIds;
        }
        $thresholds = (array)$thresholds;
        $result = [];
        foreach ($timerIds as $timerId) {
            /* Filter by pattern */
            if ($filterPattern && !preg_match($filterPattern, $timerId)) {
                continue;
            }
            /* Filter by thresholds */
            $match = true;
            foreach ($thresholds as $fetchKey => $minMatchValue) {
                $match = $this->fetch($timerId, $fetchKey) >= $minMatchValue;
                if ($match) {
                    break;
                }
            }
            if ($match) {
                $result[] = $timerId;
            }
        }
        return $result;
    }

    /**
     * Get ordered list of timer ids
     *
     * @return array
     */
    protected function _getOrderedTimerIds()
    {
        $timerIds = array_keys($this->_timers);
        if (count($timerIds) <= 2) {
            /* No sorting needed */
            return $timerIds;
        }

        /* Prepare PCRE once to use it inside the loop body */
        $nestingSep = preg_quote(Profiler::NESTING_SEPARATOR, '/');
        $patternLastTimerId = '/' . $nestingSep . '(?:.(?!' . $nestingSep . '))+$/';

        $prevTimerId = $timerIds[0];
        $result = [$prevTimerId];
        for ($i = 1; $i < count($timerIds); $i++) {
            $timerId = $timerIds[$i];
            /* Skip already added timer */
            if (!$timerId) {
                continue;
            }
            /* Loop over all timers that need to be closed under previous timer */
            while (strpos($timerId, $prevTimerId . Profiler::NESTING_SEPARATOR) !== 0) {
                /* Add to result all timers nested in the previous timer */
                for ($j = $i + 1; $j < count($timerIds); $j++) {
                    if (strpos($timerIds[$j], $prevTimerId . Profiler::NESTING_SEPARATOR) === 0) {
                        $result[] = $timerIds[$j];
                        /* Mark timer as already added */
                        $timerIds[$j] = null;
                    }
                }
                /* Go to upper level timer */
                $count = 0;
                $prevTimerId = preg_replace($patternLastTimerId, '', $prevTimerId, -1, $count);
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
}
