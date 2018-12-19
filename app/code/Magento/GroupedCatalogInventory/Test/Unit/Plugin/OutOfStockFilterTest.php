<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GroupedCatalogInventory\Test\Unit\Plugin;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\GroupedCatalogInventory\Plugin\OutOfStockFilter;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OutOfStockFilterTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $subjectMock;

    /**
     * @var MockObject
     */
    private $stockStatusRepositoryMock;

    /**
     * @var MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var MockObject
     */
    private $searchCriteriaFactoryMock;

    /**
     * @var MockObject
     */
    private $stockStatusCollectionMock;

    /**
     * @param $nonArrayResult
     * @dataProvider nonArrayResults
     */
    public function testFilterOnlyProcessesArray($nonArrayResult)
    {
        $this->searchCriteriaMock->expects($this->never())->method('setProductsFilter');
        $this->stockStatusRepositoryMock->expects($this->never())->method('getList');

        $plugin = $this->getPluginInstance();

        $result = $plugin->afterPrepareForCartAdvanced(
            $this->subjectMock,
            $nonArrayResult,
            new DataObject()
        );

        $this->assertSame($nonArrayResult, $result);
    }

    public function testFilterIgnoresResultIfSuperGroupIsPresent()
    {
        $this->searchCriteriaMock->expects($this->never())->method('setProductsFilter');
        $this->stockStatusRepositoryMock->expects($this->never())->method('getList');

        $plugin = $this->getPluginInstance();

        $product = $this->createProductMock();

        $result = $plugin->afterPrepareForCartAdvanced(
            $this->subjectMock,
            [$product],
            new DataObject(['super_group' => [123 => '1']])
        );

        $this->assertSame([$product], $result);
    }

    /**
     * @param $originalResult
     * @param $stockStatusCollection
     * @param $expectedResult
     * @dataProvider outOfStockProductData
     */
    public function testFilterRemovesOutOfStockProductsWhenSuperGroupIsNotPresent(
        $originalResult,
        $stockStatusCollection,
        $expectedResult
    ) {
        $this->stockStatusRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($stockStatusCollection);

        $plugin = $this->getPluginInstance();

        $result = $plugin->afterPrepareForCartAdvanced(
            $this->subjectMock,
            $originalResult,
            new DataObject()
        );

        $this->assertSame($expectedResult, $result);
    }

    public function outOfStockProductData()
    {
        $product1 = $this->createProductMock();
        $product1->method('getId')->willReturn(123);

        $product2 = $this->createProductMock();
        $product2->method('getId')->willReturn(321);

        return [
            [[$product1, $product2], $this->createStatusResult([123 => false, 321 => true]), [1 => $product2]],
            [[$product1], $this->createStatusResult([123 => true]), [0 => $product1]],
            [$product1, $this->createStatusResult([123 => true]), [0 => $product1]]
        ];
    }

    public function nonArrayResults()
    {
        return [
            [123],
            ['abc'],
            [new \stdClass()]
        ];
    }

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder(Grouped::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockStatusRepositoryMock = $this->getMockBuilder(StockStatusRepositoryInterface::class)
            ->getMock();

        $this->searchCriteriaFactoryMock = $this->getMockBuilder(StockStatusCriteriaInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaMock = $this->getMockBuilder(StockStatusCriteriaInterface::class)
            ->getMock();

        $this->stockStatusCollectionMock = $this->getMockBuilder(StockStatusCollectionInterface::class)
            ->getMock();

        $this->searchCriteriaFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
    }

    private function createProductMock()
    {
        return $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return OutOfStockFilter
     */
    private function getPluginInstance()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        /** @var OutOfStockFilter $filter */
        $filter = $objectManager->getObject(OutOfStockFilter::class, [
            'stockStatusRepository' => $this->stockStatusRepositoryMock,
            'criteriaInterfaceFactory' => $this->searchCriteriaFactoryMock
        ]);

        return $filter;
    }

    private function createStatusResult(array $productStatuses)
    {
        $result = [];

        foreach ($productStatuses as $productId => $status) {
            $mock = $this->getMockBuilder(StockStatusInterface::class)
                ->getMock();

            $mock->expects($this->any())
                ->method('getProductId')
                ->willReturn($productId);

            $mock->expects($this->any())
                ->method('getStockStatus')
                ->willReturn(
                    $status
                    ? StockStatusInterface::STATUS_IN_STOCK
                    : StockStatusInterface::STATUS_OUT_OF_STOCK
                );

            $result[] = $mock;
        }

        $stockStatusCollection = $this->getMockBuilder(StockStatusCollectionInterface::class)
            ->getMock();

        $stockStatusCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($result);

        return $stockStatusCollection;
    }
}
