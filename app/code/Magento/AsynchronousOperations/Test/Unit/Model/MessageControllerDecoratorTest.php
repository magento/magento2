<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\MessageControllerDecorator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for MessageControllerDecorator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageControllerDecoratorTest extends TestCase
{
    /**
     * @var MessageControllerDecorator
     */
    private $model;

    /**
     * @var MessageController|MockObject
     */
    private $messageController;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /**
     * @var MessageEncoder|MockObject
     */
    private $messageEncoder;

    /**
     * @var DateTime|MockObject
     */
    private $dateTime;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->messageController = $this->createMock(MessageController::class);
        $messageValidator = $this->createMock(MessageValidator::class);
        $this->messageEncoder = $this->createMock(MessageEncoder::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->dateTime = $this->createMock(DateTime::class);
        $this->model = new MessageControllerDecorator(
            $this->resource,
            $this->messageController,
            $messageValidator,
            $this->messageEncoder,
            $this->metadataPool,
            $this->dateTime
        );
    }

    /**
     * Check that message lock is created and operation start time is updated
     */
    public function testLock(): void
    {
        $bUuid = uniqid();
        $operationId = 0;
        $operationTableName = 'table_1';
        $connectionName = 'connection_1';
        $timestamp = 1631104794;
        $date = '2021-09-08 12:39:54';
        $this->dateTime->method('gmtTimestamp')
            ->willReturn($timestamp);
        $metadata = $this->createMock(EntityMetadataInterface::class);
        $metadata->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $metadata->method('getEntityTable')
            ->willReturn($operationTableName);
        $this->metadataPool->method('getMetadata')
            ->with(OperationInterface::class)
            ->willReturn($metadata);
        $connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')
            ->with($connectionName)
            ->willReturn($connection);
        $operation = $this->createMock(OperationInterface::class);
        $operation->method('getId')
            ->willReturn($operationId);
        $operation->method('getBulkUuid')
            ->willReturn($bUuid);
        $this->messageEncoder->method('decode')
            ->willReturn($operation);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $lock = $this->createMock(LockInterface::class);
        $consumerName = 'consumer_1';
        $this->messageController->expects($this->once())
            ->method('lock')
            ->with($envelope, $consumerName)
            ->willReturn($lock);
        $connection->expects($this->once())
            ->method('beginTransaction');
        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->never())
            ->method('rollBack');
        $connection->expects($this->once())
            ->method('formatDate')
            ->with($timestamp)
            ->willReturn($date);
        $connection->expects($this->once())
            ->method('update')
            ->with(
                $operationTableName,
                [
                    'started_at' => $date
                ],
                [
                    'bulk_uuid = ?' => $bUuid,
                    'operation_key = ?' => $operationId
                ]
            );
        $this->assertSame($lock, $this->model->lock($envelope, $consumerName));
    }

    /**
     * ER_CHECKREAD / similar messages should trigger one retry after rollback.
     */
    public function testLockRetriesOnceWhenUpdateFailsWithRecordChangedMessage(): void
    {
        $bUuid = uniqid();
        $operationId = 0;
        $operationTableName = 'table_1';
        $connectionName = 'connection_1';
        $timestamp = 1631104794;
        $date = '2021-09-08 12:39:54';
        $this->dateTime->method('gmtTimestamp')
            ->willReturn($timestamp);
        $metadata = $this->createMock(EntityMetadataInterface::class);
        $metadata->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $metadata->method('getEntityTable')
            ->willReturn($operationTableName);
        $this->metadataPool->method('getMetadata')
            ->with(OperationInterface::class)
            ->willReturn($metadata);
        $connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')
            ->with($connectionName)
            ->willReturn($connection);
        $operation = $this->createMock(OperationInterface::class);
        $operation->method('getId')
            ->willReturn($operationId);
        $operation->method('getBulkUuid')
            ->willReturn($bUuid);
        $this->messageEncoder->method('decode')
            ->willReturn($operation);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $lock = $this->createMock(LockInterface::class);
        $consumerName = 'consumer_1';
        $this->messageController->expects($this->exactly(2))
            ->method('lock')
            ->with($envelope, $consumerName)
            ->willReturn($lock);
        $connection->expects($this->exactly(2))
            ->method('beginTransaction');
        $connection->expects($this->once())
            ->method('rollBack');
        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->exactly(2))
            ->method('formatDate')
            ->with($timestamp)
            ->willReturn($date);
        $connection->expects($this->exactly(2))
            ->method('update')
            ->with(
                $operationTableName,
                [
                    'started_at' => $date
                ],
                [
                    'bulk_uuid = ?' => $bUuid,
                    'operation_key = ?' => $operationId
                ]
            )->willReturnOnConsecutiveCalls(
                $this->throwException(
                    new \Exception(
                        'SQLSTATE[HY000]: General error: 1020 Record has changed since last read in table '
                        . '`magento_operation`'
                    )
                ),
                1
            );
        $this->assertSame($lock, $this->model->lock($envelope, $consumerName));
    }

    /**
     * DeadlockException should be retried until update succeeds.
     */
    public function testLockRetriesOnceOnDeadlockException(): void
    {
        $bUuid = uniqid();
        $operationId = 0;
        $operationTableName = 'table_1';
        $connectionName = 'connection_1';
        $timestamp = 1631104794;
        $date = '2021-09-08 12:39:54';
        $this->dateTime->method('gmtTimestamp')
            ->willReturn($timestamp);
        $metadata = $this->createMock(EntityMetadataInterface::class);
        $metadata->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $metadata->method('getEntityTable')
            ->willReturn($operationTableName);
        $this->metadataPool->method('getMetadata')
            ->with(OperationInterface::class)
            ->willReturn($metadata);
        $connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')
            ->with($connectionName)
            ->willReturn($connection);
        $operation = $this->createMock(OperationInterface::class);
        $operation->method('getId')
            ->willReturn($operationId);
        $operation->method('getBulkUuid')
            ->willReturn($bUuid);
        $this->messageEncoder->method('decode')
            ->willReturn($operation);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $lock = $this->createMock(LockInterface::class);
        $consumerName = 'consumer_1';
        $this->messageController->expects($this->exactly(2))
            ->method('lock')
            ->with($envelope, $consumerName)
            ->willReturn($lock);
        $connection->expects($this->exactly(2))
            ->method('beginTransaction');
        $connection->expects($this->once())
            ->method('rollBack');
        $connection->expects($this->once())
            ->method('commit');
        $connection->expects($this->exactly(2))
            ->method('formatDate')
            ->with($timestamp)
            ->willReturn($date);
        $connection->expects($this->exactly(2))
            ->method('update')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new DeadlockException('Deadlock found when trying to get lock', 1213)),
                1
            );
        $this->assertSame($lock, $this->model->lock($envelope, $consumerName));
    }

    /**
     * Non-transient failures must not retry message controller lock.
     */
    public function testLockDoesNotRetryOnNonTransientUpdateFailure(): void
    {
        $bUuid = uniqid();
        $operationId = 0;
        $operationTableName = 'table_1';
        $connectionName = 'connection_1';
        $timestamp = 1631104794;
        $date = '2021-09-08 12:39:54';
        $this->dateTime->method('gmtTimestamp')
            ->willReturn($timestamp);
        $metadata = $this->createMock(EntityMetadataInterface::class);
        $metadata->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $metadata->method('getEntityTable')
            ->willReturn($operationTableName);
        $this->metadataPool->method('getMetadata')
            ->with(OperationInterface::class)
            ->willReturn($metadata);
        $connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')
            ->with($connectionName)
            ->willReturn($connection);
        $operation = $this->createMock(OperationInterface::class);
        $operation->method('getId')
            ->willReturn($operationId);
        $operation->method('getBulkUuid')
            ->willReturn($bUuid);
        $this->messageEncoder->method('decode')
            ->willReturn($operation);
        $envelope = $this->createMock(EnvelopeInterface::class);
        $lock = $this->createMock(LockInterface::class);
        $consumerName = 'consumer_1';
        $this->messageController->expects($this->once())
            ->method('lock')
            ->with($envelope, $consumerName)
            ->willReturn($lock);
        $connection->expects($this->once())
            ->method('beginTransaction');
        $connection->expects($this->once())
            ->method('rollBack');
        $connection->expects($this->never())
            ->method('commit');
        $connection->expects($this->once())
            ->method('formatDate')
            ->with($timestamp)
            ->willReturn($date);
        $connection->expects($this->once())
            ->method('update')
            ->willThrowException(new \Exception('Unknown SQL error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown SQL error');
        $this->model->lock($envelope, $consumerName);
    }
}
