<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler;

/**
 * Interface for different ways of outputting our performance data.
 */
interface OutputInterface
{

    /**
     * Whether this output is currently enabled
     *
     * @return bool
     */
    public function isEnabled() : bool;

    /**
     * Output our metrics and other information
     *
     * @param Metric[] $metrics
     * @param array $information
     * @return void
     */
    public function doOutput(array $metrics, array $information) : void;
}
