<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class WildcardTopicTest extends QueueTestCaseAbstract
{
    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var string[]
     */
    protected $consumers = [
        'wildcard.queue.one.consumer',
        'wildcard.queue.two.consumer',
        'wildcard.queue.three.consumer',
        'wildcard.queue.four.consumer',
    ];

    protected function setUp()
    {
        $this->tmpPath = TESTS_TEMP_DIR . "/testWildcardTopicTest.txt";
        parent::setUp();
    }

    protected function tearDown()
    {
        if (file_exists($this->tmpPath)) {
            unlink($this->tmpPath);
        };
        parent::tearDown();
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
        $testObject = $this->generateTestObject();
        $this->publisher->publish($topic, $testObject);

        $this->waitForAsynchronousResult(count($matchingQueues), $this->tmpPath);

        $this->assertTrue(file_exists($this->tmpPath), "No handlers invoked (log file was not created).");
        foreach ($nonMatchingQueues as $queueName) {
            $this->assertNotContains($queueName, file_get_contents($this->tmpPath));
        }
        foreach ($matchingQueues as $queueName) {
            $this->assertContains($queueName, file_get_contents($this->tmpPath));
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
        $this->assertFalse(file_exists($this->tmpPath), "No log file must be created for non-matching topic.");
    }

    /**
     * @return AsyncTestData
     */
    private function generateTestObject()
    {
        $testObject = $this->objectManager->create(AsyncTestData::class);
        $testObject->setValue('||Message Contents||');
        $testObject->setTextFilePath($this->tmpPath);
        return $testObject;
    }
}
