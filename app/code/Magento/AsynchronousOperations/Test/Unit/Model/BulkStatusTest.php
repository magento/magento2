<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\BulkOperationsStatusInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperations\Api\Data\DetailedBulkOperationsStatusInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\BulkStatus;
use Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkStatusTest extends TestCase
{
    /**
     * @var BulkStatus
     */
    private $model;

    /**
     * @var MockObject
     */
    private $bulkCollectionFactory;

    /**
     * @var MockObject
     */
    private $operationCollectionFactory;

    /**
     * @var MockObject
     */
    private $operationMock;

    /**
     * @var MockObject
     */
    private $bulkMock;

    /**
     * @var MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MockObject
     */
    private $calculatedStatusSqlMock;

    /**
     * @var MockObject
     */
    private $metadataPoolMock;

    /**
     * @var MockObject
     */
    private $bulkDetailedFactory;

    /**
     * @var MockObject
     */
    private $bulkShortFactory;

    /**
     * @var MockObject
     */
    private $entityMetadataMock;

    /**
     * @var MockObject
     */
    private $entityManager;

    /**
     * @var MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $this->bulkCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->operationCollectionFactory = $this->createPartialMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory::class,
            ['create']
        );
        $this->operationMock = $this->createMock(OperationInterface::class);
        $this->bulkMock = $this->createMock(BulkSummaryInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->calculatedStatusSqlMock = $this->createMock(
            CalculatedStatusSql::class
        );
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->bulkDetailedFactory = $this->createPartialMock(
            DetailedBulkOperationsStatusInterfaceFactory ::class,
            ['create']
        );
        $this->bulkShortFactory = $this->createPartialMock(
            BulkOperationsStatusInterfaceFactory::class,
            ['create']
        );
        $this->entityManager = $this->createMock(EntityManager::class);

        $this->entityMetadataMock = $this->createMock(EntityMetadataInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $this->model = new BulkStatus(
            $this->bulkCollectionFactory,
            $this->operationCollectionFactory,
            $this->resourceConnectionMock,
            $this->calculatedStatusSqlMock,
            $this->metadataPoolMock,
            $this->bulkDetailedFactory,
            $this->bulkShortFactory,
            $this->entityManager
        );
    }

    /**
     * @param int|null $failureType
     * @param array $failureCodes
     * @dataProvider getFailedOperationsByBulkIdDataProvider
     */
    public function testGetFailedOperationsByBulkId($failureType, $failureCodes)
    {
        $bulkUuid = 'bulk-1';
        $operationCollection = $this->createMock(
            Collection::class
        );
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

    public function testGetOperationsCountByBulkIdAndStatus()
    {
        $bulkUuid = 'bulk-1';
        $status = 1354;
        $size = 32;

        $operationCollection = $this->createMock(
            Collection::class
        );
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

    public function testGetBulksByUser()
    {
        $userId = 1;
        $selectMock = $this->createMock(Select::class);
        $bulkCollection = $this->createMock(\Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection::class);
        $bulkCollection->expects($this->once())->method('getSelect')->willReturn($selectMock);
        $selectMock->expects($this->once())->method('columns')->willReturnSelf();
        $selectMock->expects($this->once())->method('order')->willReturnSelf();
        $this->bulkCollectionFactory->expects($this->once())->method('create')->willReturn($bulkCollection);
        $bulkCollection->expects($this->once())->method('addFieldToFilter')->with('user_id', $userId)->willReturnSelf();
        $bulkCollection->expects($this->once())->method('getItems')->willReturn([$this->bulkMock]);
        $this->assertEquals([$this->bulkMock], $this->model->getBulksByUser($userId));
    }

    public function testGetBulksStatus()
    {
        $bulkUuid = 'bulk-1';
        $allProcessedOperationCollection = $this->createMock(
            Collection::class
        );

        $completeOperationCollection = $this->createMock(
            Collection::class
        );

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
        $completeOperationCollection->method('getSize')->willReturn(5);
        $this->assertEquals(BulkSummaryInterface::IN_PROGRESS, $this->model->getBulkStatus($bulkUuid));
    }
}
