<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Model\Entity;

use Magento\AsynchronousOperations\Model\Entity\BulkSummaryMapper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BulkSummaryMapperTest extends TestCase
{
    /**
     * @var BulkSummaryMapper
     */
    private $model;

    /**
     * @var MockObject
     */
    private $metadataPoolMock;

    /**
     * @var MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MockObject
     */
    private $entityMetadataMock;

    /**
     * @var MockObject
     */
    private $connectionMock;

    /**
     * @var MockObject
     */
    private $selectMock;

    protected function setUp(): void
    {
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->entityMetadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);
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
