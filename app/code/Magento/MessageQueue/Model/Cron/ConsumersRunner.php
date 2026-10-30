<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\MessageQueue\Model\Cron;

use Magento\Framework\App\DeploymentConfig;
use Magento\MessageQueue\Model\ConsumersRunnerExecutor;

/**
 * Class for running consumers processes by cron
 */
class ConsumersRunner
{
    /**
     * Application deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ConsumersRunnerExecutor
     */
    private $consumersRunnerExecutor;

    /**
     * @param DeploymentConfig $deploymentConfig The application deployment configuration
     * @param ConsumersRunnerExecutor $consumersRunnerExecutor
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ConsumersRunnerExecutor $consumersRunnerExecutor
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->consumersRunnerExecutor = $consumersRunnerExecutor;
    }

    /**
     * Runs consumers processes
     */
    public function run(): void
    {
        if (!$this->deploymentConfig->get('cron_consumers_runner/cron_run', true)) {
            return;
        }

        $multipleProcesses = $this->deploymentConfig->get('cron_consumers_runner/multiple_processes', []);
        $maxMessages = (int) $this->deploymentConfig->get('cron_consumers_runner/max_messages', 10000);
        $allowedConsumers = $this->deploymentConfig->get('cron_consumers_runner/consumers', []);

        $this->consumersRunnerExecutor->run($multipleProcesses, $maxMessages, $allowedConsumers);
    }
}
