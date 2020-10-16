<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product;

use Magento\Catalog\Model\Indexer\Category\Product\RowSizeEstimator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RowSizeEstimatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var RowSizeEstimator
     */
    private $model;

    protected function setUp(): void
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new RowSizeEstimator(
            $this->resourceConnectionMock
        );
    }

    public function testEstimateRowSize()
    {
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $storeGroupCounterMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock->expects($this->exactly(2))
            ->method('getTableName')
            ->willReturnMap([['store_group', 'storegrouptable'], ['catalog_category_product', 'ccp']]);

        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->exactly(3))
            ->method('select')
            ->willReturn($storeGroupCounterMock);
        $storeGroupCounterMock->expects($this->exactly(3))
            ->method('from')
            ->willReturnSelf();
        $storeGroupCounterMock->expects($this->once())
            ->method('where')
            ->with('group_id > 0')
            ->willReturnSelf();
        $connectionMock->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturn(5);

        $storeGroupCounterMock->expects($this->once())
            ->method('group')
            ->with('product_id')
            ->willReturnSelf();
        $this->assertEquals(2500, $this->model->estimateRowSize());
    }
}
