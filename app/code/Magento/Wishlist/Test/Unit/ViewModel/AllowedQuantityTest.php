<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Store\Model\Store;
use Magento\Wishlist\ViewModel\AllowedQuantity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AllowedQuantityTest
 */
class AllowedQuantityTest extends TestCase
{
    /**
     * @var AllowedQuantity
     */
    private $sut;

    /**
     * @var StockRegistry|MockObject
     */
    private $stockRegistryMock;

    /**
     * @var ItemInterface|MockObject
     */
    private $itemMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->stockRegistryMock = $this->createMock(StockRegistry::class);
        $this->itemMock = $this->getMockForAbstractClass(
            ItemInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getMinSaleQty', 'getMaxSaleQty']
        );
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new AllowedQuantity(
            $this->stockRegistryMock
        );
        $this->sut->setItem($this->itemMock);
    }

    /**
     * Getting min and max qty test.
     *
     * @dataProvider saleQuantityDataProvider
     *
     * @param int $minSaleQty
     * @param int $maxSaleQty
     * @param array $expectedResult
     */
    public function testGettingMinMaxQty(int $minSaleQty, int $maxSaleQty, array $expectedResult)
    {
        $this->storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn(1);
        $this->productMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);
        $this->productMock->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->itemMock->expects($this->any())
            ->method('getMinSaleQty')
            ->willReturn($minSaleQty);
        $this->itemMock->expects($this->any())
            ->method('getMaxSaleQty')
            ->willReturn($maxSaleQty);
        $this->stockRegistryMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->itemMock);

        $result = $this->sut->getMinMaxQty();

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Sales quantity provider
     *
     * @return array
     */
    public function saleQuantityDataProvider(): array
    {
        return [
            [
                1,
                10,
                [
                    'minAllowed' => 1,
                    'maxAllowed' => 10
                ]
            ], [
                1,
                0,
                [
                    'minAllowed' => 1,
                    'maxAllowed' => 99999999
                ]
            ]
        ];
    }
}
