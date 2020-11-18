<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RedisMq\Test\Integration;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Base test case for message queue tests.
 *
 */
class QueueTestCaseAbstract extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
    }


//    /**
//     * @magentoCache config disabled
//     */
//    public function testA()
//    {
//        /** @var PublisherInterface $publisher */
//        $publisher = $this->objectManager->get(PublisherInterface::class);
//        $publisher->publish('redis.test', $this->objectManager->create(\Magento\RedisMq\Model\T\DataObject::class));
//    }
    public function testReject()
    {
        $queue1 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);
        /** @var \Magento\RedisMq\Model\Driver\Queue $queue2 */
        $queue2 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);

        /** @var \Magento\Framework\MessageQueue\EnvelopeFactory $envelopFactory */
        $envelopFactory = $this->objectManager->get(\Magento\Framework\MessageQueue\EnvelopeFactory::class);
        $envelop = $envelopFactory->create(['body' => '', 'properties' => []]);

        $queue1->push($envelop);

        $messageFromQueue = $queue2->dequeue();
        $queue2->reject($messageFromQueue);
        sleep(4);
        $messageFromQueue = $queue1->dequeue();

        $this->assertNotNull($messageFromQueue);
        $queue2->acknowledge($messageFromQueue);
        $this->assertNull($queue2->dequeue());
    }

    public function testEmptyDequeue()
    {
        /** @var \Magento\RedisMq\Model\Driver\Queue $queue */
        $queue = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);
        $this->assertNull($queue->dequeue());
    }

    public function testPushAndDequeue()
    {
        /** @var \Magento\RedisMq\Model\Driver\Queue $queue */
        $queue = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);
        /** @var \Magento\Framework\MessageQueue\EnvelopeFactory $envelopFactory */
        $envelopFactory = $this->objectManager->get(\Magento\Framework\MessageQueue\EnvelopeFactory::class);
        $messageBody = '{"data": {"body": "Message body"}, "message_id": 1}';
        $topicName = 'some.topic';
        $envelop = $envelopFactory->create(['body' => $messageBody, 'properties' => ['topic_name' => $topicName]]);

        $queue->push($envelop);

        $messageFromQueue = $queue->dequeue();
        $queue->acknowledge($messageFromQueue);

        $this->assertNotNull($messageFromQueue);
        $this->assertEquals($messageBody, $messageFromQueue->getBody());
        $actualMessageProperties = $messageFromQueue->getProperties();
        $this->assertArrayHasKey('topic_name', $actualMessageProperties);
        $this->assertEquals($topicName, $actualMessageProperties['topic_name']);

        $this->assertNull($queue->dequeue());
    }

    public function testPushOneAndDequeueAnother()
    {
        $queue1 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);
        $queue2 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);

        /** @var \Magento\Framework\MessageQueue\EnvelopeFactory $envelopFactory */
        $envelopFactory = $this->objectManager->get(\Magento\Framework\MessageQueue\EnvelopeFactory::class);
        $envelop = $envelopFactory->create(['body' => '', 'properties' => []]);

        $queue1->push($envelop);

        $messageFromQueue = $queue2->dequeue();
        $queue2->acknowledge($messageFromQueue);

        $this->assertNotNull($messageFromQueue);
        $this->assertNull($queue2->dequeue());
    }

    public function testMessageIsUnvisableForAnother()
    {
        $queue1 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);
        $queue2 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);

        /** @var \Magento\Framework\MessageQueue\EnvelopeFactory $envelopFactory */
        $envelopFactory = $this->objectManager->get(\Magento\Framework\MessageQueue\EnvelopeFactory::class);
        $envelop = $envelopFactory->create(['body' => '', 'properties' => []]);

        $queue1->push($envelop);

        $messageFromQueue = $queue1->dequeue();
        $this->assertNull($queue2->dequeue());
        $queue2->acknowledge($messageFromQueue);
        $this->assertNotNull($messageFromQueue);

    }

    public function testTwoMesssage()
    {
        $queue1 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);
        $queue2 = $this->objectManager->create(\Magento\RedisMq\Model\Driver\Queue::class, ['queueName' => 'test']);

        /** @var \Magento\Framework\MessageQueue\EnvelopeFactory $envelopFactory */
        $envelopFactory = $this->objectManager->get(\Magento\Framework\MessageQueue\EnvelopeFactory::class);

        $queue1->push($envelopFactory->create(['body' => 'm1', 'properties' => []]));
        $queue1->push($envelopFactory->create(['body' => 'm2', 'properties' => []]));

        $messageFromQueue1 = $queue2->dequeue();
        $this->assertNotNull($messageFromQueue1);
        $this->assertEquals('m1', $messageFromQueue1->getBody());

        $messageFromQueue2 = $queue1->dequeue();

        $this->assertNotNull($messageFromQueue2);
        $this->assertEquals('m2', $messageFromQueue2->getBody());

        $queue2->acknowledge($messageFromQueue1);
        $queue1->acknowledge($messageFromQueue2);
    }
}
