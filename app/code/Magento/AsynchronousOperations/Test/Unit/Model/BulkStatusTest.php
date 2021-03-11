<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\BulkStatus;
use Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection as BulkCollection;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory as BulkCollectionFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection as OperationCollection;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory as OperationCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Magento\AsynchronousOperations\Model\BulkStatus class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkStatusTest extends TestCase
{
    /**
     * @var BulkStatus
     */
    private $model;

    /**
     * @var BulkCollectionFactory|MockObject
     */
    private $bulkCollectionFactory;

    /**
     * @var OperationCollectionFactory|MockObject
     */
    private $operationCollectionFactory;

    /**
     * @var OperationInterface|MockObject
     */
    private $operationMock;

    /**
     * @var BulkSummaryInterface|MockObject
     */
    private $bulkMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var CalculatedStatusSql|MockObject
     */
    private $calculatedStatusSqlMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityMetadataInterface|MockObject
     */
    private $entityMetadataMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->bulkCollectionFactory = $this->createPartialMock(BulkCollectionFactory::class, ['create']);
        $this->operationCollectionFactory = $this->createPartialMock(OperationCollectionFactory::class, ['create']);
        $this->operationMock = $this->getMockForAbstractClass(OperationInterface::class);
        $this->bulkMock = $this->getMockForAbstractClass(BulkSummaryInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->calculatedStatusSqlMock = $this->createMock(CalculatedStatusSql::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->entityMetadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->model = new BulkStatus(
            $this->bulkCollectionFactory,
            $this->operationCollectionFactory,
            $this->resourceConnectionMock,
            $this->calculatedStatusSqlMock,
            $this->metadataPoolMock
        );
    }

    /**
     * @param int|null $failureType
     * @param array $failureCodes
     * @return void
     * @dataProvider getFailedOperationsByBulkIdDataProvider
     */
    public function testGetFailedOperationsByBulkId($failureType, $failureCodes)
    {
        $bulkUuid = 'bulk-1';
        $operationCollection = $this->createMock(OperationCollection::class);
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $operationCollection
            ->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with('bulk_uuid', $bulkUuid)
            ->willReturnSelf();
        $operationCollection
            ->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('status', $failureCodes)
            ->willReturnSelf();
        $operationCollection->expects($this->once())->method('getItems')->willReturn([$this->operationMock]);
        $this->assertEquals([$this->operationMock], $this->model->getFailedOperationsByBulkId($bulkUuid, $failureType));
    }

    /**
     * @return void
     */
    public function testGetOperationsCountByBulkIdAndStatus()
    {
        $bulkUuid = 'bulk-1';
        $status = 1354;
        $size = 32;

        $operationCollection = $this->createMock(OperationCollection::class);
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $operationCollection
            ->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with('bulk_uuid', $bulkUuid)
            ->willReturnSelf();
        $operationCollection
            ->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('status', $status)
            ->willReturnSelf();
        $operationCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($size);
        $this->assertEquals($size, $this->model->getOperationsCountByBulkIdAndStatus($bulkUuid, $status));
    }

    /**
     * @return void
     */
    public function testGetOperationsCountByBulkIdAndOpenStatus()
    {
        $bulkUuid = 'bulk-2';
        $status = OperationInterface::STATUS_TYPE_OPEN;
        $size = 32;

        $operationCollection = $this->createMock(OperationCollection::class);
        $this->operationCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($operationCollection);
        $operationCollection
            ->expects($this->exactly(3))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['bulk_uuid', $bulkUuid],
                ['bulk_uuid', $bulkUuid],
                ['status', $status]
            )
            ->willReturnSelf();
        $operationCollection
            ->expects($this->exactly(2))
            ->method('getSize')
            ->willReturn($size);
        $operationCollection
            ->expects($this->once())
            ->method('clear')
            ->willReturnSelf();
        $this->assertEquals($size, $this->model->getOperationsCountByBulkIdAndStatus($bulkUuid, $status));
    }

    /**
     * @return void
     */
    public function testGetNotStartedOperationsCountByBulkIdAndOpenStatus()
    {
        $bulkUuid = 'bulk-3';
        $bulkOperationCount = 3;
        $status = OperationInterface::STATUS_TYPE_OPEN;
        $size = 0;

        $operationCollection = $this->createMock(OperationCollection::class);
        $this->operationCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($operationCollection);
        $operationCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with('bulk_uuid', $bulkUuid)
            ->willReturnSelf();
        $operationCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($size);

        $connectionName = 'connection_name';
        $entityType = BulkSummaryInterface::class;
        $this->metadataPoolMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($this->entityMetadataMock);
        $this->entityMetadataMock
            ->expects($this->once())
            ->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($this->connectionMock);
        $selectMock = $this->createMock(Select::class);
        $selectMock
            ->expects($this->once())
            ->method('from')
            ->willReturnSelf();
        $selectMock
            ->expects($this->once())
            ->method('where')
            ->with('uuid = ?', $bulkUuid)
            ->willReturnSelf();
        $this->connectionMock
            ->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->connectionMock
            ->expects($this->once())
            ->method('fetchOne')
            ->with($selectMock)
            ->willReturn($bulkOperationCount);

        $this->assertEquals($bulkOperationCount, $this->model->getOperationsCountByBulkIdAndStatus($bulkUuid, $status));
    }

    /**
     * @return array
     */
    public function getFailedOperationsByBulkIdDataProvider()
    {
        return [
            [1, [1]],
            [
                null,
                [
                    OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
                    OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetBulksByUser()
    {
        $userId = 1;
        $selectMock = $this->createMock(Select::class);
        $bulkCollection = $this->createMock(BulkCollection::class);
        $bulkCollection->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $selectMock->expects($this->once())->method('columns')->willReturnSelf();
        $selectMock->expects($this->once())->method('order')->willReturnSelf();
        $this->bulkCollectionFactory->expects($this->once())->method('create')->willReturn($bulkCollection);
        $bulkCollection->expects($this->once())->method('addFieldToFilter')->with('user_id', $userId)->willReturnSelf();
        $bulkCollection->expects($this->once())->method('getItems')->willReturn([$this->bulkMock]);
        $this->assertEquals([$this->bulkMock], $this->model->getBulksByUser($userId));
    }

    /**
     * @return void
     */
    public function testGetBulksStatus()
    {
        $bulkUuid = 'bulk-1';
        $allProcessedOperationCollection = $this->createMock(OperationCollection::class);
        $completeOperationCollection = $this->createMock(OperationCollection::class);

        $connectionName = 'connection_name';
        $entityType = BulkSummaryInterface::class;
        $this->metadataPoolMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($this->entityMetadataMock);
        $this->entityMetadataMock
            ->expects($this->once())
            ->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $this->resourceConnectionMock
            ->expects($this->once())
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($this->connectionMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->once())->method('from')->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('uuid = ?', $bulkUuid)->willReturnSelf();
        $this->connectionMock->expects($this->once())->method('select')->willReturn($selectMock);
        $this->connectionMock->expects($this->once())->method('fetchOne')->with($selectMock)->willReturn(10);

        $this->operationCollectionFactory
            ->expects($this->at(0))
            ->method('create')
            ->willReturn($allProcessedOperationCollection);
        $this->operationCollectionFactory
            ->expects($this->at(1))
            ->method('create')
            ->willReturn($completeOperationCollection);
        $allProcessedOperationCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with('bulk_uuid', $bulkUuid)
            ->willReturnSelf();
        $allProcessedOperationCollection->expects($this->once())->method('getSize')->willReturn(5);

        $completeOperationCollection
            ->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with('bulk_uuid', $bulkUuid)
            ->willReturnSelf();
        $completeOperationCollection
            ->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('status', OperationInterface::STATUS_TYPE_COMPLETE)
            ->willReturnSelf();
        $completeOperationCollection->expects($this->any())->method('getSize')->willReturn(5);
        $this->assertEquals(BulkSummaryInterface::IN_PROGRESS, $this->model->getBulkStatus($bulkUuid));
    }
}
