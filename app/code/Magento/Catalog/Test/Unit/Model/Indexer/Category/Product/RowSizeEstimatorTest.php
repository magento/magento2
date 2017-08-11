<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product;

/**
 * Class RowSizeEstimatorTest
 * @package Magento\Catalog\Test\Unit\Model\Indexer\Category
 */
class RowSizeEstimatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product\RowSizeEstimator
     */
    private $model;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Product\RowSizeEstimator(
            $this->resourceConnectionMock
        );
    }

    public function testEstimateRowSize()
    {
        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMock();
        $storeGroupCounterMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
