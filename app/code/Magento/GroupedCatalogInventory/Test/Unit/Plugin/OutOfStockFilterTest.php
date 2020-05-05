<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GroupedCatalogInventory\Test\Unit\Plugin;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedCatalogInventory\Plugin\OutOfStockFilter;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for OutOfStockFilter plugin.
 */
class OutOfStockFilterTest extends TestCase
{
    /**
     * @var OutOfStockFilter
     */
    private $unit;
    /**
     * @var Grouped|MockObject
     */
    private $subjectMock;

    /**
     * @var StockRegistryInterface|MockObject
     */
    private $stockRegistryMock;

    /**
     * @var DataObject|MockObject
     */
    private $buyRequestMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->subjectMock = $this->getMockBuilder(Grouped::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->getMock();

        $this->stockRegistryMock = $this->getMockBuilder(StockRegistryInterface::class)
            ->getMock();

        $this->unit = $objectManager->getObject(
            OutOfStockFilter::class,
            [
                'stockRegistry' => $this->stockRegistryMock,
            ]
        );
    }

    /**
     * Tests that the unit will process only parameters of array type.
     *
     * @param mixed $nonArrayResult
     * @return void
     * @dataProvider nonArrayResultsProvider
     */
    public function testFilterOnlyProcessesArray($nonArrayResult): void
    {
        $this->stockRegistryMock->expects($this->never())
            ->method('getProductStockStatus');

        $result = $this->unit->afterPrepareForCartAdvanced(
            $this->subjectMock,
            $nonArrayResult,
            $this->buyRequestMock
        );

        $this->assertSame($nonArrayResult, $result);
    }

    /**
     * Tests that the unit will not process if special parameter "super_group" will present in "buyRequest" parameter.
     *
     * @return void
     */
    public function testFilterIgnoresResultIfSuperGroupIsPresent(): void
    {
        $this->stockRegistryMock->method('getProductStockStatus')
            ->willReturn(StockStatusInterface::STATUS_OUT_OF_STOCK);
        $this->buyRequestMock->method('getData')
            ->with('super_group')
            ->willReturn([123 => '1']);

        $product = $this->createProductMock();

        $result = $this->unit->afterPrepareForCartAdvanced(
            $this->subjectMock,
            [$product],
            $this->buyRequestMock
        );

        $this->assertSame([$product], $result, 'All products should stay in array if super_group is setted.');
    }

    /**
     * Tests that out of stock products will be removed from resulting array.
     *
     * @param array $originalResult
     * @param array $productStockStatusMap
     * @param array $expectedResult
     * @dataProvider outOfStockProductDataProvider
     */
    public function testFilterRemovesOutOfStockProducts(
        $originalResult,
        array $productStockStatusMap,
        array $expectedResult
    ): void {
        $this->stockRegistryMock->method('getProductStockStatus')
            ->willReturnMap($productStockStatusMap);

        $result = $this->unit->afterPrepareForCartAdvanced(
            $this->subjectMock,
            $originalResult,
            $this->buyRequestMock
        );

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Out of stock
     *
     * @return array
     */
    public function outOfStockProductDataProvider(): array
    {
        $product1 = $this->createProductMock();
        $product1->method('getId')
            ->willReturn(123);

        $product2 = $this->createProductMock();
        $product2->method('getId')
            ->willReturn(321);

        return [
            [
                'originalResult' => [$product1, $product2],
                'productStockStatusMap' => [
                    [123, null, StockStatusInterface::STATUS_OUT_OF_STOCK],
                    [321, null, StockStatusInterface::STATUS_IN_STOCK],
                ],
                'expectedResult' => [1 => $product2],
            ],
            [
                'originalResult' => [$product1],
                'productStockStatusMap' => [[123, null, StockStatusInterface::STATUS_IN_STOCK]],
                'expectedResult' => [0 => $product1],
            ],
            [
                'originalResult' => $product1,
                'productStockStatusMap' => [[123, null, StockStatusInterface::STATUS_IN_STOCK]],
                'expectedResult' => [0 => $product1],
            ],
        ];
    }

    /**
     * Provider of non array type "result" parameters.
     *
     * @return array
     */
    public function nonArrayResultsProvider(): array
    {
        return [
            [123],
            ['abc'],
            [new \stdClass()]
        ];
    }

    /**
     * Creates new Product mock.
     *
     * @return MockObject|Product
     */
    private function createProductMock(): MockObject
    {
        return $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
