<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRowSizeEstimator;

class CompositeProductRowSizeEstimatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeProductRowSizeEstimator
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rowSizeEstimatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $defaultPriceMock;

    protected function setUp()
    {
        $this->rowSizeEstimatorMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableRowSizeEstimator::class,
            [],
            [],
            '',
            false
        );
        $this->defaultPriceMock = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice::class,
            [],
            [],
            '',
            false
        );
        $this->model = new CompositeProductRowSizeEstimator($this->defaultPriceMock, $this->rowSizeEstimatorMock);
    }

    public function testEstimateRowSize()
    {
        $expectedResult = 2000;
        $tableName = 'catalog_product_relation';
        $maxRelatedProductCount = 10;

        $this->rowSizeEstimatorMock->expects($this->once())->method('estimateRowSize')->willReturn(200);

        $connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->defaultPriceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->defaultPriceMock->expects($this->once())->method('getTable')->with($tableName)->willReturn($tableName);

        $relationSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $relationSelectMock->expects($this->once())
            ->method('from')
            ->with(
                ['relation' => $tableName],
                ['count' => 'count(relation.child_id)']
            )
            ->willReturnSelf();
        $relationSelectMock->expects($this->once())->method('group')->with('parent_id')->willReturnSelf();
        $connectionMock->expects($this->at(0))->method('select')->willReturn($relationSelectMock);

        $maxSelectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $maxSelectMock->expects($this->once())
            ->method('from')
            ->with(
                ['max_value' => $relationSelectMock],
                ['count' => 'MAX(count)']
            )
            ->willReturnSelf();
        $connectionMock->expects($this->at(1))->method('select')->willReturn($maxSelectMock);

        $connectionMock->expects($this->at(2))
            ->method('fetchOne')
            ->with($maxSelectMock)
            ->willReturn($maxRelatedProductCount);

        $this->assertEquals(
            $expectedResult,
            $this->model->estimateRowSize()
        );
    }
}
