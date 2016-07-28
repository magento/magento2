<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class AsyncMultipleHandlersTest extends QueueTestCaseAbstract
{
    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var string
     */
    protected $expectedMessages;

    /**
     * @var string[]
     */
    protected $consumers = [
        'mtmh.queue.1.consumer',
        'mtmh.queue.2.consumer',
    ];

    /**
     * @var string[]
     */
    private $topicValueMap = [
        'mtmh.topic.1' => 'mtmh.topic.1',
        'mtmh.topic.2' => ['mtmh.topic.2-1', 'mtmh.topic.2-2']
    ];

    /**
     * @var string[]
     */
    private $expectedValues = [
        'string-mtmh.topic.1',
        'mixed-mtmh.topic.1',
        'array-mtmh.topic.2-1',
        'array-mtmh.topic.2-2',
        'mixed-mtmh.topic.2-1',
        'mixed-mtmh.topic.2-2'
    ];

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink($this->tmpPath);
    }

    /**
     * Verify that Queue Framework supports multiple topics per queue.
     *
     * Current test is not test of Web API framework itself,
     * it just utilizes its infrastructure to test Message Queue.
     */
    public function testAsynchronousRpcCommunication()
    {
        $this->tmpPath = TESTS_TEMP_DIR . "/testAsynchronousRpcCommunication.txt";

        foreach ($this->topicValueMap as $topic => $data) {
            $message = null;
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    /** @var AsyncTestData $testObject */
                    $testObject = $this->objectManager->create(AsyncTestData::class);
                    $testObject->setValue($value);
                    $testObject->setTextFilePath($this->tmpPath);
                    $message[$key] = $testObject;
                }
            } else {
                $testObject = $this->objectManager->create(AsyncTestData::class);
                $testObject->setValue($data);
                $testObject->setTextFilePath($this->tmpPath);
                $message = $testObject;
            }
            $this->publisher->publish($topic, $message);
        }

        $this->waitForAsynchronousResult(count($this->expectedValues), $this->tmpPath);

        //assertions
        foreach ($this->expectedValues as $item) {
            $this->assertContains($item, file_get_contents($this->tmpPath));
        }
    }
}
