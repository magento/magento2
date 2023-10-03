<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

use Magento\Framework\AppInterface as Application;

/**
 * Profiles a callable and then outputs it the configured OutputInterface(s).
 */
class Profiler
{
    /** @var OutputInterface[] */
    private array $outputs;

    /** @var InputInterface[] */
    private array $inputs;

    private ?Metrics $previousAfterMetrics = null;
    private int $previousRequestCount = 0;

    /**
     * @param OutputInterface[] $outputs
     * @param InputInterface[] $inputs
     * @param MetricsComparator $metricsComparator
     * @param MetricsGatherer $metricsGatherer
     */
    public function __construct(
        array $outputs,
        array $inputs,
        private MetricsComparator $metricsComparator,
        private MetricsGatherer $metricsGatherer
    ) {
        $this->outputs = $outputs;
        $this->inputs = $inputs;
    }

    /**
     * Does the actual profiling of the function being profiled and then sends results to the outputs.
     *
     * @param callable $functionBeingProfiled
     * @param Application $application
     * @return void
     */
    public function doProfile(callable $functionBeingProfiled, Application $application) : void
    {
        $previousAfterMetrics = $this->previousAfterMetrics;
        $previousRequestCount = $this->previousRequestCount;
        $this->previousRequestCount++;
        $this->previousAfterMetrics = null;
        if (!$this->isEnabled()) {
            $functionBeingProfiled();
            return;
        }
        $beforeMetrics = $this->metricsGatherer->gatherMetrics();
        $functionBeingProfiled();
        $afterMetrics = $this->metricsGatherer->gatherMetrics();
        $this->previousAfterMetrics = $afterMetrics;
        $information = [];
        foreach ($this->inputs as $input) {
            $information[] = $input->doInput($application);
        }
        $information = array_merge(...$information);
        $information['threadPreviousRequestCount'] = $previousRequestCount;
        $this->doOutput($beforeMetrics, $afterMetrics, $previousAfterMetrics, $information);
    }

    /**
     * Outputs the results of profiling to all enabled outputs.
     *
     * @param Metrics $beforeMetrics
     * @param Metrics $afterMetrics
     * @param Metrics|null $previousAfterMetrics,
     * @param array $information extra information that we send to output
     * @return void
     */
    private function doOutput(
        Metrics $beforeMetrics,
        Metrics $afterMetrics,
        ?Metrics $previousAfterMetrics,
        array $information
    ) : void {
        if (!$this->isEnabled()) {
            return;
        }
        $metrics = $this->metricsComparator->compareMetrics($beforeMetrics, $afterMetrics, $previousAfterMetrics);
        foreach ($this->outputs as $output) {
            $output->doOutput($metrics, $information);
        }
    }

    /**
     * Returns true if any of our outputs are enabled.
     *
     * @return bool
     */
    public function isEnabled() : bool
    {
        foreach ($this->outputs as $output) {
            if ($output->isEnabled()) {
                return true;
            }
        }
        return false;
    }
}
