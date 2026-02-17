<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
        $this->defaultPriceMock = $this->createMock(DefaultPrice::class);
        $this->model = new CompositeProductRelationsCalculator($this->defaultPriceMock);
    }

    /**
     * @return void
     */
    public function testGetMaxRelationsCount(): void
    {
        $tableName = 'catalog_product_relation';
        $maxRelatedProductCount = 200;

        $connectionMock = $this->createMock(AdapterInterface::class);
        $this->defaultPriceMock->expects($this->once())->method('getConnection')->willReturn($connectionMock);
        $this->defaultPriceMock->expects($this->once())->method('getTable')->with($tableName)->willReturn($tableName);

        $relationSelectMock = $this->createMock(Select::class);
        $relationSelectMock->expects($this->once())
            ->method('from')
            ->with(
                ['relation' => $tableName],
                ['count' => 'count(relation.child_id)']
            )
            ->willReturnSelf();
        $relationSelectMock->expects($this->once())->method('group')->with('parent_id')->willReturnSelf();

        $maxSelectMock = $this->createMock(Select::class);
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
