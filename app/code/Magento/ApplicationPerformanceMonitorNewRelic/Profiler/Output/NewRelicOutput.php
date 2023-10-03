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

/**
 * Outputs the performance metrics and other information to New Relic
 */
class NewRelicOutput implements OutputInterface
{
    public const CONFIG_ENABLE_KEY = 'application/performance_monitor/newrelic_output_enable';
    public const CONFIG_VERBOSE_KEY = 'application/performance_monitor/newrelic_output_verbose';

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(private DeploymentConfig $deploymentConfig)
    {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        if (!extension_loaded('newrelic')) {
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
    public function doOutput(array $metrics, array $information)
    {
        if (!$this->isEnabled()) {
            return;
        }
        foreach ($information as $key => $value) {
            newrelic_add_custom_parameter($key, $value);
        }
        $verbose = $this->isVerbose();
        /** @var Metric $metric */
        foreach ($metrics as $metric) {
            if (!$verbose && $metric->isVerbose()) {
                continue;
            }
            newrelic_add_custom_parameter($metric->getName(), $metric->getValue());
        }
    }

    private function isVerbose(): bool
    {
        return match ($this->deploymentConfig->get(static::CONFIG_VERBOSE_KEY)) {
            1, "1", "true", true => true,
            default => false,
        };
    }
}
