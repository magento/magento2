<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem;
use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class WaitAndNotWaitMessagesTest extends QueueTestCaseAbstract
{
    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $config;

    /**
     * @var AsyncTestData
     */
    protected $msgObject;

    /**
     * {@inheritdoc}
     */
    protected $consumers = ['mixed.sync.and.async.queue.consumer'];

    /**
     * @var string[]
     */
    protected $messages = ['message1', 'message2', 'message3'];

    /**
     * @var int|null
     */
    protected $maxMessages = 4;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        // phpstan:ignore "Class Magento\TestModuleAsyncAmqp\Model\AsyncTestData not found."
        $this->msgObject = $this->objectManager->create(AsyncTestData::class);
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->config = $this->loadConfig();
    }

    /**
     * Check if consumers wait for messages from the queue
     */
    public function testWaitForMessages()
    {
        $this->assertArrayHasKey('queue', $this->config);
        $this->assertArrayHasKey('consumers_wait_for_messages', $this->config['queue']);
        $this->assertEquals(1, $this->config['queue']['consumers_wait_for_messages']);

        foreach ($this->messages as $message) {
            $this->publishMessage($message);
        }

        $this->waitForAsynchronousResult(count($this->messages), $this->logFilePath);

        foreach ($this->messages as $item) {
            $this->assertStringContainsString($item, file_get_contents($this->logFilePath));
        }

        $this->publishMessage('message4');
        $this->waitForAsynchronousResult(count($this->messages) + 1, $this->logFilePath);
        $this->assertStringContainsString('message4', file_get_contents($this->logFilePath));
    }

    /**
     * Check if consumers do not wait for messages from the queue and die
     */
    public function testNotWaitForMessages(): void
    {
        $this->publisherConsumerController->stopConsumers();

        $config = $this->config;
        $config['queue']['consumers_wait_for_messages'] = 0;
        $this->writeConfig($config);

        $loadedConfig = $this->loadConfig();
        $this->assertArrayHasKey('queue', $loadedConfig);
        $this->assertArrayHasKey('consumers_wait_for_messages', $loadedConfig['queue']);
        $this->assertEquals(0, $loadedConfig['queue']['consumers_wait_for_messages']);
        foreach ($this->messages as $message) {
            $this->publishMessage($message);
        }

        $this->publisherConsumerController->startConsumers();
        $this->waitForAsynchronousResult(count($this->messages), $this->logFilePath);

        foreach ($this->messages as $item) {
            $this->assertStringContainsString($item, file_get_contents($this->logFilePath));
        }

        // Checks that consumers do not wait 4th message and die
        $consumersProcessIds = $this->publisherConsumerController->getConsumersProcessIds();
        $this->assertArrayHasKey('mixed.sync.and.async.queue.consumer', $consumersProcessIds);
        $this->assertEquals([], $consumersProcessIds['mixed.sync.and.async.queue.consumer']);
    }

    /**
     * @param string $message
     */
    private function publishMessage(string $message): void
    {
        $this->msgObject->setValue($message);
        $this->msgObject->setTextFilePath($this->logFilePath);
        $this->publisher->publish('multi.topic.queue.topic.c', $this->msgObject);
    }

    /**
     * @return array
     */
    private function loadConfig(): array
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * @param array $config
     */
    private function writeConfig(array $config): void
    {
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_ENV => $config], true);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->writeConfig($this->config);
    }
}
