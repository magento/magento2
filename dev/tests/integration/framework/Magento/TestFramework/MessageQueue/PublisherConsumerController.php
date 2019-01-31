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
        if ($this->osInfo->isWindows()) {
            throw new EnvironmentPreconditionException(
                "This test relies on *nix shell and should be skipped in Windows environment."
            );
        }
        $connections = $this->amqpHelper->getConnections();
        foreach (array_keys($connections) as $connectionName) {
            $this->amqpHelper->deleteConnection($connectionName);
        }
        $this->amqpHelper->clearQueue("async.operations.all");
        foreach ($this->consumers as $consumer) {
            foreach ($this->getConsumerProcessIds($consumer) as $consumerProcessId) {
                exec("kill {$consumerProcessId}");
            }
        }
        foreach ($this->consumers as $consumer) {
            if (!$this->getConsumerProcessIds($consumer)) {
                exec("{$this->getConsumerStartCommand($consumer, true)} > /dev/null &");
            }
            sleep(5);
        }

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
     * @param callable $condition
     * @param $params
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
     * @return PublisherInterface
     */
    public function getPublisher()
    {
        return $this->publisher;
    }
}
