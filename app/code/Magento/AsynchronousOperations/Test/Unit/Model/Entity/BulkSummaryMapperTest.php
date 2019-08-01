<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model\Entity;

use Magento\AsynchronousOperations\Model\Entity\BulkSummaryMapper;

/**
 * Class BulkSummaryMapperTest
 */
class BulkSummaryMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Model\Entity\BulkSummaryMapper
     */
    private $model;

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
    private $selectMock;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $this->resourceConnectionMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->entityMetadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadataInterface::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->model = new BulkSummaryMapper(
            $this->metadataPoolMock,
            $this->resourceConnectionMock
        );
    }

    /**
     * @param int $identifier
     * @param array|false $result
     * @dataProvider entityToDatabaseDataProvider
     */
    public function testEntityToDatabase($identifier, $result)
    {
        $entityType = 'entityType';
        $data = ['uuid' => 'bulk-1'];
        $connectionName = 'connection_name';
        $entityTable = 'table_name';
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
        $this->connectionMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->entityMetadataMock->expects($this->once())->method('getEntityTable')->willReturn($entityTable);
        $this->selectMock->expects($this->once())->method('from')->with($entityTable, 'id')->willReturnSelf();
        $this->selectMock->expects($this->once())->method('where')->with("uuid = ?", 'bulk-1')->willReturnSelf();
        $this->connectionMock
            ->expects($this->once())
            ->method('fetchOne')
            ->with($this->selectMock)
            ->willReturn($identifier);
        
        $this->assertEquals($result, $this->model->entityToDatabase($entityType, $data));
    }

    /**
     * @return array
     */
    public function entityToDatabaseDataProvider()
    {
        return [
            [1, ['uuid' => 'bulk-1', 'id' => 1]],
            [false, ['uuid' => 'bulk-1']]
        ];
    }
}
