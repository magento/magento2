<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model\ResourceModel\Operation;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationListInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Create;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Create operation.
 */
class CreateTest extends TestCase
{
    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var TypeResolver|MockObject
     */
    private $typeResolver;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var Create
     */
    private $create;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->typeResolver = $this->createMock(TypeResolver::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);

        $objectManager = new ObjectManager($this);
        $this->create = $objectManager->getObject(
            Create::class,
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
        $operationList = $this->getMockForAbstractClass(OperationListInterface::class);
        $this->typeResolver->expects($this->once())->method('resolve')->with($operationList)
            ->willReturn(OperationListInterface::class);
        $metadata = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(OperationListInterface::class)->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnection->expects($this->once())
            ->method('getConnection')->with($connectionName)->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $operation = $this->getMockBuilder(OperationInterface::class)
            ->addMethods(['getData'])
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
     */
    public function testExecuteWithException()
    {
        $this->expectException('Exception');
        $connectionName = 'default';
        $operationData = ['key1' => 'value1'];
        $operationTable = 'magento_operation';
        $operationList = $this->getMockForAbstractClass(OperationListInterface::class);
        $this->typeResolver->expects($this->once())->method('resolve')->with($operationList)
            ->willReturn(OperationListInterface::class);
        $metadata = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->once())->method('getMetadata')
            ->with(OperationListInterface::class)->willReturn($metadata);
        $metadata->expects($this->once())->method('getEntityConnectionName')->willReturn($connectionName);
        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnection->expects($this->once())
            ->method('getConnection')->with($connectionName)->willReturn($connection);
        $connection->expects($this->once())->method('beginTransaction')->willReturnSelf();
        $operation = $this->getMockBuilder(OperationInterface::class)
            ->addMethods(['getData'])
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
