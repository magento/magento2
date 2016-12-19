<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

class BulkStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\BulkStatus
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationCollectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $calculatedStatusSqlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMetadataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    protected function setUp()
    {
        $this->bulkCollectionFactory = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->operationCollectionFactory = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->operationMock = $this->getMock(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class);
        $this->bulkMock = $this->getMock(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class);
        $this->resourceConnectionMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $this->calculatedStatusSqlMock = $this->getMock(
            \Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql::class,
            [],
            [],
            '',
            false
        );
        $this->metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );

        $this->entityMetadataMock = $this->getMock(\Magento\Framework\EntityManager\EntityMetadataInterface::class);
        $this->connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

        $this->model = new \Magento\AsynchronousOperations\Model\BulkStatus(
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
     * @dataProvider getFailedOperationsByBulkIdDataProvider
     */
    public function testGetFailedOperationsByBulkId($failureType, $failureCodes)
    {
        $bulkUuid = 'bulk-1';
        $operationCollection = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection::class,
            [],
            [],
            '',
            false
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

        $operationCollection = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection::class,
            [],
            [],
            '',
            false
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

    public function getFailedOperationsByBulkIdDataProvider()
    {
        return [
            [1, [1]],
            [null,
                [
                    OperationInterface::STATUS_TYPE_RETRIABLY_FAILED,
                    OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED
                ]
            ]
        ];
    }

    public function testGetBulksByUser()
    {
        $userId = 1;
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $bulkCollection = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection::class,
            [],
            [],
            '',
            false
        );
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
        $allProcessedOperationCollection = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection::class,
            [],
            [],
            '',
            false
        );

        $completeOperationCollection = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection::class,
            [],
            [],
            '',
            false
        );

        $connectionName = 'connection_name';
        $entityType = \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class;
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

        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
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
