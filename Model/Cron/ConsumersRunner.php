<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron;

use Magento\Framework\ShellInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Class for running consumers processes by cron
 */
class ConsumersRunner
{
    /**
     * Shell command line wrapper
     *
     * @var ShellInterface
     */
    private $shell;

    /**
     * Shell command line wrapper for executing command in background
     *
     * @var ShellInterface
     */
    private $shellBackground;

    /**
     * Consumer config provider
     *
     * @var ConsumerConfigInterface
     */
    private $consumerConfig;

    /**
     * Application deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ConsumerConfigInterface $consumerConfig The consumer config provider
     * @param DeploymentConfig $deploymentConfig The application deployment configuration
     * @param ShellInterface $shell The shell command line wrapper
     * @param ShellInterface $shellBackground The shell command line wrapper for executing command in background
     */
    public function __construct(
        ConsumerConfigInterface $consumerConfig,
        DeploymentConfig $deploymentConfig,
        ShellInterface $shell,
        ShellInterface $shellBackground
    ) {
        $this->consumerConfig = $consumerConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->shell = $shell;
        $this->shellBackground = $shellBackground;
    }

    /**
     * Runs consumers processes
     */
    public function run()
    {
        $runByCron = $this->deploymentConfig->get('queue_consumer/cron_run', true);
        $maxMessages = (int) $this->deploymentConfig->get('queue_consumer/max_messages', 10000);

        $isRun = $this->shell->execute("ps aux | grep '[q]ueue:consumers:start' | awk '{print $2}'")
            ? true : false;

        if (!$runByCron || $isRun) {
            return;
        }

        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $command = 'php '. BP . '/bin/magento queue:consumers:start ' . $consumer->getName()
                . ' --max-messages=' . $maxMessages;
            $this->shellBackground->execute($command);
        }
    }
}
