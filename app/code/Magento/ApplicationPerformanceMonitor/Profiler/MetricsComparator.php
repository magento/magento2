<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * Compares metrics against another one to get the deltas.
 */
class MetricsComparator
{
    /**
     * @param MetricFactory $metricFactory
     */
    public function __construct(private readonly MetricFactory $metricFactory)
    {
    }

    /**
     * Compares with a previous Metrics and returns results as array.
     *
     * @param Metrics $beforeMetrics
     * @param Metrics $afterMetrics
     * @param Metrics|null $previousAfterMetrics
     * @return Metric[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function compareMetrics(Metrics $beforeMetrics, Metrics $afterMetrics, ?Metrics $previousAfterMetrics)
    {
        $metrics = [];
        $metrics['memoryUsageBefore'] = $this->metricFactory->create([
            'type' => MetricType::MEMORY_SIZE_INT,
            'name' => 'memoryUsageBefore',
            'value' => $beforeMetrics->getMemoryUsage(),
            'verbose' => true,
        ]);
        $metrics['memoryUsageAfter'] = $this->metricFactory->create([
            'type' => MetricType::MEMORY_SIZE_INT,
            'name' => 'memoryUsageAfter',
            'value' => $afterMetrics->getMemoryUsage(),
            'verbose' => false,
        ]);
        if ($previousAfterMetrics) {
            $metrics['memoryUsageAfterComparedToPrevious'] = $this->metricFactory->create([
                'type' => MetricType::MEMORY_SIZE_INT,
                'name' => 'memoryUsageAfterComparedToPrevious',
                'value' => $afterMetrics->getMemoryUsage() - $previousAfterMetrics->getMemoryUsage(),
                'verbose' => false,
            ]);
        }
        $metrics['memoryUsageDelta'] = $this->metricFactory->create([
            'type' => MetricType::MEMORY_SIZE_INT,
            'name' => 'memoryUsageDelta',
            'value' => $afterMetrics->getMemoryUsage() - $beforeMetrics->getMemoryUsage(),
            'verbose' => false,
        ]);
        $metrics['peakMemoryUsageBefore'] = $this->metricFactory->create([
            'type' => MetricType::MEMORY_SIZE_INT,
            'name' => 'peakMemoryUsageBefore',
            'value' => $beforeMetrics->getPeakMemoryUsage(),
            'verbose' => true,
        ]);
        $metrics['peakMemoryUsageAfter'] = $this->metricFactory->create([
            'type' => MetricType::MEMORY_SIZE_INT,
            'name' => 'peakMemoryUsageAfter',
            'value' => $afterMetrics->getPeakMemoryUsage(),
            'verbose' => false,
        ]);
        $metrics['peakMemoryUsageDelta'] = $this->metricFactory->create([
            'type' => MetricType::MEMORY_SIZE_INT,
            'name' => 'peakMemoryUsageDelta',
            'value' => $afterMetrics->getPeakMemoryUsage() - $beforeMetrics->getPeakMemoryUsage(),
            'verbose' => false,
        ]);
        $metrics['wallTimeBefore'] = $this->metricFactory->create([
            'type' => MetricType::UNIX_TIMESTAMP_FLOAT,
            'name' => 'wallTimeBefore',
            'value' => $beforeMetrics->getMicrotime(),
            'verbose' => true,
        ]);
        $metrics['wallTimeAfter'] = $this->metricFactory->create([
            'type' => MetricType::UNIX_TIMESTAMP_FLOAT,
            'name' => 'wallTimeAfter',
            'value' => $afterMetrics->getMicrotime(),
            'verbose' => true,
        ]);
        $metrics['wallTimeElapsed'] = $this->metricFactory->create([
            'type' => MetricType::SECONDS_ELAPSED_FLOAT,
            'name' => 'wallTimeElapsed',
            'value' => $afterMetrics->getMicrotime() - $beforeMetrics->getMicrotime(),
            'verbose' => false,
        ]);
        $metrics['userTimeBefore'] = $this->metricFactory->create([
            'type' => MetricType::SECONDS_ELAPSED_FLOAT,
            'name' => 'userTimeBefore',
            'value' => $beforeMetrics->getRusage()['ru_utime.tv_sec']
                + 0.000001 * $beforeMetrics->getRusage()['ru_utime.tv_usec'],
            'verbose' => true,
        ]);
        $metrics['userTimeAfter'] = $this->metricFactory->create([
            'type' => MetricType::SECONDS_ELAPSED_FLOAT,
            'name' => 'userTimeAfter',
            'value' => $afterMetrics->getRusage()['ru_utime.tv_sec']
                + 0.000001 * $afterMetrics->getRusage()['ru_utime.tv_usec'],
            'verbose' => true,
        ]);
        $metrics['userTimeElapsed'] = $this->metricFactory->create([
            'type' => MetricType::SECONDS_ELAPSED_FLOAT,
            'name' => 'userTimeElapsed',
            'value' => $metrics['userTimeAfter']->getValue() - $metrics['userTimeBefore']->getValue(),
            'verbose' => true,
        ]);
        $metrics['systemTimeBefore'] = $this->metricFactory->create([
            'type' => MetricType::SECONDS_ELAPSED_FLOAT,
            'name' => 'systemTimeBefore',
            'value' => $beforeMetrics->getRusage()['ru_stime.tv_sec']
                + 0.000001 * $beforeMetrics->getRusage()['ru_stime.tv_usec'],
            'verbose' => true,
        ]);
        $metrics['systemTimeAfter'] = $this->metricFactory->create([
            'type' => MetricType::SECONDS_ELAPSED_FLOAT,
            'name' => 'systemTimeAfter',
            'value' => $afterMetrics->getRusage()['ru_stime.tv_sec']
                + 0.000001 * $afterMetrics->getRusage()['ru_stime.tv_usec'],
            'verbose' => true,
        ]);
        $metrics['systemTimeElapsed'] = $this->metricFactory->create([
            'type' => MetricType::SECONDS_ELAPSED_FLOAT,
            'name' => 'systemTimeElapsed',
            'value' => $metrics['systemTimeAfter']->getValue() - $metrics['systemTimeBefore']->getValue(),
            'verbose' => true,
        ]);
        return $metrics;
    }
}
