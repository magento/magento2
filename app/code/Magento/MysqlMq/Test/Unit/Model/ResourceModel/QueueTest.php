<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Model\QueueManagement;
use Magento\MysqlMq\Model\ResourceModel\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Queue resource model.
 */
class QueueTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resources;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->resources = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->queue = $objectManager->getObject(
            Queue::class,
            [
                '_resources' => $this->resources,
            ]
        );
    }

    /**
     * Test for saveMessage method.
     *
     * @return void
     */
    public function testSaveMessage()
    {
        $messageTopic = 'topic.name';
        $message = 'messageBody';
        $tableName = 'queue_message';
        $messageId = 2;
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->addMethods(['lastInsertId'])
            ->onlyMethods(['insert'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->exactly(2))->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->once())
            ->method('getTableName')->with($tableName, 'default')->willReturn($tableName);
        $connection->expects($this->once())->method('insert')
            ->with($tableName, ['topic_name' => $messageTopic, 'body' => $message])->willReturn(1);
        $connection->expects($this->once())->method('lastInsertId')->with($tableName)->willReturn($messageId);
        $this->assertEquals($messageId, $this->queue->saveMessage($messageTopic, $message));
    }

    /**
     * Test for saveMessages method.
     *
     * @return void
     */
    public function testSaveMessages()
    {
        $messageTopic = 'topic.name';
        $messages = ['messageBody0', 'messageBody1'];
        $tableName = 'queue_message';
        $messageIds = [3, 4];
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->onlyMethods(['insertMultiple'])
            ->addMethods(['lastInsertId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->once())
            ->method('getTableName')->with($tableName, 'default')->willReturn($tableName);
        $connection->expects($this->once())->method('insertMultiple')
            ->with(
                $tableName,
                [
                    ['topic_name' => $messageTopic, 'body' => $messages[0]],
                    ['topic_name' => $messageTopic, 'body' => $messages[1]],
                ]
            )->willReturn(2);
        $connection->expects($this->once())->method('lastInsertId')->with($tableName)->willReturn($messageIds[0]);
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->with(['qm' => $tableName], ['id'])->willReturnSelf();
        $select->expects($this->once())->method('where')->with('qm.id >= ?', $messageIds[0])->willReturnSelf();
        $select->expects($this->once())->method('limit')->with(2)->willReturnSelf();
        $connection->expects($this->once())->method('fetchCol')->with($select)->willReturn($messageIds);
        $this->assertEquals($messageIds, $this->queue->saveMessages($messageTopic, $messages));
    }

    /**
     * Test for linkQueues method.
     *
     * @return void
     */
    public function testLinkQueues()
    {
        $messageId = 3;
        $queueNames = ['queueName0', 'queueName1'];
        $queueIds = [5, 6];
        $tableNames = ['queue', 'queue_message_status'];
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->exactly(2))->method('getTableName')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($tableNames) {
                    if ($arg1 == $tableNames[0] && $arg2 == 'default') {
                        return $tableNames[0];
                    } elseif ($arg1 == $tableNames[1] && $arg2 == 'default') {
                        return $tableNames[1];
                    }
                }
            );
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->with(['queue' => $tableNames[0]])->willReturnSelf();
        $select->expects($this->once())->method('columns')->with(['id'])->willReturnSelf();
        $select->expects($this->once())->method('where')->with('queue.name IN (?)', $queueNames)->willReturnSelf();
        $connection->expects($this->once())->method('fetchCol')->with($select)->willReturn($queueIds);
        $connection->expects($this->once())->method('insertArray')->with(
            $tableNames[1],
            ['queue_id', 'message_id', 'status'],
            [
                [
                    $queueIds[0],
                    $messageId,
                    QueueManagement::MESSAGE_STATUS_NEW
                ],
                [
                    $queueIds[1],
                    $messageId,
                    QueueManagement::MESSAGE_STATUS_NEW
                ],
            ]
        )->willReturn(4);
        $this->assertEquals($this->queue, $this->queue->linkQueues($messageId, $queueNames));
    }

    /**
     * Test for getMessages method.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    public function testGetMessages(): void
    {
        $limit = 100;
        $queueName = 'queueName0';
        $tableNames = ['queue_message', 'queue_message_status', 'queue'];
        $messages = [['message0_data'], ['message1_data']];
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->exactly(3))->method('getTableName')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($tableNames) {
                    if ($arg1 == $tableNames[0] && $arg2 == 'default') {
                        return $tableNames[0];
                    } elseif ($arg1 == $tableNames[1] && $arg2 == 'default') {
                        return $tableNames[1];
                    } elseif ($arg1 == $tableNames[2] && $arg2 == 'default') {
                        return $tableNames[2];
                    }
                }
            );
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->with(
            ['queue_message' => $tableNames[0]],
            [
                QueueManagement::MESSAGE_TOPIC => 'topic_name',
                QueueManagement::MESSAGE_BODY => 'body'
            ]
        )->willReturnSelf();
        $select->expects($this->exactly(2))->method('join')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($tableNames, $select) {
                    if ($arg1 === ['queue_message_status' => $tableNames[1]] &&
                        $arg2 === 'queue_message.id = queue_message_status.message_id' &&
                        $arg3 === [
                            QueueManagement::MESSAGE_QUEUE_RELATION_ID => 'id',
                            QueueManagement::MESSAGE_QUEUE_ID => 'queue_id',
                            QueueManagement::MESSAGE_ID => 'message_id',
                            QueueManagement::MESSAGE_STATUS => 'status',
                            QueueManagement::MESSAGE_UPDATED_AT => 'updated_at',
                            QueueManagement::MESSAGE_NUMBER_OF_TRIALS => 'number_of_trials'
                        ]
                    ) {
                        return $select;
                    } elseif ($arg1 === ['queue' => $tableNames[2]] &&
                        $arg2 === 'queue.id = queue_message_status.queue_id' &&
                        $arg3 === [QueueManagement::MESSAGE_QUEUE_NAME => 'name']
                    ) {
                        return $select;
                    }
                }
            );
        $select->expects($this->exactly(2))->method('where')
            ->willReturnCallback(
                function ($arg1, $arg2 = null) use ($queueName, $select) {
                    if ($arg1 == 'queue_message_status.status IN (?)' &&
                        $arg2 == [QueueManagement::MESSAGE_STATUS_NEW, QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED]
                    ) {
                        return $select;
                    } elseif ($arg1 == 'queue.name = ?' &&
                        $arg2 == $queueName
                    ) {
                        return $select;
                    }
                }
            );
        $select->expects($this->once())
            ->method('order')
            ->with(['queue_message_status.updated_at ASC', 'queue_message_status.id ASC'])
            ->willReturnSelf();
        $select->expects($this->once())->method('limit')->with($limit)->willReturnSelf();
        $connection->expects($this->once())->method('fetchAll')->with($select)->willReturn($messages);
        $this->assertEquals($messages, $this->queue->getMessages($queueName, $limit));
    }

    /**
     * Test for deleteMarkedMessages method.
     *
     * @return void
     */
    public function testDeleteMarkedMessages()
    {
        $tableNames = ['queue_message_status', 'queue_message'];
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->exactly(2))->method('getTableName')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($tableNames) {
                    if ($arg1 == $tableNames[0] && $arg2 == 'default') {
                        return $tableNames[0];
                    } elseif ($arg1 == $tableNames[1] && $arg2 == 'default') {
                        return $tableNames[1];
                    }
                }
            );
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())
            ->method('from')->with(['queue_message_status' => $tableNames[0]], ['message_id'])->willReturnSelf();
        $select->expects($this->once())->method('joinLeft')
            ->with(
                ['message_status2' => 'queue_message_status'],
                'queue_message_status.message_id = message_status2.message_id AND message_status2.status <> ' .
                QueueManagement::MESSAGE_STATUS_TO_BE_DELETED,
                []
            )
            ->willReturnSelf();
        $select->expects($this->exactly(2))->method('where')
            ->willReturnSelf();
        $select->expects($this->once())->method('distinct')->willReturnSelf();
        $connection->expects($this->once())->method('fetchCol')->with($select)->willReturn([1, 2]);

        $connection->expects($this->once())->method('delete')
            ->with(
                $tableNames[1],
                ['id IN (?)' => [1,2]]
            )->willReturn(2);
        $this->queue->deleteMarkedMessages();
    }

    /**
     * Test for takeMessagesInProgress method.
     *
     * @return void
     */
    public function testTakeMessagesInProgress()
    {
        $relationIds = [1, 2];
        $tableName = 'queue_message_status';
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->once())->method('getTableName')->with($tableName)->willReturn($tableName);
        $connection->expects($this->exactly(2))->method('update')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($relationIds, $tableName) {
                    if ($arg1 == $tableName &&
                        $arg2 == ['status' => QueueManagement::MESSAGE_STATUS_IN_PROGRESS] &&
                        $arg3 == ['id = ?' => $relationIds[0]]
                    ) {
                        return 1;
                    } elseif ($arg1 == $tableName &&
                        $arg2 == ['status' => QueueManagement::MESSAGE_STATUS_IN_PROGRESS] &&
                        $arg3 == ['id = ?' => $relationIds[1]]
                    ) {
                        return 0;
                    }
                }
            );
        $this->assertEquals([$relationIds[0]], $this->queue->takeMessagesInProgress($relationIds));
    }

    /**
     * Test for pushBackForRetry method.
     *
     * @return void
     */
    public function testPushBackForRetry()
    {
        $relationId = 1;
        $tableName = 'queue_message_status';
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->once())->method('getTableName')->with($tableName)->willReturn($tableName);
        $connection->expects($this->once())->method('update')->with(
            $tableName,
            [
                'status' => QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED,
                'number_of_trials' => new \Zend_Db_Expr('number_of_trials+1')
            ],
            ['id = ?' => $relationId]
        )->willReturn(1);
        $this->queue->pushBackForRetry($relationId);
    }

    /**
     * Test for changeStatus method.
     *
     * @return void
     */
    public function testChangeStatus()
    {
        $relationIds = [1, 2];
        $status = QueueManagement::MESSAGE_STATUS_RETRY_REQUIRED;
        $tableName = 'queue_message_status';
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->once())->method('getTableName')->with($tableName)->willReturn($tableName);
        $connection->expects($this->once())
            ->method('update')->with($tableName, ['status' => $status], ['id IN (?)' => $relationIds])->willReturn(1);
        $this->queue->changeStatus($relationIds, $status);
    }
}
