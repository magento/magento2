<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * Gathers and stores metrics.  Compares against another one to get the deltas.
 */
class Metrics
{
    public function __construct(
        private int $peakMemoryUsage,
        private int $memoryUsage,
        private array $rusage,
        private float $microtime
    ) {
    }

    public function getPeakMemoryUsage() : int
    {
        return $this->peakMemoryUsage;
    }

    public function getMemoryUsage() : int
    {
        return $this->memoryUsage;
    }

    public function getRusage() : array
    {
        return $this->rusage;
    }

    public function getMicrotime() : float
    {
        return $this->microtime;
    }
}
