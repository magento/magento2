<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Model;

/**
 * Test for Queue Management class.
 */
class QueueManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var QueueManagement
     */
    protected $queueManagement;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->queueManagement = $this->objectManager->create(QueueManagement::class);
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testMessageReading()
    {
        $this->queueManagement->addMessageToQueues('topic1', 'messageBody1', ['queue1']);
        $this->queueManagement->addMessageToQueues('topic2', 'messageBody2', ['queue1']);
        $this->queueManagement->addMessageToQueues('topic3', 'messageBody3', ['queue1']);
        $maxMessagesNumber = 2;
        $messages = $this->queueManagement->readMessages('queue1', $maxMessagesNumber);

        $this->assertCount($maxMessagesNumber, $messages);

        $firstMessage = array_shift($messages);
        $this->assertEquals('topic1', $firstMessage[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody1', $firstMessage[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue1', $firstMessage[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $firstMessage[QueueManagement::MESSAGE_STATUS]
        );
        $this->assertIsNumeric($firstMessage[QueueManagement::MESSAGE_QUEUE_ID]);
        $this->assertIsNumeric($firstMessage[QueueManagement::MESSAGE_ID]);
        $this->assertIsNumeric($firstMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]);
        $this->assertEquals(0, $firstMessage[QueueManagement::MESSAGE_NUMBER_OF_TRIALS]);
        $this->assertCount(12, date_parse($firstMessage[QueueManagement::MESSAGE_UPDATED_AT]));

        $secondMessage = array_shift($messages);
        $this->assertEquals('topic2', $secondMessage[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody2', $secondMessage[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue1', $secondMessage[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $secondMessage[QueueManagement::MESSAGE_STATUS]
        );
        $this->assertIsNumeric($secondMessage[QueueManagement::MESSAGE_QUEUE_ID]);
        $this->assertIsNumeric($secondMessage[QueueManagement::MESSAGE_ID]);
        $this->assertIsNumeric($secondMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]);
        $this->assertEquals(0, $secondMessage[QueueManagement::MESSAGE_NUMBER_OF_TRIALS]);
        $this->assertCount(12, date_parse($secondMessage[QueueManagement::MESSAGE_UPDATED_AT]));
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testMessageReadingMultipleQueues()
    {
        $this->queueManagement->addMessageToQueues('topic1', 'messageBody1', ['queue1']);
        $this->queueManagement->addMessageToQueues('topic2', 'messageBody2', ['queue1', 'queue2']);
        $this->queueManagement->addMessageToQueues('topic3', 'messageBody3', ['queue2']);

        $maxMessagesNumber = 2;
        $messages = $this->queueManagement->readMessages('queue1', $maxMessagesNumber);
        $this->assertCount($maxMessagesNumber, $messages);

        $message = array_shift($messages);
        $this->assertEquals('topic1', $message[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody1', $message[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue1', $message[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $message[QueueManagement::MESSAGE_STATUS]
        );

        $message= array_shift($messages);
        $this->assertEquals('topic2', $message[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody2', $message[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue1', $message[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $message[QueueManagement::MESSAGE_STATUS]
        );

        $maxMessagesNumber = 2;
        $messages = $this->queueManagement->readMessages('queue2', $maxMessagesNumber);
        $this->assertCount($maxMessagesNumber, $messages);

        $message= array_shift($messages);
        $this->assertEquals('topic2', $message[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody2', $message[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue2', $message[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $message[QueueManagement::MESSAGE_STATUS]
        );

        $message = array_shift($messages);
        $this->assertEquals('topic3', $message[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody3', $message[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue2', $message[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $message[QueueManagement::MESSAGE_STATUS]
        );
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testChangingMessageStatus()
    {
        $this->queueManagement->addMessageToQueues('topic1', 'messageBody1', ['queue1']);
        $this->queueManagement->addMessageToQueues('topic2', 'messageBody2', ['queue1']);
        $this->queueManagement->addMessageToQueues('topic3', 'messageBody3', ['queue1']);
        $this->queueManagement->addMessageToQueues('topic4', 'messageBody4', ['queue1']);

        $maxMessagesNumber = 4;
        $messages = $this->queueManagement->readMessages('queue1', $maxMessagesNumber);
        $this->assertCount($maxMessagesNumber, $messages);

        $firstMessage = array_shift($messages);
        $secondMessage = array_shift($messages);
        $thirdMessage = array_shift($messages);
        $fourthMessage = array_shift($messages);

        $this->queueManagement->changeStatus(
            [
                $firstMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]
            ],
            QueueManagement::MESSAGE_STATUS_ERROR
        );

        $this->queueManagement->changeStatus(
            [
                $secondMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]
            ],
            QueueManagement::MESSAGE_STATUS_COMPLETE
        );

        $this->queueManagement->changeStatus(
            [
                $thirdMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]
            ],
            QueueManagement::MESSAGE_STATUS_NEW
        );

        $this->queueManagement->changeStatus(
            [
                $fourthMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]
            ],
            QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED
        );

        $messages = $this->queueManagement->readMessages('queue1');
        $this->assertCount(2, $messages);
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testMessageRetry()
    {
        $this->queueManagement->addMessageToQueues('topic1', 'messageBody1', ['queue1']);

        $messages = $this->queueManagement->readMessages('queue1', 1);
        $message = array_shift($messages);
        $messageRelationId = $message[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals($i, $message[QueueManagement::MESSAGE_NUMBER_OF_TRIALS]);
            $this->queueManagement->pushToQueueForRetry($message[QueueManagement::MESSAGE_QUEUE_RELATION_ID]);
            $messages = $this->queueManagement->readMessages('queue1', 1);
            $message = array_shift($messages);
            $this->assertEquals($messageRelationId, $message[QueueManagement::MESSAGE_QUEUE_RELATION_ID]);
        }
    }
}