<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

use Magento\MysqlMq\Model\QueueManagement;

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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->queueManagement = $this->objectManager->create(\Magento\MysqlMq\Model\QueueManagement::class);
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testAllFlows()
    {
        $this->queueManagement->addMessageToQueues('topic1', 'messageBody1', ['queue1', 'queue2']);
        $this->queueManagement->addMessageToQueues('topic2', 'messageBody2', ['queue2', 'queue3']);
        $this->queueManagement->addMessageToQueues('topic3', 'messageBody3', ['queue1', 'queue3']);
        $this->queueManagement->addMessageToQueues('topic4', 'messageBody4', ['queue1', 'queue2', 'queue3']);
        $maxMessagesNumber = 2;
        $messages = $this->queueManagement->readMessages('queue3', $maxMessagesNumber);

        $this->assertCount($maxMessagesNumber, $messages);

        $firstMessage = array_shift($messages);
        $this->assertEquals('topic2', $firstMessage[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody2', $firstMessage[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue3', $firstMessage[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $firstMessage[QueueManagement::MESSAGE_STATUS]
        );
        $this->assertTrue(is_numeric($firstMessage[QueueManagement::MESSAGE_QUEUE_ID]));
        $this->assertTrue(is_numeric($firstMessage[QueueManagement::MESSAGE_ID]));
        $this->assertTrue(is_numeric($firstMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]));
        $this->assertEquals(0, $firstMessage[QueueManagement::MESSAGE_NUMBER_OF_TRIALS]);
        $this->assertCount(12, date_parse($firstMessage[QueueManagement::MESSAGE_UPDATED_AT]));

        $secondMessage = array_shift($messages);
        $this->assertEquals('topic3', $secondMessage[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody3', $secondMessage[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue3', $secondMessage[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
            $secondMessage[QueueManagement::MESSAGE_STATUS]
        );
        $this->assertTrue(is_numeric($secondMessage[QueueManagement::MESSAGE_QUEUE_ID]));
        $this->assertTrue(is_numeric($secondMessage[QueueManagement::MESSAGE_ID]));
        $this->assertTrue(is_numeric($secondMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]));
        $this->assertEquals(0, $secondMessage[QueueManagement::MESSAGE_NUMBER_OF_TRIALS]);
        $this->assertCount(12, date_parse($secondMessage[QueueManagement::MESSAGE_UPDATED_AT]));

        /** Mark one message as complete or failed and make sure it is not displayed in the list of read messages */
        $this->queueManagement->changeStatus(
            [
                $secondMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]
            ],
            QueueManagement::MESSAGE_STATUS_COMPLETE
        );
        $messages = $this->queueManagement->readMessages('queue3', $maxMessagesNumber);
        $this->assertCount(1, $messages);

        $this->queueManagement->changeStatus(
            [
                $firstMessage[QueueManagement::MESSAGE_QUEUE_RELATION_ID]
            ],
            QueueManagement::MESSAGE_STATUS_ERROR
        );
        $messages = $this->queueManagement->readMessages('queue3', $maxMessagesNumber);
        $this->assertCount(0, $messages);

        /** Ensure that message for retry is still accessible when reading messages from the queue */
        $messages = $this->queueManagement->readMessages('queue2', 1);
        $message = array_shift($messages);
        $messageRelationId = $message[QueueManagement::MESSAGE_QUEUE_RELATION_ID];

        for ($i = 0; $i < 2; $i++) {
            $this->assertEquals($i, $message[QueueManagement::MESSAGE_NUMBER_OF_TRIALS]);
            $this->queueManagement->pushToQueueForRetry($message[QueueManagement::MESSAGE_QUEUE_RELATION_ID]);
            $messages = $this->queueManagement->readMessages('queue2', 1);
            $message = array_shift($messages);
            $this->assertEquals($messageRelationId, $message[QueueManagement::MESSAGE_QUEUE_RELATION_ID]);
        }
    }
}
