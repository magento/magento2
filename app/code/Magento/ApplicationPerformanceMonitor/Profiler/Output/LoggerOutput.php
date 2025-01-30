<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ApplicationPerformanceMonitor\Profiler\Output;

use Magento\ApplicationPerformanceMonitor\Profiler\Metric;
use Magento\ApplicationPerformanceMonitor\Profiler\MetricType;
use Magento\ApplicationPerformanceMonitor\Profiler\OutputInterface;
use Magento\Framework\App\DeploymentConfig;
use Psr\Log\LoggerInterface;

/**
 * Outputs the performance metrics and other information to Logger
 */
class LoggerOutput implements OutputInterface
{
    public const CONFIG_ENABLE_KEY = 'application/performance_monitor/logger_output_enable';
    public const CONFIG_VERBOSE_KEY = 'application/performance_monitor/logger_output_verbose';

    /**
     * @param LoggerInterface $logger
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DeploymentConfig $deploymentConfig,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return match ($this->deploymentConfig->get(static::CONFIG_ENABLE_KEY)) {
            1, "1", "true", true => true,
            default => false,
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
        if (!empty($information['subject'])) {
            $subject = __('Profile information for %1', $information['subject']);
            unset($information['subject']);
        } else {
            $subject = __('Profile information');
        }
        if (!empty($information['requestContentLength'])) {
            $information['requestContentLength'] = $this->prettyMemorySize($information['requestContentLength']);
        }
        $verbose = $this->isVerbose();
        $prettyMetrics = $this->doOutputMetrics($metrics, $verbose);
        $message = sprintf("\"%s\": {\n", $subject);
        foreach ($information as $key => $value) {
            $message .= sprintf("\t\"%s\":\t\"%s\",\n", (string)$key, (string)$value);
        }
        foreach ($prettyMetrics as $key => $value) {
            $message .= sprintf("\t\"%s\":\t\"%s\",\n", (string)$key, (string)$value);
        }
        $message = \rtrim($message, ",\n");
        $message .= sprintf("\n}\n");
        $this->logger->debug($message);
    }

    /**
     * Make the metrics pretty and checks verbosity
     *
     * @param array $metrics
     * @param bool $verbose
     * @return array
     */
    private function doOutputMetrics(array $metrics, bool $verbose)
    {
        $prettyMetrics = [];
        /** @var Metric $metric */
        foreach ($metrics as $metric) {
            if (!$verbose && $metric->isVerbose()) {
                continue;
            }
            switch ($metric->getType()) {
                case MetricType::SECONDS_ELAPSED_FLOAT:
                    $prettyMetrics[$metric->getName()] = $this->prettyElapsedTime($metric->getValue());
                    break;
                case MetricType::UNIX_TIMESTAMP_FLOAT:
                    $prettyMetrics[$metric->getName()] = $this->prettyUnixTime($metric->getValue());
                    break;
                case MetricType::MEMORY_SIZE_INT:
                    $prettyMetrics[$metric->getName()] = $this->prettyMemorySize($metric->getValue());
                    break;
                default:
                    $prettyMetrics[$metric->getName()] = $metric->getValue();
                    break;
            }
        }
        return $prettyMetrics;
    }

    /**
     * Returns a string format of memory with units.
     *
     * @param int $size
     * @return string
     */
    private function prettyMemorySize(int $size): string
    {
        if (!$this->isVerbose()) {
            $absSize = abs($size);
            if ($absSize > 1000000000) {
                return sprintf("%.3g GB", $size / 1000000000.0);
            }
            if ($absSize > 1000000) {
                return sprintf("%.3g MB", $size / 1000000.0);
            }
            if ($absSize > 1000) {
                return sprintf("%.3g KB", $size / 1000.0);
            }
        }
        return ((string)($size)) . ' B';
    }

    /**
     * Returns a string format of elapsed time with units.
     *
     * @param string $time
     * @return string
     */
    private function prettyElapsedTime(float $time): string
    {
        if ($this->isVerbose()) {
            return ((string)($time)) . ' s';
        }
        $time = (int) $time;
        if ($time > 60) {
            return sprintf("%.3g m", $time / 60.0);
        }
        return ((string)($time)) . ' s';
    }

    /**
     * Returns a string format of unix time with units.
     *
     * @param string $time
     * @return string
     */
    private function prettyUnixTime(float $time): string
    {
        $timeAsString = sprintf("%.1f", $time);
        return \DateTime::createFromFormat('U.u', $timeAsString)->format('Y-m-d\TH:i:s.u');
    }

    /**
     * Returns true when verbose is enabled in configuration.
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
