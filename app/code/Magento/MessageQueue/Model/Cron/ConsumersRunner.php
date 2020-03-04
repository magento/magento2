<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItemInterface;
use Magento\Framework\ShellInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Magento\Framework\Lock\LockManagerInterface;

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
     * @var PhpExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * @var ConnectionTypeResolver
     */
    private $mqConnectionTypeResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Lock Manager
     *
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @param PhpExecutableFinder $phpExecutableFinder The executable finder specifically designed
     *        for the PHP executable
     * @param ConsumerConfigInterface $consumerConfig The consumer config provider
     * @param DeploymentConfig $deploymentConfig The application deployment configuration
     * @param ShellInterface $shellBackground The shell command line wrapper for executing command in background
     * @param LockManagerInterface $lockManager The lock manager
     * @param ConnectionTypeResolver $mqConnectionTypeResolver Consumer connection resolver
     * @param LoggerInterface $logger Logger
     */
    public function __construct(
        PhpExecutableFinder $phpExecutableFinder,
        ConsumerConfigInterface $consumerConfig,
        DeploymentConfig $deploymentConfig,
        ShellInterface $shellBackground,
        LockManagerInterface $lockManager,
        ConnectionTypeResolver $mqConnectionTypeResolver = null,
        LoggerInterface $logger = null
    ) {
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->consumerConfig = $consumerConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->shellBackground = $shellBackground;
        $this->lockManager = $lockManager;
        $this->mqConnectionTypeResolver = $mqConnectionTypeResolver
            ?: ObjectManager::getInstance()->get(ConnectionTypeResolver::class);
        $this->logger = $logger
            ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Runs consumers processes
     */
    public function run()
    {
        $runByCron = $this->deploymentConfig->get('cron_consumers_runner/cron_run', true);

        if (!$runByCron) {
            return;
        }

        $maxMessages = (int) $this->deploymentConfig->get('cron_consumers_runner/max_messages', 10000);
        $allowedConsumers = $this->deploymentConfig->get('cron_consumers_runner/consumers', []);
        $php = $this->phpExecutableFinder->find() ?: 'php';

        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            if (!$this->canBeRun($consumer, $allowedConsumers)) {
                continue;
            }

            $arguments = [
                $consumer->getName(),
                '--single-thread'
            ];

            if ($maxMessages) {
                $arguments[] = '--max-messages=' . $maxMessages;
            }

            $command = $php . ' ' . BP . '/bin/magento queue:consumers:start %s %s'
                . ($maxMessages ? ' %s' : '');

            $this->shellBackground->execute($command, $arguments);
        }
    }

    /**
     * Checks that the consumer can be run
     *
     * @param ConsumerConfigItemInterface $consumerConfig The consumer config
     * @param array $allowedConsumers The list of allowed consumers
     *        If $allowedConsumers is empty it means that all consumers are allowed
     * @return bool Returns true if the consumer can be run
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function canBeRun(ConsumerConfigItemInterface $consumerConfig, array $allowedConsumers = []): bool
    {
        $consumerName = $consumerConfig->getName();
        if (!empty($allowedConsumers) && !in_array($consumerName, $allowedConsumers)) {
            return false;
        }

        if ($this->lockManager->isLocked(md5($consumerName))) { //phpcs:ignore
            return false;
        }

        $connectionName = $consumerConfig->getConnection();
        try {
            $this->mqConnectionTypeResolver->getConnectionType($connectionName);
        } catch (\LogicException $e) {
            $this->logger->info(
                sprintf(
                    'Consumer "%s" skipped as required connection "%s" is not configured. %s',
                    $consumerName,
                    $connectionName,
                    $e->getMessage()
                )
            );
            return false;
        }

        return true;
    }
}
