<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Model\MessageStatus;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\MessageStatusCollection;
use Magento\MysqlMq\Model\ResourceModel\MessageStatusCollectionFactory;
use Magento\MysqlMq\Model\ResourceModel\Queue;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for QueueManagement model.
 */
class QueueManagementTest extends TestCase
{
    /**
     * @var Queue|MockObject
     */
    private $messageResource;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var DateTime|MockObject
     */
    private $dateTime;

    /**
     * @var MessageStatusCollectionFactory|MockObject
     */
    private $messageStatusCollectionFactory;

    /**
     * @var QueueManagement
     */
    private $queueManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->messageResource = $this->getMockBuilder(Queue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageStatusCollectionFactory = $this
            ->getMockBuilder(MessageStatusCollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->queueManagement = $objectManager->getObject(
            QueueManagement::class,
            [
                'messageResource' => $this->messageResource,
                'scopeConfig' => $this->scopeConfig,
                'dateTime' => $this->dateTime,
                'messageStatusCollectionFactory' => $this->messageStatusCollectionFactory,
            ]
        );
    }

    /**
     * Test for addMessageToQueues method.
     *
     * @return void
     */
    public function testAddMessageToQueues()
    {
        $topicName = 'topic.name';
        $queueNames = ['queue0', 'queue1'];
        $message = 'test_message';
        $messageId = 1;
        $this->messageResource->expects($this->once())
            ->method('saveMessage')->with($topicName, $message)->willReturn($messageId);
        $this->messageResource->expects($this->once())
            ->method('linkQueues')->with($messageId, $queueNames)->willReturnSelf();
        $this->assertEquals(
            $this->queueManagement,
            $this->queueManagement->addMessageToQueues($topicName, $message, $queueNames)
        );
    }

    /**
     * Test for addMessagesToQueues method.
     *
     * @return void
     */
    public function testAddMessagesToQueues()
    {
        $topicName = 'topic.name';
        $queueNames = ['queue0', 'queue1'];
        $messages = ['test_message0', 'test_message1'];
        $messageIds = [1, 2];
        $this->messageResource->expects($this->once())
            ->method('saveMessages')->with($topicName, $messages)->willReturn($messageIds);
        $this->messageResource->expects($this->once())
            ->method('linkMessagesWithQueues')->with($messageIds, $queueNames)->willReturnSelf();
        $this->assertEquals(
            $this->queueManagement,
            $this->queueManagement->addMessagesToQueues($topicName, $messages, $queueNames)
        );
    }

    /**
     * Test for markMessagesForDelete method.
     *
     * @return void
     */
    public function testMarkMessagesForDelete()
    {
        $messageId = 99;
        $collection = $this->getMockBuilder(MessageStatusCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageStatusCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->scopeConfig->expects($this->exactly(8))->method('getValue')
            ->withConsecutive(
                [
                    QueueManagement::XML_PATH_SUCCESSFUL_MESSAGES_LIFETIME,
                    ScopeInterface::SCOPE_STORE,
                ],
                [
                    QueueManagement::XML_PATH_FAILED_MESSAGES_LIFETIME,
                    ScopeInterface::SCOPE_STORE,
                ],
                [
                    QueueManagement::XML_PATH_NEW_MESSAGES_LIFETIME,
                    ScopeInterface::SCOPE_STORE,
                ],
                [
                    QueueManagement::XML_PATH_RETRY_IN_PROGRESS_AFTER,
                    ScopeInterface::SCOPE_STORE,
                ],
                [
                    QueueManagement::XML_PATH_SUCCESSFUL_MESSAGES_LIFETIME,
                    ScopeInterface::SCOPE_STORE,
                ],
                [
                    QueueManagement::XML_PATH_FAILED_MESSAGES_LIFETIME,
                    ScopeInterface::SCOPE_STORE,
                ],
                [
                    QueueManagement::XML_PATH_NEW_MESSAGES_LIFETIME,
                    ScopeInterface::SCOPE_STORE,
                ],
                [
                    QueueManagement::XML_PATH_RETRY_IN_PROGRESS_AFTER,
                    ScopeInterface::SCOPE_STORE,
                ]
            )->willReturn(1);
        $collection->expects($this->once())->method('addFieldToFilter')
            ->with(
                'status',
                [
                    'in' => [
                        QueueManagement::MESSAGE_STATUS_COMPLETE,
                        QueueManagement::MESSAGE_STATUS_ERROR,
                        QueueManagement::MESSAGE_STATUS_NEW,
                        QueueManagement::MESSAGE_STATUS_IN_PROGRESS,
                    ]
                ]
            )->willReturnSelf();
        $messageStatuses =
            [
                $this->getMessageStatusMock(),
                $this->getMessageStatusMock(),
                $this->getMessageStatusMock(),
                $this->getMessageStatusMock(),
            ];
        $this->dateTime->expects($this->exactly(4))->method('gmtTimestamp')->willReturn(1486741063);
        $messageStatuses[0]->expects($this->atLeastOnce())->method('getStatus')->willReturn(
            QueueManagement::MESSAGE_STATUS_COMPLETE
        );
        $messageStatuses[1]->expects($this->atLeastOnce())->method('getStatus')->willReturn(
            QueueManagement::MESSAGE_STATUS_ERROR
        );
        $messageStatuses[2]->expects($this->atLeastOnce())->method('getStatus')->willReturn(
            QueueManagement::MESSAGE_STATUS_NEW
        );
        $messageStatuses[3]->expects($this->atLeastOnce())->method('getStatus')->willReturn(
            QueueManagement::MESSAGE_STATUS_IN_PROGRESS
        );
        $messageStatuses[0]->expects($this->once())->method('setStatus')
            ->with(QueueManagement::MESSAGE_STATUS_TO_BE_DELETED)->willReturnSelf();
        $messageStatuses[1]->expects($this->once())->method('setStatus')
            ->with(QueueManagement::MESSAGE_STATUS_TO_BE_DELETED)->willReturnSelf();
        $messageStatuses[2]->expects($this->once())->method('setStatus')
            ->with(QueueManagement::MESSAGE_STATUS_TO_BE_DELETED)->willReturnSelf();
        $messageStatuses[0]->expects($this->once())->method('save')->willReturnSelf();
        $messageStatuses[1]->expects($this->once())->method('save')->willReturnSelf();
        $messageStatuses[2]->expects($this->once())->method('save')->willReturnSelf();
        $messageStatuses[3]->expects($this->once())->method('getId')->willReturn($messageId);
        $collection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator($messageStatuses));
        $this->messageResource->expects($this->once())->method('pushBackForRetry')->with($messageId);
        $this->messageResource->expects($this->once())->method('deleteMarkedMessages');
        $this->queueManagement->markMessagesForDelete();
    }

    /**
     * Create mock of MessageStatus method.
     *
     * @return MockObject
     */
    private function getMessageStatusMock()
    {
        $messageStatus = $this->getMockBuilder(MessageStatus::class)
            ->setMethods(['getStatus', 'setStatus', 'save', 'getId', 'getUpdatedAt'])
            ->disableOriginalConstructor()
            ->getMock();
        $messageStatus->expects($this->once())->method('getUpdatedAt')->willReturn('2010-01-01 00:00:00');
        return $messageStatus;
    }

    /**
     * Test for changeStatus method.
     */
    public function testChangeStatus()
    {
        $messageIds = [1, 2];
        $status = QueueManagement::MESSAGE_STATUS_TO_BE_DELETED;
        $this->messageResource->expects($this->once())->method('changeStatus')->with($messageIds, $status);
        $this->queueManagement->changeStatus($messageIds, $status);
    }
}
