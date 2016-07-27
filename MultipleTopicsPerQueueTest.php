<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\QueueTestCaseAbstract;

class MultipleTopicsPerQueueTest extends QueueTestCaseAbstract
{
    /**
     * {@inheritdoc}
     */
    protected $consumers = ['queue.for.multiple.topics.test'];

    /**
     * Verify that Queue Framework supports multiple topics per queue.
     *
     * Current test is not test of Web API framework itself,
     * it just utilizes its infrastructure to test Message Queue.
     */
    public function testSynchronousRpcCommunication()
    {
        foreach (['multi.topic.queue.topic.a', 'multi.topic.queue.topic.b'] as $topic) {
            $input = "Input value for topic '{$topic}'";
            $response = $this->publisher->publish($topic, $input);
            $this->assertEquals($input . ' processed by RPC handler', $response);
        }
    }
}
