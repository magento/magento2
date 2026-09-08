<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\DataProvider;
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
        $this->operationMock = $this->createMock(OperationInterface::class);
        $this->bulkMock = $this->createMock(BulkSummaryInterface::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->calculatedStatusSqlMock = $this->createMock(CalculatedStatusSql::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->entityMetadataMock = $this->createMock(EntityMetadataInterface::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);

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
     *
     * @return void
     */
    #[DataProvider('getFailedOperationsByBulkIdDataProvider')]
    public function testGetFailedOperationsByBulkId(?int $failureType, array $failureCodes): void
    {
        $bulkUuid = 'bulk-1';
        $operationCollection = $this->createMock(OperationCollection::class);
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $operationCollection
            ->method('addFieldToFilter')
            ->willReturnCallback(function ($arg1, $arg2) use ($bulkUuid, $operationCollection, $failureCodes) {
                if ($arg1 == 'bulk_uuid' && $arg2 == $bulkUuid) {
                    return $operationCollection;
                } elseif ($arg1 == 'status' && $arg2 == $failureCodes) {
                    return $operationCollection;
                }
            });
        $operationCollection->expects($this->once())->method('getItems')->willReturn([$this->operationMock]);
        $this->assertEquals([$this->operationMock], $this->model->getFailedOperationsByBulkId($bulkUuid, $failureType));
    }

    /**
     * @return void
     */
    public function testGetOperationsCountByBulkIdAndStatus(): void
    {
        $bulkUuid = 'bulk-1';
        $status = 1354;
        $size = 32;

        $operationCollection = $this->createMock(OperationCollection::class);
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $operationCollection
            ->method('addFieldToFilter')
            ->willReturnCallback(function ($arg1, $arg2) use ($bulkUuid, $operationCollection, $status) {
                if ($arg1 == 'bulk_uuid' && $arg2 == $bulkUuid) {
                    return $operationCollection;
                } elseif ($arg1 == 'status' && $arg2 == $status) {
                    return $operationCollection;
                }
            });
        $operationCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($size);
        $this->assertEquals($size, $this->model->getOperationsCountByBulkIdAndStatus($bulkUuid, $status));
    }

    /**
     * @return void
     */
    public function testGetOperationsCountByBulkIdAndOpenStatus(): void
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
            ->willReturnCallback(function ($arg1, $arg2) use ($bulkUuid, $operationCollection, $status) {
                if ($arg1 == 'bulk_uuid' && $arg2 == $bulkUuid) {
                    return $operationCollection;
                } elseif ($arg1 == 'status' && $arg2 == $status) {
                    return $operationCollection;
                }
            });
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
    public function testGetNotStartedOperationsCountByBulkIdAndOpenStatus(): void
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
    public static function getFailedOperationsByBulkIdDataProvider(): array
    {
        return [
            [1, [1]],
            [
                null,
                [
                    OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
                    OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetBulksByUser(): void
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
    /**
     * @param int $scheduledQty
     * @param int $persistedQty
     * @param int $openQty
     * @param int $completeQty
     * @param int $expectedStatus
     * @return void
     */
    #[DataProvider('getBulkStatusDataProvider')]
    public function testGetBulkStatus(
        int $scheduledQty,
        int $persistedQty,
        int $openQty,
        int $completeQty,
        int $expectedStatus
    ): void {
        $bulkUuid = 'bulk-1';
        $persistedCollection = $this->createMock(OperationCollection::class);
        $openCollection = $this->createMock(OperationCollection::class);
        $completeCollection = $this->createMock(OperationCollection::class);

        $this->stubOperationCount($bulkUuid, $scheduledQty);

        $this->operationCollectionFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls($persistedCollection, $openCollection, $completeCollection);

        $persistedCollection
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with('bulk_uuid', $bulkUuid)
            ->willReturnSelf();
        $persistedCollection->expects($this->once())->method('getSize')->willReturn($persistedQty);

        $openCollection
            ->method('addFieldToFilter')
            ->willReturnCallback(function ($arg1, $arg2) use ($bulkUuid, $openCollection) {
                if ($arg1 === 'bulk_uuid' && $arg2 === $bulkUuid) {
                    return $openCollection;
                }
                if ($arg1 === 'status' && $arg2 === OperationInterface::STATUS_TYPE_OPEN) {
                    return $openCollection;
                }
                return $openCollection;
            });
        $openCollection->expects($this->once())->method('getSize')->willReturn($openQty);

        if ($persistedQty - $openQty === 0) {
            $completeCollection->expects($this->never())->method('addFieldToFilter');
        } else {
            $completeCollection
                ->method('addFieldToFilter')
                ->willReturnCallback(function ($arg1, $arg2) use ($bulkUuid, $completeCollection) {
                    if ($arg1 === 'bulk_uuid' && $arg2 === $bulkUuid) {
                        return $completeCollection;
                    }
                    if ($arg1 === 'status' && $arg2 === OperationInterface::STATUS_TYPE_COMPLETE) {
                        return $completeCollection;
                    }
                    return $completeCollection;
                });
            $completeCollection->method('getSize')->willReturn($completeQty);
        }

        $this->assertEquals($expectedStatus, $this->model->getBulkStatus($bulkUuid));
    }

    /**
     * @return array
     */
    public static function getBulkStatusDataProvider(): array
    {
        return [
            'not_started_legacy_no_rows' => [
                2,
                0,
                0,
                0,
                BulkSummaryInterface::NOT_STARTED,
            ],
            'not_started_all_open' => [
                2,
                2,
                2,
                0,
                BulkSummaryInterface::NOT_STARTED,
            ],
            'in_progress_mixed_complete_and_open' => [
                2,
                2,
                1,
                1,
                BulkSummaryInterface::IN_PROGRESS,
            ],
            'in_progress_legacy_partial_processed' => [
                10,
                5,
                0,
                5,
                BulkSummaryInterface::IN_PROGRESS,
            ],
            'finished_successfully' => [
                2,
                2,
                0,
                2,
                BulkSummaryInterface::FINISHED_SUCCESSFULLY,
            ],
            'finished_with_failure' => [
                2,
                2,
                0,
                1,
                BulkSummaryInterface::FINISHED_WITH_FAILURE,
            ],
        ];
    }

    /**
     * Stub magento_bulk.operation_count lookup used by getBulkStatus().
     *
     * @param string $bulkUuid
     * @param int $scheduledQty
     * @return void
     */
    private function stubOperationCount(string $bulkUuid, int $scheduledQty): void
    {
        $connectionName = 'connection_name';
        $this->metadataPoolMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with(BulkSummaryInterface::class)
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
        $this->connectionMock->expects($this->once())->method('fetchOne')->with($selectMock)->willReturn($scheduledQty);
    }
}
