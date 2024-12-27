<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitorNewRelic\Profiler\Output;

use Magento\ApplicationPerformanceMonitor\Profiler\Metric;
use Magento\ApplicationPerformanceMonitor\Profiler\OutputInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\NewRelicReporting\Model\NewRelicWrapper;

/**
 * Outputs the performance metrics and other information to New Relic
 */
class NewRelicOutput implements OutputInterface
{
    public const CONFIG_ENABLE_KEY = 'application/performance_monitor/newrelic_output_enable';
    public const CONFIG_VERBOSE_KEY = 'application/performance_monitor/newrelic_output_verbose';

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param NewRelicWrapper $newRelicWrapper
     */
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
        private readonly NewRelicWrapper $newRelicWrapper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        if (!$this->newRelicWrapper->isExtensionInstalled()) {
            return false;
        }
        return match ($this->deploymentConfig->get(static::CONFIG_ENABLE_KEY)) {
            0, "0", "false", false => false,
            default => true,
        };
    }

    /**
     * @inheritDoc
     */
    public function doOutput(array $metrics, array $information) : void
    {
        if (!$this->isEnabled()) {
            return;
        }
        foreach ($information as $key => $value) {
            $this->newRelicWrapper->addCustomParameter($key, $value);
        }
        $verbose = $this->isVerbose();
        foreach ($metrics as $metric) {
            if (!$verbose && $metric->isVerbose()) {
                continue;
            }
            $this->newRelicWrapper->addCustomParameter($metric->getName(), $metric->getValue());
        }
    }

    /**
     * Is configured to output verbose
     *
     * @return bool
     */
    private function isVerbose(): bool
    {
        return match ($this->deploymentConfig->get(static::CONFIG_VERBOSE_KEY)) {
            1, "1", "true", true => true,
            default => false,
        };
    }
}
