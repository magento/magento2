<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\CompositeProductRelationsCalculator;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeProductRelationsCalculatorTest extends TestCase
{
    /**
     * @var MockObject|DefaultPrice
     */
    private $defaultPriceMock;

    /**
     * @var CompositeProductRelationsCalculator
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->defaultPriceMock = $this->getMockBuilder(DefaultPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new CompositeProductRelationsCalculator($this->defaultPriceMock);
    }

    /**
     * @return void
     */
    public function testGetMaxRelationsCount(): void
    {
        $tableName = 'catalog_product_relation';
        $maxRelatedProductCount = 200;

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $this->defaultPriceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->defaultPriceMock->expects($this->once())->method('getTable')->with($tableName)->willReturn($tableName);

        $relationSelectMock = $this->getMockBuilder(Select::class)
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

        $maxSelectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $maxSelectMock->expects($this->once())
            ->method('from')
            ->with(
                ['max_value' => $relationSelectMock],
                ['count' => 'MAX(count)']
            )
            ->willReturnSelf();

        $connectionMock
            ->method('select')
            ->willReturnOnConsecutiveCalls($relationSelectMock, $maxSelectMock);
        $connectionMock
            ->method('fetchOne')
            ->with($maxSelectMock)
            ->willReturn($maxRelatedProductCount);

        $this->assertEquals($maxRelatedProductCount, $this->model->getMaxRelationsCount());
    }
}
