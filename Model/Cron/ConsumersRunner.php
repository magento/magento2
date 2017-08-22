<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron;

use Magento\Framework\ShellInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Symfony\Component\Process\phpExecutableFinder;
use Magento\MessageQueue\Model\Cron\ConsumersRunner\Pid;

/**
 * Class for running consumers processes by cron
 */
class ConsumersRunner
{
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
     * The executable finder specifically designed for the PHP executable
     *
     * @var phpExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * The class for checking status of process by PID
     *
     * @var Pid
     */
    private $pid;

    /**
     * @param phpExecutableFinder $phpExecutableFinder The executable finder specifically designed for the PHP executable
     * @param ConsumerConfigInterface $consumerConfig The consumer config provider
     * @param DeploymentConfig $deploymentConfig The application deployment configuration
     * @param ShellInterface $shellBackground The shell command line wrapper for executing command in background
     * @param Pid $pid The class for checking status of process by PID
     */
    public function __construct(
        phpExecutableFinder $phpExecutableFinder,
        ConsumerConfigInterface $consumerConfig,
        DeploymentConfig $deploymentConfig,
        ShellInterface $shellBackground,
        Pid $pid
    ) {
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->consumerConfig = $consumerConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->shellBackground = $shellBackground;
        $this->pid = $pid;
    }

    /**
     * Runs consumers processes
     */
    public function run()
    {
        $runByCron = $this->deploymentConfig->get('queue_consumer/cron_run', true);
        $maxMessages = (int) $this->deploymentConfig->get('queue_consumer/max_messages', 10000);
        $php = $this->phpExecutableFinder->find() ?: 'php';

        if (!$runByCron) {
            return;
        }

        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $consumerName = $consumer->getName();

            if ($this->pid->isRun($consumerName)) {
                continue;
            }

            $arguments = [
                $consumerName,
                '--pid-file-path=' . $this->pid->getPidFilePath($consumerName),
            ];

            if ($maxMessages) {
                $arguments[] = '--max-messages=' . $maxMessages;
            }

            $command = $php . ' ' . BP . '/bin/magento queue:consumers:start %s %s'
                . ($maxMessages ? ' %s' : '');

            $this->shellBackground->execute($command, $arguments);
        }
    }
}
