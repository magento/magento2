<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model;

use Magento\Framework\MessageQueue\Consumer;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\Queue;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests the different cases of consumers running by Consumer processor
 */
class ConsumerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Consumer
     */
    private $model;

    /**
     * @var Queue
     */
    private $queueResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        /** @var ConsumerFactory $factory */
        $factory = $this->objectManager->get(ConsumerFactory::class);
        $this->model = $factory->get('demoConsumerWithConnectionException');
        $this->queueResource = $this->objectManager->get(Queue::class);
    }

    /**
     * Test if after connection exception and retry
     * message doesn't have success status but still has status in progress
     *
     * @return void
     */
    public function testRunWithException(): void
    {
        /** @var EnvelopeFactory $envelopFactory */
        $envelopFactory = $this->objectManager->get(EnvelopeFactory::class);
        $messageBody = '{"name":"test"}';
        $topicName = 'demo.connection.exception';
        $queueName = 'queue-connection-exception';
        $envelope = $envelopFactory->create(['body' => $messageBody, 'properties' => ['topic_name' => $topicName]]);
        /** @var QueueInterface $queue */
        $queue = $this->objectManager->create(
            \Magento\MysqlMq\Model\Driver\Queue::class,
            ['queueName' => $queueName]
        );
        $queue->push($envelope);
        $messages = $this->queueResource->getMessages($queueName, 1);
        $envelope = $envelopFactory->create(['body' => $messageBody, 'properties' => $messages[0]]);
        $this->model->process(1);
        $queue->reject($envelope);
        $this->model->process(1);
        $message = $this->getLastMessage($queueName);
        $this->assertEquals(QueueManagement::MESSAGE_STATUS_IN_PROGRESS, $message['status']);
    }

    /**
     * Return last message by queue name
     *
     * @param string $queueName
     * @return array
     */
    private function getLastMessage(string $queueName)
    {
        $connection = $this->queueResource->getConnection();
        $select = $connection->select()
            ->from(
                ['queue_message' => $this->queueResource->getTable('queue_message')],
                []
            )->join(
                ['queue_message_status' => $this->queueResource->getTable('queue_message_status')],
                'queue_message.id = queue_message_status.message_id',
                [
                    QueueManagement::MESSAGE_QUEUE_RELATION_ID => 'id',
                    QueueManagement::MESSAGE_STATUS => 'status',
                ]
            )->join(
                ['queue' => $this->queueResource->getTable('queue')],
                'queue.id = queue_message_status.queue_id',
                [QueueManagement::MESSAGE_QUEUE_NAME => 'name']
            )->where('queue.name = ?', $queueName)
            ->order(['queue_message_status.id DESC']);

        return $connection->fetchRow($select);
    }
}
