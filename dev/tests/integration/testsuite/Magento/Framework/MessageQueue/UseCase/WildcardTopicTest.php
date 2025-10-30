<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class WildcardTopicTest extends QueueTestCaseAbstract
{
    /**
     * @var string[]
     */
    protected $consumers = [
        'wildcard.queue.one.consumer',
        'wildcard.queue.two.consumer',
        'wildcard.queue.three.consumer',
        'wildcard.queue.four.consumer',
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

        if ($this->connectionType === 'amqp') {
            parent::setUp();
        }
    }

    /**
     * @param string $topic
     * @param string[] $matchingQueues
     * @param string[] $nonMatchingQueues
     *
     * @dataProvider wildCardTopicsDataProvider
     */
    public function testWildCardMatchingTopic($topic, $matchingQueues, $nonMatchingQueues)
    {
        if ($this->connectionType === 'stomp') {
            $this->markTestSkipped('AMQP test skipped because STOMP connection is available.
            This test is AMQP-specific.');
        }

        $testObject = $this->generateTestObject();
        $this->publisher->publish($topic, $testObject);

        $this->waitForAsynchronousResult(count($matchingQueues), $this->logFilePath);

        $this->assertFileExists($this->logFilePath, "No handlers invoked (log file was not created).");
        foreach ($nonMatchingQueues as $queueName) {
            $this->assertStringNotContainsString($queueName, file_get_contents($this->logFilePath));
        }
        foreach ($matchingQueues as $queueName) {
            $this->assertStringContainsString($queueName, file_get_contents($this->logFilePath));
        }
    }

    public static function wildCardTopicsDataProvider()
    {
        return [
            'segment1.segment2.segment3.wildcard' => [
                'segment1.segment2.segment3.wildcard',
                ['wildcard.queue.one', 'wildcard.queue.two', 'wildcard.queue.four'],
                ['wildcard.queue.three']
            ],
            'segment2.segment3.wildcard' => [
                'segment2.segment3.wildcard',
                ['wildcard.queue.one', 'wildcard.queue.three', 'wildcard.queue.four'],
                ['wildcard.queue.two']
            ]
        ];
    }

    public function testWildCardNonMatchingTopic()
    {
        if ($this->connectionType === 'stomp') {
            $this->markTestSkipped('AMQP test skipped because STOMP connection is available.
            This test is AMQP-specific.');
        }

        $testObject = $this->generateTestObject();
        $this->publisher->publish('not.matching.wildcard.topic', $testObject);
        sleep(2);
        $this->assertFileDoesNotExist($this->logFilePath, "No log file must be created for non-matching topic.");
    }

    /**
     * @return AsyncTestData
     */
    private function generateTestObject()
    {
        $testObject = $this->objectManager->create(AsyncTestData::class); // @phpstan-ignore-line
        $testObject->setValue('||Message Contents||');
        $testObject->setTextFilePath($this->logFilePath);
        return $testObject;
    }
}
