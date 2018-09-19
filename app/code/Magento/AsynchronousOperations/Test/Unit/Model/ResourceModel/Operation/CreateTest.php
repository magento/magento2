<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model\ResourceModel\Operation;

/**
 * Unit test for Create operation.
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\EntityManager\TypeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeResolver;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    /**
     * @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Create
     */
    private $create;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->metadataPool = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()->getMock();
        $this->typeResolver = $this->getMockBuilder(\Magento\Framework\EntityManager\TypeResolver::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->create = $objectManager->getObject(
            \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Create::class,
            [
                'metadataPool' => $this->metadataPool,
                'typeResolver' => $this->typeResolver,
                'resourceConnection' => $this->resourceConnection,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $connectionName = 'default';
        $operationData = ['key1' => 'value1'];
        $operationTable = 'magento_operation';
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->typeResolver->expects($this->once())->method('resolve')->with($operationList)
            ->willReturn(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class);
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection->expects($this->once())
            ->method('getConnection')->with($connectionName)->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $operation = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $operationList->expects($this->once())->method('getItems')->willReturn([$operation]);
        $operation->expects($this->once())->method('getData')->willReturn($operationData);
        $metadata->expects($this->once())->method('getEntityTable')->willReturn($operationTable);
        $connection->expects($this->once())->method('insertOnDuplicate')
            ->with($operationTable, [$operationData], ['status', 'error_code', 'result_message'])->willReturn(1);
        $connection->expects($this->once())->method('commit')->willReturnSelf();
        $this->assertEquals($operationList, $this->create->execute($operationList));
    }

    /**
     * Test for execute method with exception.
     *
     * @return void
     * @expectedException \Exception
     */
    public function testExecuteWithException()
    {
        $connectionName = 'default';
        $operationData = ['key1' => 'value1'];
        $operationTable = 'magento_operation';
        $operationList = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->typeResolver->expects($this->once())->method('resolve')->with($operationList)
            ->willReturn(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class);
        $metadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(\Magento\AsynchronousOperations\Api\Data\OperationListInterface::class)->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceConnection->expects($this->once())
            ->method('getConnection')->with($connectionName)->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $operation = $this->getMockBuilder(\Magento\AsynchronousOperations\Api\Data\OperationInterface::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $operationList->expects($this->once())->method('getItems')->willReturn([$operation]);
        $operation->expects($this->once())->method('getData')->willReturn($operationData);
        $metadata->expects($this->once())->method('getEntityTable')->willReturn($operationTable);
        $connection->expects($this->once())->method('insertOnDuplicate')
            ->with($operationTable, [$operationData], ['status', 'error_code', 'result_message'])
            ->willThrowException(new \Exception());
        $connection->expects($this->once())->method('rollBack')->willReturnSelf();
        $this->create->execute($operationList);
    }
}
