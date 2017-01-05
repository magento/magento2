<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

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
     * @param string $topic
     * @param string[] $matchingQueues
     * @param string[] $nonMatchingQueues
     *
     * @dataProvider wildCardTopicsDataProvider
     */
    public function testWildCardMatchingTopic($topic, $matchingQueues, $nonMatchingQueues)
    {
        $testObject = $this->generateTestObject();
        $this->publisher->publish($topic, $testObject);

        $this->waitForAsynchronousResult(count($matchingQueues), $this->logFilePath);

        $this->assertTrue(file_exists($this->logFilePath), "No handlers invoked (log file was not created).");
        foreach ($nonMatchingQueues as $queueName) {
            $this->assertNotContains($queueName, file_get_contents($this->logFilePath));
        }
        foreach ($matchingQueues as $queueName) {
            $this->assertContains($queueName, file_get_contents($this->logFilePath));
        }
    }

    public function wildCardTopicsDataProvider()
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
        $testObject = $this->generateTestObject();
        $this->publisher->publish('not.matching.wildcard.topic', $testObject);
        sleep(2);
        $this->assertFalse(file_exists($this->logFilePath), "No log file must be created for non-matching topic.");
    }

    /**
     * @return AsyncTestData
     */
    private function generateTestObject()
    {
        $testObject = $this->objectManager->create(AsyncTestData::class);
        $testObject->setValue('||Message Contents||');
        $testObject->setTextFilePath($this->logFilePath);
        return $testObject;
    }
}
