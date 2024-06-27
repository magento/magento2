<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * Gathers and stores metrics. Compares against another one to get the deltas.
 */
class Metrics
{
    /**
     * @param int $peakMemoryUsage
     * @param int $memoryUsage
     * @param array $rusage
     * @param float $microtime
     */
    public function __construct(
        private readonly int $peakMemoryUsage,
        private readonly int $memoryUsage,
        private readonly array $rusage,
        private readonly float $microtime
    ) {
    }

    /**
     * Gets peak memory usage
     *
     * @return int
     */
    public function getPeakMemoryUsage() : int
    {
        return $this->peakMemoryUsage;
    }

    /**
     * Gets memory usage
     *
     * @return int
     */
    public function getMemoryUsage() : int
    {
        return $this->memoryUsage;
    }

    /**
     * Gets fusage
     *
     * @return array
     */
    public function getRusage() : array
    {
        return $this->rusage;
    }

    /**
     * Gets microtime
     *
     * @return float
     */
    public function getMicrotime() : float
    {
        return $this->microtime;
    }
}
