<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\UseCase\DeprecatedFormat;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\UseCase\QueueTestCaseAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class AsyncMultiTopicsSeparateQueuesTest extends QueueTestCaseAbstract
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
    protected $consumers = [
        'queue.for.multiple.topics.test.c.deprecated',
        'queue.for.multiple.topics.test.d.deprecated'
    ];

    /**
     * @var string[]
     */
    private $topics = ['multi.topic.queue.topic.c.deprecated', 'multi.topic.queue.topic.d.deprecated'];

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

        if ($this->connectionType === 'amqp') {
            parent::setUp();
        }
    }

    /**
     * Verify that Queue Framework processes multiple asynchronous topics sent to the same queue.
     *
     * Current test is not test of Web API framework itself, it just utilizes its infrastructure to test Message Queue.
     */
    public function testAsyncMultipleTopicsPerQueue()
    {
        if ($this->connectionType === 'stomp') {
            $this->markTestSkipped('AMQP test skipped because STOMP connection is available.
            This test is AMQP-specific.');
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
