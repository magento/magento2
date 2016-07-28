<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

class MixSyncAndAsyncSingleQueueTest extends QueueTestCaseAbstract
{
    /**
     * @var String
     */
    protected $tmpPath;

    /**
     * @var \Magento\TestModuleAsyncAmqp\Model\AsyncTestData
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

    protected function tearDown()
    {
        unlink($this->tmpPath);
        parent::tearDown();
    }

    public function testMixSyncAndAsyncSingleQueue()
    {
        $this->tmpPath = TESTS_TEMP_DIR . "/testMixSyncAndAsyncSingleQueue.txt";
        $this->msgObject = $this->objectManager->create('Magento\TestModuleAsyncAmqp\Model\AsyncTestData');

        // Publish asynchronous messages
        foreach ($this->messages as $item) {
            $this->msgObject->setValue($item);
            $this->msgObject->setTextFilePath($this->tmpPath);
            $this->publisher->publish('multi.topic.queue.topic.c', $this->msgObject);
        }

        // Publish synchronous message to the same queue
        $input = 'Input value';
        $response = $this->publisher->publish('sync.topic.for.mixed.sync.and.async.queue', $input);
        $this->assertEquals($input . ' processed by RPC handler', $response);

        $this->waitForAsynchronousResult(count($this->messages), $this->tmpPath);

        // Verify that asynchronous messages were processed
        foreach ($this->messages as $item) {
            $this->assertContains($item, file_get_contents($this->tmpPath));
        }
    }
}
