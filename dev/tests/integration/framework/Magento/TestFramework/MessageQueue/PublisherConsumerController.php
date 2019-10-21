<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\MessageQueue;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\OsInfo;
use Magento\TestFramework\Helper\Amqp;

/**
 * Publisher Consumer Controller
 */
class PublisherConsumerController
{
    /**
     * @var string[]
     */
    private $consumers = [];

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var string
     */
    private $logFilePath;

    /**
     * @var int|null
     */
    private $maxMessages = null;

    /**
     * @var OsInfo
     */
    private $osInfo;

    /**
     * @var array
     */
    private $appInitParams;

    /**
     * @var Amqp
     */
    private $amqpHelper;

    /**
     * PublisherConsumerController constructor.
     * @param PublisherInterface $publisher
     * @param OsInfo $osInfo
     * @param Amqp $amqpHelper
     * @param string $logFilePath
     * @param array $consumers
     * @param array $appInitParams
     * @param null|int $maxMessages
     */
    public function __construct(
        PublisherInterface $publisher,
        OsInfo $osInfo,
        Amqp $amqpHelper,
        $logFilePath,
        $consumers,
        $appInitParams,
        $maxMessages = null
    ) {
        $this->consumers = $consumers;
        $this->publisher = $publisher;
        $this->logFilePath = $logFilePath;
        $this->maxMessages = $maxMessages;
        $this->osInfo = $osInfo;
        $this->appInitParams = $appInitParams;
        $this->amqpHelper = $amqpHelper;
    }

    /**
     * Initialize Environment and Consumers
     *
     * @throws EnvironmentPreconditionException
     * @throws PreconditionFailedException
     */
    public function initialize()
    {
        $this->validateEnvironmentPreconditions();

        $connections = $this->amqpHelper->getConnections();
        foreach (array_keys($connections) as $connectionName) {
            $this->amqpHelper->deleteConnection($connectionName);
        }
        $this->amqpHelper->clearQueue("async.operations.all");

        $this->stopConsumers();
        $this->startConsumers();

        if (file_exists($this->logFilePath)) {
            // try to remove before failing the test
            unlink($this->logFilePath);
            if (file_exists($this->logFilePath)) {
                throw new PreconditionFailedException(
                    "Precondition failed: test log ({$this->logFilePath}) cannot be deleted before test execution."
                );
            }
        }
    }

    /**
     * Validate environment preconditions
     *
     * @throws EnvironmentPreconditionException
     * @throws PreconditionFailedException
     */
    private function validateEnvironmentPreconditions()
    {
        if ($this->osInfo->isWindows()) {
            throw new EnvironmentPreconditionException(
                "This test relies on *nix shell and should be skipped in Windows environment."
            );
        }

        if (!$this->amqpHelper->isAvailable()) {
            throw new PreconditionFailedException(
                'This test relies on RabbitMQ Management Plugin.'
            );
        }
    }

    /**
     * Stop Consumers
     */
    public function stopConsumers()
    {
        foreach ($this->consumers as $consumer) {
            foreach ($this->getConsumerProcessIds($consumer) as $consumerProcessId) {
                exec("kill {$consumerProcessId}");
            }
        }
    }

    /**
     * Get Consumers ProcessIds
     *
     * @return array
     */
    public function getConsumersProcessIds()
    {
        $consumers = [];
        foreach ($this->consumers as $consumer) {
            $consumers[$consumer] = $this->getConsumerProcessIds($consumer);
        }
        return $consumers;
    }

    /**
     * Get Consumer ProcessIds
     *
     * @param string $consumer
     * @return string[]
     */
    private function getConsumerProcessIds($consumer)
    {
        exec("ps ax | grep -v grep | grep '{$this->getConsumerStartCommand($consumer)}' | awk '{print $1}'", $output);
        return $output;
    }

    /**
     * Get CLI command for starting specified consumer.
     *
     * @param string $consumer
     * @param bool $withEnvVariables
     * @return string
     */
    private function getConsumerStartCommand($consumer, $withEnvVariables = false)
    {
        $binDirectory = realpath(INTEGRATION_TESTS_DIR . '/bin/');
        $magentoCli = $binDirectory . '/magento';
        $consumerStartCommand = "php {$magentoCli} queue:consumers:start -vvv " . $consumer;
        if ($this->maxMessages) {
            $consumerStartCommand .= " --max-messages={$this->maxMessages}";
        }
        if ($withEnvVariables) {
            $params = $this->appInitParams;
            $params['MAGE_DIRS']['base']['path'] = BP;
            $params = 'INTEGRATION_TEST_PARAMS="' . urldecode(http_build_query($params)) . '"';
            $consumerStartCommand = $params . ' ' . $consumerStartCommand;
        }
        return $consumerStartCommand;
    }

    /**
     * Wait for asynchronous result
     *
     * @param callable $condition
     * @param array $params
     * @throws PreconditionFailedException
     */
    public function waitForAsynchronousResult(callable $condition, $params)
    {
        $i = 0;
        do {
            sleep(1);
            $assertion = call_user_func_array($condition, $params);
        } while (!$assertion && ($i++ < 180));

        if (!$assertion) {
            throw new PreconditionFailedException("No asynchronous messages were processed.");
        }
    }

    /**
     * Get publisher
     *
     * @return PublisherInterface
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Start consumers
     *
     * @return void
     */
    public function startConsumers(): void
    {
        foreach ($this->consumers as $consumer) {
            if (!$this->getConsumerProcessIds($consumer)) {
                exec("{$this->getConsumerStartCommand($consumer, true)} > /dev/null &");
            }
            sleep(5);
        }
    }
}
