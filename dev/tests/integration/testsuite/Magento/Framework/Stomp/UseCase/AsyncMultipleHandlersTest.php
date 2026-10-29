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

class AsyncMultipleHandlersTest extends QueueTestCaseAbstract
{
    /**
     * @var string
     */
    protected $expectedMessages;

    /**
     * @var string[]
     */
    protected $consumers = [
        'stomp.mtmh.queue.1.consumer',
        'stomp.mtmh.queue.2.consumer',
    ];

    /**
     * @var string[]
     */
    private $topicValueMap = [
        'stomp.mtmh.topic.1' => 'stomp.mtmh.topic.1',
        'stomp.mtmh.topic.2' => ['stomp.mtmh.topic.2-1', 'stomp.mtmh.topic.2-2']
    ];

    /**
     * @var string[]
     */
    private $expectedValues = [
        'stomp-string-stomp.mtmh.topic.1',
        'stomp-array-stomp.mtmh.topic.2-1',
        'stomp-array-stomp.mtmh.topic.2-2'
    ];

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
     * Verify that Queue Framework supports multiple handlers with STOMP's one-to-one topic-queue architecture.
     *
     * Unlike AMQP's many-to-many model, STOMP uses dedicated queues for each topic.
     * This test verifies that each topic is processed by its own dedicated handler in STOMP.
     *
     * Current test is not test of Web API framework itself,
     * it just utilizes its infrastructure to test Message Queue.
     */
    public function testAsynchronousMultipleHandlers()
    {
        if ($this->connectionType === 'amqp') {
            $this->markTestSkipped('STOMP test skipped because AMQP connection is available.
            This test is STOMP-specific.');
        }

        foreach ($this->topicValueMap as $topic => $data) {
            $message = null;
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    /** @var AsyncTestData $testObject */
                    $testObject = $this->objectManager->create(AsyncTestData::class); // @phpstan-ignore-line
                    $testObject->setValue($value);
                    $testObject->setTextFilePath($this->logFilePath);
                    $message[$key] = $testObject;
                }
            } else {
                $testObject = $this->objectManager->create(AsyncTestData::class); // @phpstan-ignore-line
                $testObject->setValue($data);
                $testObject->setTextFilePath($this->logFilePath);
                $message = $testObject;
            }
            $this->publisher->publish($topic, $message);
        }

        $this->waitForAsynchronousResult(count($this->expectedValues), $this->logFilePath);

        //assertions
        foreach ($this->expectedValues as $item) {
            $this->assertStringContainsString($item, file_get_contents($this->logFilePath));
        }
    }
}
