<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\QueueTestCaseAbstract;
use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

class AsyncMultipleTopicsPerQueueTest extends QueueTestCaseAbstract
{
    /**
     * @var String
     */
    protected $tmpPath;

    /**
     * @var String[]
     */
    protected $uniqueID;

    /**
     * @var \Magento\TestModuleAsyncAmqp\Model\AsyncTestData
     */
    protected $msgObject;

    /**
     * @var string[]
     */
    protected $consumers = ['queue.for.multiple.topics.test.c', 'queue.for.multiple.topics.test.d'];

    /**
     * @var string[]
     */
    private $topics = ['multi.topic.queue.topic.c', 'multi.topic.queue.topic.d'];

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
     * Verify that Queue Framework processes multiple asynchronous topics sent to the same queue.
     *
     * Current test is not test of Web API framework itself, it just utilizes its infrastructure to test Message Queue.
     */
    public function testAsyncMultipleTopicsPerQueue()
    {
        $this->tmpPath = TESTS_TEMP_DIR . "/testAsyncMultipleTopicsPerQueue.txt";
        $this->msgObject = $this->objectManager->create('Magento\TestModuleAsyncAmqp\Model\AsyncTestData');

        foreach ($this->topics as $topic) {
            $this->uniqueID[$topic] = md5(uniqid($topic));
            $this->msgObject->setValue($this->uniqueID[$topic] . "_" . $topic);
            $this->msgObject->setTextFilePath($this->tmpPath);
            $this->publisher->publish($topic, $this->msgObject);
        }

        // Give some time for processing of asynchronous messages
        sleep(20);

        //assertions
        foreach ($this->topics as $item)
        {
            $this->assertContains($this->uniqueID[$item] . "_" . $item, file_get_contents($this->tmpPath));
        }
    }
}
