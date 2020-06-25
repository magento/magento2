<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\UseCase;

use Magento\TestModuleAsyncAmqp\Model\AsyncTestData;

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
    protected $consumers = ['queue.for.multiple.topics.test.y', 'queue.for.multiple.topics.test.z'];

    /**
     * @var string[]
     */
    private $topics = ['multi.topic.queue.topic.y', 'multi.topic.queue.topic.z'];

    /**
     * Verify that Queue Framework processes multiple asynchronous topics sent to the same queue.
     *
     * Current test is not test of Web API framework itself, it just utilizes its infrastructure to test Message Queue.
     */
    public function testAsyncMultipleTopicsPerQueue()
    {
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
