<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model\ResourceModel\Profile;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\SalesSequence\Model\ResourceModel\Profile\Ids;
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

    public function testGetByMetadataIds()
    {
        $profileTableName = 'sequence_profile';
        $profileIdFieldName = 'profile_id';
        $metadataIds = [1, 2];
        $profileIds = [10, 11];
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn($profileTableName);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->select);
        $this->select->expects($this->at(0))
            ->method('from')
            ->with($profileTableName, [$profileIdFieldName])
            ->willReturn($this->select);
        $this->select->expects($this->at(1))
            ->method('where')
            ->with('meta_id IN (?)', $metadataIds)
            ->willReturn($this->select);
        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($this->select)
            ->willReturn($profileIds);
        $this->assertEquals($profileIds, $this->resource->getByMetadataIds($metadataIds));
    }
}
