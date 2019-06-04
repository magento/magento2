<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model\ResourceModel\Meta;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\SalesSequence\Model\ResourceModel\Meta\Ids;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class IdsTest
 */
class IdsTest extends TestCase
{
    /**
     * @var AdapterInterface | MockObject
     */
    private $connectionMock;

    /**
     * @var Context | MockObject
     */
    private $dbContext;

    /**
     * @var Ids
     */
    private $resource;

    /**
     * @var Resource | MockObject
     */
    protected $resourceMock;

    /**
     * @var Select | MockObject
     */
    private $select;

    /**
     * Initialization
     */
    protected function setUp()
    {
        $this->connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['query']
        );
        $this->dbContext = $this->createMock(Context::class);
        $this->resourceMock = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->dbContext->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->select = $this->createMock(Select::class);
        $this->resource = new Ids(
            $this->dbContext
        );
    }

    public function testGetByStoreId()
    {
        $metaTableName = 'sequence_meta';
        $metaIdFieldName = 'meta_id';
        $storeId = 1;
        $metaIds = [1, 2];
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn($metaTableName);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->select);
        $this->select->expects($this->at(0))
            ->method('from')
            ->with($metaTableName, [$metaIdFieldName])
            ->willReturn($this->select);
        $this->select->expects($this->at(1))
            ->method('where')
            ->with('store_id = :store_id')
            ->willReturn($this->select);
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->select, ['store_id' => $storeId])
            ->willReturn($metaIds);
        $this->assertEquals($metaIds, $this->resource->getByStoreId($storeId));
    }
}
