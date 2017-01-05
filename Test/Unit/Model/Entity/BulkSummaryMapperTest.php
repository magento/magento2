<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Model\Entity;

use Magento\AsynchronousOperations\Model\Entity\BulkSummaryMapper;

/**
 * Class BulkSummaryMapperTest
 */
class BulkSummaryMapperTest extends \PHPUnit_Framework_TestCase
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
        $this->selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
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

    public function entityToDatabaseDataProvider()
    {
        return [
            [1, ['uuid' => 'bulk-1', 'id' => 1]],
            [false, ['uuid' => 'bulk-1']]
        ];
    }
}
