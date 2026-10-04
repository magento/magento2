<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\UseCase;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\UseCase\QueueTestCaseAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleAsyncStomp\Model\AsyncTestData;

class AsyncMultipleTopicsWithEachQueueTest extends QueueTestCaseAbstract
{
    /**
     * @var string[]
     */
    protected $uniqueID;

    /**
     * @var AsyncTestData
     */
    protected $msgObject;

    /**
     * @var string[]
     */
    protected $consumers = ['stomp.queue.for.multiple.topics.test.y', 'stomp.queue.for.multiple.topics.test.z'];

    /**
     * @var string[]
     */
    private $topics = ['stomp.multi.topic.queue.topic.y', 'stomp.multi.topic.queue.topic.z'];

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var DefaultValueProvider $defaultValueProvider */
        $defaultValueProvider = $this->objectManager->get(DefaultValueProvider::class);
        $this->connectionType = $defaultValueProvider->getConnection();

        if ($this->connectionType === 'stomp') {
            parent::setUp();
        }
    }

    /**
     * Verify that Queue Framework processes multiple asynchronous topics sent to the same queue.
     *
     * Current test is not test of Web API framework itself, it just utilizes its infrastructure to test Message Queue.
     */
    public function testAsyncMultipleTopicsPerQueue(): void
    {
        if ($this->connectionType === 'amqp') {
            $this->markTestSkipped('STOMP test skipped because AMQP connection is available.
            This test is STOMP-specific.');
        }

        $this->msgObject = $this->objectManager->create(AsyncTestData::class); // @phpstan-ignore-line

        foreach ($this->topics as $topic) {
            // phpcs:ignore Magento2.Security.InsecureFunction
            $this->uniqueID[$topic] = md5(uniqid($topic));
            $this->msgObject->setValue($this->uniqueID[$topic] . "_" . $topic);
            $this->msgObject->setTextFilePath($this->logFilePath);
            $this->publisher->publish($topic, $this->msgObject);
        }

        $this->waitForAsynchronousResult(count($this->uniqueID), $this->logFilePath);

        //assertions
        foreach ($this->topics as $item) {
            $this->assertStringContainsString(
                $this->uniqueID[$item] . "_" . $item,
                file_get_contents($this->logFilePath)
            );
        }
    }
}
