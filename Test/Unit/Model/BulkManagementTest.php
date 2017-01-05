<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model;

use Magento\AsynchronousOperations\Model\BulkManagement;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection as OperationCollection;
use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\BulkManagement
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkSummaryFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $publisherMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMetadataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkSummaryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $operationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->entityManagerMock = $this->getMock(
            \Magento\Framework\EntityManager\EntityManager::class,
            [],
            [],
            '',
            false
        );
        $this->bulkSummaryFactoryMock = $this->getMock(
            \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->publisherMock = $this->getMock(\Magento\Framework\MessageQueue\PublisherInterface::class);
        $this->metadataPoolMock = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );
        $this->resourceConnectionMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );
        $this->entityMetadataMock = $this->getMock(\Magento\Framework\EntityManager\EntityMetadataInterface::class);
        $this->connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->bulkSummaryMock = $this->getMock(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class);
        $this->operationMock = $this->getMock(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class);
        $this->collectionFactoryMock = $this->getMock(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->model = new BulkManagement(
            $this->entityManagerMock,
            $this->bulkSummaryFactoryMock,
            $this->collectionFactoryMock,
            $this->publisherMock,
            $this->metadataPoolMock,
            $this->resourceConnectionMock,
            $this->loggerMock
        );
    }

    public function testScheduleBulk()
    {
        $bulkUuid = 'bulk-1';
        $description = 'description';
        $userId = 1;
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
        $this->connectionMock->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->bulkSummaryFactoryMock->expects($this->once())->method('create')->willReturn($this->bulkSummaryMock);
        $this->bulkSummaryMock->expects($this->once())->method('setBulkId')->with($bulkUuid);
        $this->bulkSummaryMock->expects($this->once())->method('setDescription')->with($description);
        $this->bulkSummaryMock->expects($this->once())->method('setUserId')->with($userId);
        $this->entityManagerMock->expects($this->any())->method('save');
        $this->connectionMock->expects($this->once())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollBack');
        $this->operationMock->expects($this->once())->method('getTopicName')->willReturn('topic_name');
        $this->publisherMock->expects($this->once())->method('publish')->with('topic_name', $this->operationMock);
        $this->assertTrue(
            $this->model->scheduleBulk($bulkUuid, [$this->operationMock], $description, $userId)
        );
    }

    public function testScheduleBulkWithException()
    {
        $bulkUuid = 'bulk-1';
        $description = 'description';
        $userId = 1;
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
        $this->connectionMock->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $this->bulkSummaryFactoryMock->expects($this->once())->method('create')->willReturn($this->bulkSummaryMock);
        $this->bulkSummaryMock->expects($this->once())->method('setBulkId')->with($bulkUuid);
        $this->bulkSummaryMock->expects($this->once())->method('setDescription')->with($description);
        $this->bulkSummaryMock->expects($this->once())->method('setUserId')->with($userId);
        $this->entityManagerMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->connectionMock->expects($this->never())->method('commit');
        $this->connectionMock->expects($this->once())->method('rollBack');
        $this->publisherMock->expects($this->never())->method('publish');
        $this->loggerMock->expects($this->once())->method('critical');
        $this->assertFalse(
            $this->model->scheduleBulk($bulkUuid, [$this->operationMock], $description, $userId)
        );
    }

    public function testRetryBulk()
    {
        $bulkUuid = '49da7406-1ec3-4100-95ae-9654c83a6801';
        $errorCodes = [1111, 2222];
        $operations = [$this->operationMock];
        $topicName = 'test.topic.name';
        $connectionName = 'connection.name';

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

        $operationCollectionMock = $this->getMock(OperationCollection::class, [], [], '', false);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($operationCollectionMock);
        $operationCollectionMock->expects($this->at(0))
            ->method('addFieldToFilter')
            ->with('error_code', ['in' => $errorCodes])
            ->willReturnSelf();
        $operationCollectionMock->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('bulk_uuid', ['eq' => $bulkUuid])
            ->willReturnSelf();
        $operationCollectionMock->expects($this->at(2))
            ->method('getItems')
            ->willReturn($operations);

        $this->connectionMock->expects($this->once())->method('beginTransaction');
        $this->connectionMock->expects($this->once())->method('commit');
        $this->connectionMock->expects($this->never())->method('rollBack');

        $this->operationMock->expects($this->any())->method('getTopicName')->willReturn($topicName);
        $this->connectionMock->expects($this->once())->method('delete');

        $this->publisherMock->expects($this->once())->method('publish')->with($topicName, $this->operationMock);

        $this->assertEquals(count($operations), $this->model->retryBulk($bulkUuid, $errorCodes));
    }

    public function testDeleteBulk()
    {
        $bulkUuid = 'bulk-1';
        $this->bulkSummaryFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->bulkSummaryMock);
        $this->entityManagerMock
            ->expects($this->once())
            ->method('load')
            ->with($this->bulkSummaryMock, $bulkUuid)
            ->willReturn($this->bulkSummaryMock);
        $this->entityManagerMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->bulkSummaryMock)
            ->willReturn(true);
        $this->assertTrue($this->model->deleteBulk($bulkUuid));
    }
}
