<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model\Driver;

use Magento\MysqlMq\Model\Driver\Queue;

/**
 * Test for MySQL queue driver class.
 *
 * @magentoDbIsolation disabled
 */
class QueueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\MessageQueue\Config\Data $queueConfig */
        $queueConfig = $this->objectManager->get(\Magento\Framework\MessageQueue\Config\Data::class);
        $queueConfig->reset();

        $this->queue = $this->objectManager->create(
            \Magento\MysqlMq\Model\Driver\Queue::class,
            ['queueName' => 'queue2']
        );
    }

    protected function tearDown()
    {
        /** @var \Magento\Framework\MessageQueue\Config\Data $queueConfig */
        $queueConfig = $this->objectManager->get(\Magento\Framework\MessageQueue\Config\Data::class);
        $queueConfig->reset();
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testPushAndDequeue()
    {
        /** @var \Magento\Framework\MessageQueue\EnvelopeFactory $envelopFactory */
        $envelopFactory = $this->objectManager->get(\Magento\Framework\MessageQueue\EnvelopeFactory::class);
        $messageBody = '{"data": {"body": "Message body"}, "message_id": 1}';
        $topicName = 'some.topic';
        $envelop = $envelopFactory->create(['body' => $messageBody, 'properties' => ['topic_name' => $topicName]]);

        $this->queue->push($envelop);

        $messageFromQueue = $this->queue->dequeue();

        $this->assertEquals($messageBody, $messageFromQueue->getBody());
        $actualMessageProperties = $messageFromQueue->getProperties();
        $this->assertArrayHasKey('topic_name', $actualMessageProperties);
        $this->assertEquals($topicName, $actualMessageProperties['topic_name']);
    }
}
