<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model;

/**
 * Unit test for BulkManagement model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\EntityManager\EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $bulkSummaryFactory;

    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $operationCollectionFactory;

    /**
     * @var \Magento\Framework\MessageQueue\BulkPublisherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisher;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\AsynchronousOperations\Model\BulkManagement
     */
    private $bulkManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityManager::class)
            ->disableOriginalConstructor()->getMock();
        $this->bulkSummaryFactory = $this
            ->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->operationCollectionFactory = $this
            ->getMockBuilder(\Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->publisher = $this->getMockBuilder(\Magento\Framework\MessageQueue\BulkPublisherInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->bulkManagement = $objectManager->getObject(
            \Magento\AsynchronousOperations\Model\BulkManagement::class,
            [
                'entityManager' => $this->entityManager,
                'bulkSummaryFactory' => $this->bulkSummaryFactory,
                'operationCollectionFactory' => $this->operationCollectionFactory,
                'publisher' => $this->publisher,
                'metadataPool' => $this->metadataPool,
                'resourceConnection' => $this->resourceConnection,
                'logger' => $this->logger,
            ]
        );
    }

    /**
     * Test for scheduleBulk method.
     *
     * @return void
     */
    public function testScheduleBulk()
    {
        $bulkUuid = 'bulk-001';
        $description = 'Bulk summary description...';
        $userId = 1;
        $userType = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN;
        $connectionName = 'default';
        $topicNames = ['topic.name.0', 'topic.name.1'];
        $operation = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class)
            ->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection->expects($this->once())
            ->method('getConnectionByName')->with($connectionName)->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $bulkSummary = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->bulkSummaryFactory->expects($this->once())->method('create')->willReturn($bulkSummary);
        $this->entityManager->expects($this->once())
            ->method('load')->with($bulkSummary, $bulkUuid)->willReturn($bulkSummary);
        $bulkSummary->expects($this->once())->method('setBulkId')->with($bulkUuid)->willReturnSelf();
        $bulkSummary->expects($this->once())->method('setDescription')->with($description)->willReturnSelf();
        $bulkSummary->expects($this->once())->method('setUserId')->with($userId)->willReturnSelf();
        $bulkSummary->expects($this->once())->method('setUserType')->with($userType)->willReturnSelf();
        $bulkSummary->expects($this->once())->method('getOperationCount')->willReturn(1);
        $bulkSummary->expects($this->once())->method('setOperationCount')->with(3)->willReturnSelf();
        $this->entityManager->expects($this->once())->method('save')->with($bulkSummary)->willReturn($bulkSummary);
        $connection->expects($this->once())->method('commit')->willReturnSelf();
        $operation->expects($this->exactly(2))->method('getTopicName')
            ->willReturnOnConsecutiveCalls($topicNames[0], $topicNames[1]);
        $this->publisher->expects($this->exactly(2))->method('publish')
            ->withConsecutive([$topicNames[0], [$operation]], [$topicNames[1], [$operation]])->willReturn(null);
        $this->assertTrue(
            $this->bulkManagement->scheduleBulk($bulkUuid, [$operation, $operation], $description, $userId)
        );
    }

    /**
     * Test for scheduleBulk method with exception.
     *
     * @return void
     */
    public function testScheduleBulkWithException()
    {
        $bulkUuid = 'bulk-001';
        $description = 'Bulk summary description...';
        $userId = 1;
        $connectionName = 'default';
        $exceptionMessage = 'Exception message';
        $operation = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class)
            ->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection->expects($this->once())
            ->method('getConnectionByName')->with($connectionName)->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $bulkSummary = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->bulkSummaryFactory->expects($this->once())->method('create')->willReturn($bulkSummary);
        $this->entityManager->expects($this->once())->method('load')
            ->with($bulkSummary, $bulkUuid)->willThrowException(new \LogicException($exceptionMessage));
        $connection->expects($this->once())->method('rollBack')->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exceptionMessage);
        $this->publisher->expects($this->never())->method('publish');
        $this->assertFalse($this->bulkManagement->scheduleBulk($bulkUuid, [$operation], $description, $userId));
    }

    /**
     * Test for retryBulk method.
     *
     * @return void
     */
    public function testRetryBulk()
    {
        $bulkUuid = 'bulk-001';
        $errorCodes = ['errorCode'];
        $connectionName = 'default';
        $operationId = 1;
        $operationTable = 'magento_operation';
        $topicName = 'topic.name';
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class)
            ->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection->expects($this->once())
            ->method('getConnectionByName')->with($connectionName)->willReturn($connection);
        $operationCollection = $this
            ->getMockBuilder(\Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $operationCollection->expects($this->exactly(2))->method('addFieldToFilter')
            ->withConsecutive(['error_code', ['in' => $errorCodes]], ['bulk_uuid', ['eq' => $bulkUuid]])
            ->willReturnSelf();
        $operation = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $operationCollection->expects($this->once())->method('getItems')->willReturn([$operation]);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $operation->expects($this->once())->method('getId')->willReturn($operationId);
        $operation->expects($this->once())->method('setId')->with(null)->willReturnSelf();
        $this->resourceConnection->expects($this->once())
            ->method('getTableName')->with($operationTable)->willReturn($operationTable);
        $connection->expects($this->once())
            ->method('quoteInto')->with('id IN (?)', [$operationId])->willReturn('id IN (' . $operationId .')');
        $connection->expects($this->once())
            ->method('delete')->with($operationTable, 'id IN (' . $operationId .')')->willReturn(1);
        $connection->expects($this->once())->method('commit')->willReturnSelf();
        $operation->expects($this->once())->method('getTopicName')->willReturn($topicName);
        $this->publisher->expects($this->once())->method('publish')->with($topicName, [$operation])->willReturn(null);
        $this->assertEquals(1, $this->bulkManagement->retryBulk($bulkUuid, $errorCodes));
    }

    /**
     * Test for retryBulk method with exception.
     *
     * @return void
     */
    public function testRetryBulkWithException()
    {
        $bulkUuid = 'bulk-001';
        $errorCodes = ['errorCode'];
        $connectionName = 'default';
        $operationId = 1;
        $operationTable = 'magento_operation';
        $exceptionMessage = 'Exception message';
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class)
            ->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection->expects($this->once())
            ->method('getConnectionByName')->with($connectionName)->willReturn($connection);
        $operationCollection = $this
            ->getMockBuilder(\Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->operationCollectionFactory->expects($this->once())->method('create')->willReturn($operationCollection);
        $operationCollection->expects($this->exactly(2))->method('addFieldToFilter')
            ->withConsecutive(['error_code', ['in' => $errorCodes]], ['bulk_uuid', ['eq' => $bulkUuid]])
            ->willReturnSelf();
        $operation = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $operationCollection->expects($this->once())->method('getItems')->willReturn([$operation]);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $operation->expects($this->once())->method('getId')->willReturn($operationId);
        $operation->expects($this->once())->method('setId')->with(null)->willReturnSelf();
        $this->resourceConnection->expects($this->once())
            ->method('getTableName')->with($operationTable)->willReturn($operationTable);
        $connection->expects($this->once())
            ->method('quoteInto')->with('id IN (?)', [$operationId])->willReturn('id IN (' . $operationId .')');
        $connection->expects($this->once())
            ->method('delete')->with($operationTable, 'id IN (' . $operationId .')')
            ->willThrowException(new \Exception($exceptionMessage));
        $connection->expects($this->once())->method('rollBack')->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exceptionMessage);
        $this->publisher->expects($this->never())->method('publish');
        $this->assertEquals(0, $this->bulkManagement->retryBulk($bulkUuid, $errorCodes));
    }

    /**
     * Test for deleteBulk method.
     *
     * @return void
     */
    public function testDeleteBulk()
    {
        $bulkUuid = 'bulk-001';
        $bulkSummary = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->bulkSummaryFactory->expects($this->once())->method('create')->willReturn($bulkSummary);
        $this->entityManager->expects($this->once())
            ->method('load')->with($bulkSummary, $bulkUuid)->willReturn($bulkSummary);
        $this->entityManager->expects($this->once())->method('delete')->with($bulkSummary)->willReturn(true);
        $this->assertTrue($this->bulkManagement->deleteBulk($bulkUuid));
    }
}
