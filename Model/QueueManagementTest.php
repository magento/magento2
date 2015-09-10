<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

use Magento\MysqlMq\Model\QueueManagement;

/**
 * Test for Queue Management class.
 */
class QueueManagementTest extends \PHPUnit_Framework_TestCase
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
        $this->queueManagement = $this->objectManager->create('Magento\MysqlMq\Model\QueueManagement');
    }

    /**
     * @magentoDataFixture Magento/MysqlMq/_files/queues.php
     */
    public function testAddAndRead()
    {
        $this->queueManagement->addMessageToQueues('topic1', 'messageBody1', ['queue1', 'queue2']);
        $this->queueManagement->addMessageToQueues('topic2', 'messageBody2', ['queue2', 'queue3']);
        $this->queueManagement->addMessageToQueues('topic3', 'messageBody3', ['queue1', 'queue3']);
        $this->queueManagement->addMessageToQueues('topic4', 'messageBody4', ['queue1', 'queue2', 'queue3']);
        $maxMessagesNumber = 2;
        $messages = $this->queueManagement->readMessages('queue3', $maxMessagesNumber);

        $this->assertCount($maxMessagesNumber, $messages);

        $firstMessage = $messages[0];
        $this->assertEquals('topic2', $firstMessage[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody2', $firstMessage[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue3', $firstMessage[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(QueueManagement::MESSAGE_STATUS_NEW, $firstMessage[QueueManagement::MESSAGE_STATUS]);
        $this->assertTrue(is_numeric($firstMessage[QueueManagement::MESSAGE_QUEUE_ID]));
        $this->assertTrue(is_numeric($firstMessage[QueueManagement::MESSAGE_ID]));
        $this->assertCount(12, date_parse($firstMessage[QueueManagement::MESSAGE_UPDATED_AT]));

        $secondMessage = $messages[1];
        $this->assertEquals('topic3', $secondMessage[QueueManagement::MESSAGE_TOPIC]);
        $this->assertEquals('messageBody3', $secondMessage[QueueManagement::MESSAGE_BODY]);
        $this->assertEquals('queue3', $secondMessage[QueueManagement::MESSAGE_QUEUE_NAME]);
        $this->assertEquals(QueueManagement::MESSAGE_STATUS_NEW, $secondMessage[QueueManagement::MESSAGE_STATUS]);
        $this->assertTrue(is_numeric($secondMessage[QueueManagement::MESSAGE_QUEUE_ID]));
        $this->assertTrue(is_numeric($secondMessage[QueueManagement::MESSAGE_ID]));
        $this->assertCount(12, date_parse($secondMessage[QueueManagement::MESSAGE_UPDATED_AT]));
    }
}
