<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRelationsCalculator;

class CompositeProductRelationsCalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DefaultPrice
     */
    private $defaultPriceMock;

    /**
     * @var CompositeProductRelationsCalculator
     */
    private $model;

    protected function setUp(): void
    {
        $this->defaultPriceMock = $this->getMockBuilder(DefaultPrice::class)->disableOriginalConstructor()->getMock();
        $this->model = new CompositeProductRelationsCalculator($this->defaultPriceMock);
    }

    public function testGetMaxRelationsCount()
    {
        $tableName = 'catalog_product_relation';
        $maxRelatedProductCount = 200;

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)->getMock();
        $this->defaultPriceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->defaultPriceMock->expects($this->once())->method('getTable')->with($tableName)->willReturn($tableName);

        $relationSelectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $relationSelectMock->expects($this->once())
            ->method('from')
            ->with(
                ['relation' => $tableName],
                ['count' => 'count(relation.child_id)']
            )
            ->willReturnSelf();
        $relationSelectMock->expects($this->once())->method('group')->with('parent_id')->willReturnSelf();
        $connectionMock->expects($this->at(0))->method('select')->willReturn($relationSelectMock);

        $maxSelectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->assertEquals($maxRelatedProductCount, $this->model->getMaxRelationsCount());
    }
}
