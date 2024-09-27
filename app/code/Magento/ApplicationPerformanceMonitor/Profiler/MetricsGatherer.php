<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * Gathers metrics.
 */
class MetricsGatherer
{
    /**
     * @param MetricsFactory $metricsFactory
     */
    public function __construct(private readonly MetricsFactory $metricsFactory)
    {
    }

    /**
     * Updates the state of this object to the current performance metrics that we measure.
     *
     * @return Metrics
     */
    public function gatherMetrics()
    {
        return $this->metricsFactory->create([
            'memoryUsage' => \memory_get_usage(),
            'peakMemoryUsage' => \memory_get_peak_usage(),
            'rusage' => \getrusage(),
            'microtime' => \microtime(true),
        ]);
    }
}
